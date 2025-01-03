<?php
/**
 * Plugin Name: GL Color Palette Generator
 * Plugin URI: https://github.com/GeorgeLerner/gl-color-palette-generator
 * Description: Generate harmonious color palettes using AI, with support for multiple AI providers.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.1
 * Author: George Lerner
 * Author URI: https://website-tech.glerner.com/
 * Update URI: https://website-tech.glerner.com/plugins/color-palette-generator/
 * @link https://website-tech.glerner.com/
 * @package GL_Color_Palette_Generator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
define('GL_CPG_VERSION', '1.0.0');

// Plugin paths and URLs
define('GL_CPG_PLUGIN_FILE', __FILE__);
define('GL_CPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GL_CPG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Composer's autoloader
require_once GL_CPG_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Plugin compatibility check
 *
 * @return bool True if compatible, false otherwise
 */
function gl_cpg_check_compatibility() {
    global $wp_version;

    $php_version = phpversion();
    $min_php_version = '8.1';
    $min_wp_version = '6.2';

    $compatible = true;
    $messages = [];

    if (version_compare($php_version, $min_php_version, '<')) {
        $compatible = false;
        $messages[] = sprintf(
            __('GL Color Palette Generator requires PHP version %s or higher. Your current version is %s.', 'gl-color-palette-generator'),
            $min_php_version,
            $php_version
        );
    }

    if (version_compare($wp_version, $min_wp_version, '<')) {
        $compatible = false;
        $messages[] = sprintf(
            __('GL Color Palette Generator requires WordPress version %s or higher. Your current version is %s.', 'gl-color-palette-generator'),
            $min_wp_version,
            $wp_version
        );
    }

    if (!$compatible) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(implode('<br>', $messages));
    }

    return $compatible;
}

/**
 * Initialize plugin
 *
 * @return void
 */
function gl_cpg_init() {
    if (gl_cpg_check_compatibility()) {
        $plugin = GL_Color_Palette_Generator\Core\Plugin::get_instance();
        $plugin->init();
    }
}

/**
 * Plugin activation
 *
 * @return void
 */
function gl_cpg_activate() {
    if (gl_cpg_check_compatibility()) {
        // Create necessary database tables
        \GL_Color_Palette_Generator\Database\Schema::create_tables();

        // Initialize default settings
        \GL_Color_Palette_Generator\Settings\Settings_Manager::init_settings();

        // Clear any cached data
        wp_cache_flush();

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Plugin deactivation
 *
 * @return void
 */
function gl_cpg_deactivate() {
    // Clean up any plugin data if needed
    wp_cache_flush();
    flush_rewrite_rules();
}

// Register hooks
add_action('plugins_loaded', 'gl_cpg_init');
register_activation_hook(__FILE__, 'gl_cpg_activate');
register_deactivation_hook(__FILE__, 'gl_cpg_deactivate');
