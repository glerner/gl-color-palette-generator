<?php
/**
 * Admin Class
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GLColorPalette\Admin;

use GLColorPalette\ColorGenerator;

/**
 * Handles admin interface functionality
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */
class Admin {
    /**
     * @var ColorGenerator Instance of ColorGenerator class
     */
    private $color_generator;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->color_generator = new ColorGenerator();
    }

    /**
     * Initialize admin functionality
     *
     * @return void
     */
    public function init(): void {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_gl_generate_palette', [$this, 'handle_generate_palette']);
        add_action('wp_ajax_gl_save_palette', [$this, 'handle_save_palette']);
    }

    /**
     * Add menu pages to WordPress admin
     *
     * @return void
     */
    public function add_menu_pages(): void {
        add_menu_page(
            __('Color Palette Generator', 'gl-color-palette-generator'),
            __('Color Palettes', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palette',
            [$this, 'render_main_page'],
            'dashicons-art',
            30
        );

        add_submenu_page(
            'gl-color-palette',
            __('Settings', 'gl-color-palette-generator'),
            __('Settings', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palette-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        if (!strpos($hook, 'gl-color-palette')) {
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
        ]);
    }

    /**
     * Register plugin settings
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting('gl_color_palette_settings', 'gl_color_palette_size');
        register_setting('gl_color_palette_settings', 'gl_color_palette_format');

        add_settings_section(
            'gl_color_palette_general',
            __('General Settings', 'gl-color-palette-generator'),
            [$this, 'render_settings_section'],
            'gl_color_palette_settings'
        );

        add_settings_field(
            'gl_color_palette_size',
            __('Default Palette Size', 'gl-color-palette-generator'),
            [$this, 'render_size_field'],
            'gl_color_palette_settings',
            'gl_color_palette_general'
        );
    }

    /**
     * Handle AJAX palette generation
     *
     * @return void
     */
    public function handle_generate_palette(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $size = isset($_POST['size']) ? absint($_POST['size']) : 5;
        $base_color = isset($_POST['baseColor']) ? sanitize_text_field($_POST['baseColor']) : null;

        try {
            $palette = $this->color_generator->generate_palette($size, $base_color);
            wp_send_json_success(['palette' => $palette]);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Handle AJAX palette saving
     *
     * @return void
     */
    public function handle_save_palette(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $colors = isset($_POST['colors']) ? (array) $_POST['colors'] : [];
        $colors = array_map('sanitize_text_field', $colors);

        if (empty($name) || empty($colors)) {
            wp_send_json_error('Invalid data');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        $result = $wpdb->insert(
            $table_name,
            [
                'name' => $name,
                'colors' => json_encode($colors),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error('Database error');
        }

        wp_send_json_success(['id' => $wpdb->insert_id]);
    }

    /**
     * Render main plugin page
     *
     * @return void
     */
    public function render_main_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once GL_COLOR_PALETTE_PATH . 'templates/admin/main-page.php';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once GL_COLOR_PALETTE_PATH . 'templates/admin/settings-page.php';
    }

    /**
     * Render settings section description
     *
     * @return void
     */
    public function render_settings_section(): void {
        echo '<p>' . esc_html__('Configure the default settings for the color palette generator.', 'gl-color-palette-generator') . '</p>';
    }

    /**
     * Render palette size field
     *
     * @return void
     */
    public function render_size_field(): void {
        $size = get_option('gl_color_palette_size', 5);
        echo '<input type="number" name="gl_color_palette_size" value="' . esc_attr($size) . '" min="2" max="10" />';
    }
} 
