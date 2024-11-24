<?php
/**
 * Core setup class for the plugin
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Core;

use GL_Color_Palette_Generator\Admin\Color_Palette_Admin;
use GL_Color_Palette_Generator\Settings\Settings_Manager;

/**
 * Setup class to initialize the plugin
 */
class Setup {
    /**
     * Initialize the plugin
     */
    public function init() {
        $this->load_dependencies();
        $this->init_hooks();

        if (is_admin()) {
            $this->init_admin();
        }
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        / Load Settings Manager
        $settings = new Settings_Manager();
        $settings->init();

        / Initialize error handling
        $this->init_error_handling();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'load_textdomain']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        register_activation_hook(GL_CPG_PLUGIN_DIR . 'gl-color-palette-generator.php', [$this, 'activate']);
        register_deactivation_hook(GL_CPG_PLUGIN_DIR . 'gl-color-palette-generator.php', [$this, 'deactivate']);
    }

    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        $admin = new Color_Palette_Admin();
        $admin->init();
    }

    /**
     * Initialize error handling
     */
    private function init_error_handling() {
        $error_handler = new \GL_Color_Palette_Generator\Utils\Error_Handler();
        $error_handler->init();
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'gl-color-palette-generator',
            false,
            dirname(plugin_basename(GL_CPG_PLUGIN_DIR)) . '/languages/'
        );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $rest_controller = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Rest_Controller();
        $rest_controller->register_routes();
    }

    /**
     * Plugin activation hook
     */
    public function activate() {
        / Create necessary database tables
        $this->create_tables();

        / Set default options
        $this->set_default_options();

        / Clear any existing caches
        $this->clear_caches();
    }

    /**
     * Plugin deactivation hook
     */
    public function deactivate() {
        / Clean up any temporary data
        $this->cleanup_temporary_data();
    }

    /**
     * Create required database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gl_color_palettes (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            colors longtext NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = [
            'gl_cpg_ai_provider' => 'openai',
            'gl_cpg_cache_duration' => 3600,
            'gl_cpg_max_palettes' => 100,
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Clear any existing caches
     */
    private function clear_caches() {
        wp_cache_delete('gl_color_palettes', 'gl-color-palette-generator');
    }

    /**
     * Clean up temporary data
     */
    private function cleanup_temporary_data() {
        / Clean up any temporary files
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/gl-color-palette-generator/temp';
        if (is_dir($temp_dir)) {
            array_map('unlink', glob("$temp_dir/*.*"));
        }
    }
}
