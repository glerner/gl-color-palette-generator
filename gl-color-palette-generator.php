<?php
/**
 * Plugin Name: GL Color Palette Generator
 * Plugin URI: https://github.com/GeorgeLerner/gl-color-palette-generator
 * Description: Generate harmonious color palettes using AI, with support for multiple AI providers.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * Author: George Lerner
 * Author URI: https://website-tech.glerner.com/
 * Update URI: https://website-tech.glerner.com/plugins/color-palette-generator/
 * @link https://website-tech.glerner.com/
 * @package GL_Color_Palette_Generator
 */

namespace GL_Color_Palette_Generator;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
define('GL_CPG_VERSION', '1.0.0');

// Plugin directory
define('GL_CPG_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Plugin URL
define('GL_CPG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once GL_CPG_PLUGIN_DIR . 'includes/system/class-autoloader.php';
\GL_Color_Palette_Generator\System\Autoloader::register();

/**
 * Plugin compatibility check
 *
 * @return bool True if compatible, false otherwise
 */
function gl_cpg_check_compatibility() {
    global $wp_version;
    
    $php_version = phpversion();
    $min_php_version = '8.0';
    $min_wp_version = '6.2';
    
    $compatible = true;
    $messages = [];
    
    if (version_compare($php_version, $min_php_version, '<')) {
        $messages[] = sprintf(
            __('GL Color Palette Generator requires PHP version %s or higher. Your current version is %s.', 'gl-color-palette-generator'),
            $min_php_version,
            $php_version
        );
        $compatible = false;
    }
    
    if (version_compare($wp_version, $min_wp_version, '<')) {
        $messages[] = sprintf(
            __('GL Color Palette Generator requires WordPress version %s or higher. Your current version is %s.', 'gl-color-palette-generator'),
            $min_wp_version,
            $wp_version
        );
        $compatible = false;
    }
    
    if (!$compatible) {
        deactivate_plugins(plugin_basename(__FILE__));
        foreach ($messages as $message) {
            add_action('admin_notices', function() use ($message) {
                echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
            });
        }
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
    
    return $compatible;
}

/**
 * Check for plugin updates
 *
 * @return void
 */
function gl_cpg_check_for_updates() {
    if (!class_exists('\GL_Color_Palette_Generator\Core\Update_Checker')) {
        require_once GL_CPG_PLUGIN_DIR . 'includes/core/class-update-checker.php';
    }
    
    $update_checker = new \GL_Color_Palette_Generator\Core\Update_Checker(
        'https://website-tech.glerner.com/updates/gl-color-palette-generator/update-manifest.json',
        __FILE__,
        'gl-color-palette-generator'
    );
}

/**
 * Initialize plugin features
 *
 * @since 1.0.0
 * @return void
 */
function gl_cpg_init() {
    // Check compatibility before initializing
    if (!gl_cpg_check_compatibility()) {
        return;
    }

    // Initialize error handling
    $error_handler = new \GL_Color_Palette_Generator\Utils\Error_Handler();
    $error_handler->init();

    // Initialize core setup
    $setup = new \GL_Color_Palette_Generator\Core\Setup();
    $setup->init();
    
    // Check for updates
    gl_cpg_check_for_updates();
    
    // Initialize hooks
    gl_cpg_register_hooks();
}
add_action('plugins_loaded', 'gl_cpg_init');

/**
 * Register all hooks used by the plugin
 *
 * @since 1.0.0
 * @return void
 */
function gl_cpg_register_hooks() {
    add_action('admin_enqueue_scripts', '\GL_Color_Palette_Generator\Core\Assets::enqueue_admin_assets');
    add_action('wp_enqueue_scripts', '\GL_Color_Palette_Generator\Core\Assets::enqueue_frontend_assets');
    add_action('rest_api_init', '\GL_Color_Palette_Generator\Core\REST_API::register_routes');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), '\GL_Color_Palette_Generator\Core\Admin::add_action_links');
}

/**
 * Activation hook
 *
 * @return void
 */
function gl_cpg_activate() {
    require_once GL_CPG_PLUGIN_DIR . 'includes/core/class-activator.php';
    \GL_Color_Palette_Generator\Core\Activator::activate();
}
register_activation_hook(__FILE__, 'gl_cpg_activate');

/**
 * Deactivation hook
 *
 * @return void
 */
function gl_cpg_deactivate() {
    require_once GL_CPG_PLUGIN_DIR . 'includes/core/class-deactivator.php';
    \GL_Color_Palette_Generator\Core\Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'gl_cpg_deactivate');

// ... additional code for functionality and tests ...
