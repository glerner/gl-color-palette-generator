<?php
/**
 * Color Palette Admin
 *
 * Handles admin interface for the color palette generator.
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette\Admin;

use GLColorPalette\Color_Palette;
use GLColorPalette\Color_Palette_Storage;
use GLColorPalette\Color_Analysis;
use GLColorPalette\Color_Palette_Export;

/**
 * Class Color_Palette_Admin
 */
class Color_Palette_Admin {
    /**
     * Storage instance
     * @var Color_Palette_Storage
     */
    private Color_Palette_Storage $storage;

    /**
     * Analysis instance
     * @var Color_Analysis
     */
    private Color_Analysis $analysis;

    /**
     * Export instance
     * @var Color_Palette_Export
     */
    private Color_Palette_Export $export;

    /**
     * Constructor
     */
    public function __construct() {
        $this->storage = new Color_Palette_Storage();
        $this->analysis = new Color_Analysis();
        $this->export = new Color_Palette_Export();

        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_gl_color_palette_save', [$this, 'ajax_save_palette']);
        add_action('wp_ajax_gl_color_palette_analyze', [$this, 'ajax_analyze_palette']);
        add_action('wp_ajax_gl_color_palette_export', [$this, 'ajax_export_palette']);
    }

    /**
     * Add admin menu pages
     */
    public function add_menu_pages(): void {
        add_menu_page(
            __('Color Palettes', 'gl-color-palette-generator'),
            __('Color Palettes', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palettes',
            [$this, 'render_main_page'],
            'dashicons-art',
            30
        );

        add_submenu_page(
            'gl-color-palettes',
            __('Generate Palette', 'gl-color-palette-generator'),
            __('Generate New', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palettes-generate',
            [$this, 'render_generate_page']
        );

        add_submenu_page(
            'gl-color-palettes',
            __('Settings', 'gl-color-palette-generator'),
            __('Settings', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palettes-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook_suffix Current admin page.
     */
    public function enqueue_assets(string $hook_suffix): void {
        if (!str_contains($hook_suffix, 'gl-color-palettes')) {
            return;
        }

        wp_enqueue_style(
            'gl-color-palette-admin',
            GL_COLOR_PALETTE_URL . 'assets/css/admin.css',
            [],
            GL_COLOR_PALETTE_VERSION
        );

        wp_enqueue_script(
            'gl-color-palette-admin',
            GL_COLOR_PALETTE_URL . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            GL_COLOR_PALETTE_VERSION,
            true
        );

        wp_localize_script('gl-color-palette-admin', 'glColorPalette', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gl_color_palette_nonce'),
            'i18n' => [
                'saveSuccess' => __('Palette saved successfully!', 'gl-color-palette-generator'),
                'saveError' => __('Error saving palette.', 'gl-color-palette-generator'),
                'confirmDelete' => __('Are you sure you want to delete this palette?', 'gl-color-palette-generator')
            ]
        ]);
    }

    /**
     * Render main admin page
     */
    public function render_main_page(): void {
        $palettes = $this->storage->list([
            'limit' => 20,
            'order' => 'DESC'
        ]);

        include GL_COLOR_PALETTE_PATH . 'admin/views/main-page.php';
    }

    /**
     * Render generate page
     */
    public function render_generate_page(): void {
        $palette_id = $_GET['palette_id'] ?? null;
        $palette = null;

        if ($palette_id) {
            $palette = $this->storage->get($palette_id);
            if (is_wp_error($palette)) {
                wp_die($palette);
            }
        }

        include GL_COLOR_PALETTE_PATH . 'admin/views/generate-page.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void {
        if (isset($_POST['gl_color_palette_settings'])) {
            check_admin_referer('gl_color_palette_settings');
            $this->save_settings($_POST['gl_color_palette_settings']);
            add_settings_error(
                'gl_color_palette_settings',
                'settings_updated',
                __('Settings saved.', 'gl-color-palette-generator'),
                'updated'
            );
        }

        $settings = get_option('gl_color_palette_settings', []);
        include GL_COLOR_PALETTE_PATH . 'admin/views/settings-page.php';
    }

    /**
     * Handle AJAX palette save
     */
    public function ajax_save_palette(): void {
        check_ajax_referer('gl_color_palette_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $colors = $_POST['colors'] ?? [];
        $metadata = $_POST['metadata'] ?? [];

        if (empty($colors)) {
            wp_send_json_error('No colors provided');
        }

        $palette = new Color_Palette($colors, $metadata);
        $id = $this->storage->store($palette);

        wp_send_json_success([
            'id' => $id,
            'message' => __('Palette saved successfully!', 'gl-color-palette-generator')
        ]);
    }

    /**
     * Handle AJAX palette analysis
     */
    public function ajax_analyze_palette(): void {
        check_ajax_referer('gl_color_palette_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $colors = $_POST['colors'] ?? [];
        if (empty($colors)) {
            wp_send_json_error('No colors provided');
        }

        $palette = new Color_Palette($colors);
        $analysis = $this->analysis->analyze($palette);

        wp_send_json_success([
            'analysis' => $analysis,
            'html' => $this->render_analysis_results($analysis)
        ]);
    }

    /**
     * Handle AJAX palette export
     */
    public function ajax_export_palette(): void {
        check_ajax_referer('gl_color_palette_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $colors = $_POST['colors'] ?? [];
        $format = $_POST['format'] ?? 'css';
        $options = $_POST['options'] ?? [];

        if (empty($colors)) {
            wp_send_json_error('No colors provided');
        }

        $palette = new Color_Palette($colors);

        try {
            $exported = $this->export->export($palette, $format, $options);
            wp_send_json_success(['content' => $exported]);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Render analysis results
     *
     * @param array $analysis Analysis results.
     * @return string HTML output.
     */
    private function render_analysis_results(array $analysis): string {
        ob_start();
        include GL_COLOR_PALETTE_PATH . 'admin/views/analysis-results.php';
        return ob_get_clean();
    }
} 
