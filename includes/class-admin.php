<?php
/**
 * Admin Interface Handler
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Handles all admin interface functionality
 */
class Admin {
    /**
     * Initialize admin features
     */
    public function init(): void {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_gl_generate_palette', [$this, 'handle_generate_palette']);
    }

    /**
     * Add admin menu page
     */
    public function add_menu_page(): void {
        add_menu_page(
            __('Color Palette Generator', 'gl-color-palette-generator'),
            __('Color Palette', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palette',
            [$this, 'render_admin_page'],
            'dashicons-art',
            30
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page
     */
    public function enqueue_assets(string $hook): void {
        if ('toplevel_page_gl-color-palette' !== $hook) {
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
            ['jquery', 'wp-util'],
            GL_COLOR_PALETTE_VERSION,
            true
        );

        wp_localize_script('gl-color-palette-admin', 'glColorPalette', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gl_color_palette_nonce'),
            'i18n' => [
                'generating' => __('Generating palette...', 'gl-color-palette-generator'),
                'error' => __('Error generating palette', 'gl-color-palette-generator')
            ]
        ]);
    }

    /**
     * Render admin page
     */
    public function render_admin_page(): void {
        include GL_COLOR_PALETTE_PATH . 'templates/admin-page.php';
    }

    /**
     * Handle AJAX palette generation
     */
    public function handle_generate_palette(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'gl-color-palette-generator'));
        }

        $context = filter_input(INPUT_POST, 'context', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];

        $generator = Color_Palette_Generator::get_instance();
        $result = $generator->generate_palette($context);

        if (isset($result['error'])) {
            wp_send_json_error($result['message']);
        }

        wp_send_json_success($result);
    }
} 
