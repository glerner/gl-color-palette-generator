<?php
/**
 * Color Palette Admin Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Admin;

use GL_Color_Palette_Generator\Settings\Settings_Manager;

/**
 * Class Color_Palette_Admin
 */
class Color_Palette_Admin {
    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     */
    private $settings;

    /**
     * Initialize the admin
     */
    public function init() {
        $this->settings = new Settings_Manager();

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_gl_cpg_generate_palette', [$this, 'handle_generate_palette']);
    }

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Color Palette Generator', 'gl-color-palette-generator'),
            __('Color Palettes', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palette-generator',
            [$this, 'render_main_page'],
            'dashicons-art',
            30
        );

        add_submenu_page(
            'gl-color-palette-generator',
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
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_assets($hook) {
        if (!strpos($hook, 'gl-color-palette')) {
            return;
        }

        wp_enqueue_style(
            'gl-cpg-admin',
            GL_CPG_PLUGIN_URL . 'assets/css/admin.css',
            [],
            GL_CPG_VERSION
        );

        wp_enqueue_script(
            'gl-cpg-admin',
            GL_CPG_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            GL_CPG_VERSION,
            true
        );

        wp_localize_script('gl-cpg-admin', 'glCpgAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gl_cpg_admin'),
            'i18n' => [
                'generateError' => __('Error generating palette. Please try again.', 'gl-color-palette-generator'),
                'saveSuccess' => __('Palette saved successfully.', 'gl-color-palette-generator'),
                'saveError' => __('Error saving palette. Please try again.', 'gl-color-palette-generator'),
            ],
        ]);
    }

    /**
     * Render main admin page
     */
    public function render_main_page() {
        $palettes = $this->get_saved_palettes();
        include GL_CPG_PLUGIN_DIR . 'templates/admin/main-page.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        include GL_CPG_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }

    /**
     * Handle palette generation AJAX request
     */
    public function handle_generate_palette() {
        check_ajax_referer('gl_cpg_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access.', 'gl-color-palette-generator'));
        }

        $prompt = sanitize_text_field($_POST['prompt'] ?? '');
        if (empty($prompt)) {
            wp_send_json_error(__('Prompt is required.', 'gl-color-palette-generator'));
        }

        try {
            $generator = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator();
            $palette = $generator->generate_from_prompt($prompt);
            wp_send_json_success([
                'palette' => $palette,
                'message' => __('Palette generated successfully.', 'gl-color-palette-generator'),
            ]);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Get saved color palettes
     *
     * @return array
     */
    private function get_saved_palettes() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        $palettes = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC",
            ARRAY_A
        );

        return array_map(function($palette) {
            $palette['colors'] = json_decode($palette['colors'], true);
            return $palette;
        }, $palettes ?: []);
    }

    /**
     * Save color palette
     *
     * @param string $name Palette name.
     * @param array  $colors Palette colors.
     * @return int|false The number of rows inserted, or false on error.
     */
    public function save_palette($name, $colors) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        return $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field($name),
                'colors' => wp_json_encode($colors),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s']
        );
    }

    /**
     * Delete color palette
     *
     * @param int $id Palette ID.
     * @return int|false The number of rows deleted, or false on error.
     */
    public function delete_palette($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        return $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );
    }
}
