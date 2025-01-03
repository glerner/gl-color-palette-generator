<?php
/**
 * Admin Interface Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Admin
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Admin;

use GL_Color_Palette_Generator\Core\Abstract_Component;

/**
 * Class Admin_Interface
 *
 * Handles the admin interface for the plugin
 */
class Admin_Interface extends Abstract_Component {
    /**
     * Constructor
     */
    public function __construct() {
        $this->set_name('admin_interface');
        $this->set_version('1.0.0');
    }

    /**
     * Initialize the component
     *
     * @return bool True if initialization was successful
     */
    public function init(): bool {
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        return true;
    }

    /**
     * Register admin menus
     */
    public function register_menus(): void {
        add_menu_page(
            __('Color Palette Generator', 'gl-color-palette-generator'),
            __('Color Palettes', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palette-generator',
            [$this, 'render_main_page'],
            'dashicons-art'
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets(): void {
        wp_enqueue_style(
            'gl-color-palette-generator-admin',
            plugins_url('assets/css/admin.css', GL_CPG_PLUGIN_FILE),
            [],
            $this->get_version()
        );

        wp_enqueue_script(
            'gl-color-palette-generator-admin',
            plugins_url('assets/js/admin.js', GL_CPG_PLUGIN_FILE),
            ['jquery', 'wp-color-picker'],
            $this->get_version(),
            true
        );
    }

    /**
     * Render main admin page
     */
    public function render_main_page(): void {
        include_once GL_CPG_PLUGIN_DIR . 'templates/admin/main-page.php';
    }
}
