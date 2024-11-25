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
 * Initialize the plugin
 *
 * @return void
 */
function gl_cpg_init() {
    // Initialize error handling
    $error_handler = new \GL_Color_Palette_Generator\Utils\Error_Handler();
    $error_handler->init();

    // Initialize core setup
    $setup = new \GL_Color_Palette_Generator\Core\Setup();
    $setup->init();
}
add_action('plugins_loaded', 'gl_cpg_init');

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
