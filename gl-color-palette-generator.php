<?php
/**
 * Plugin Name: GL Color Palette Generator
 * Plugin URI: https://github.com/glerner/gl-color-palette-generator
 * Description: Advanced color palette generator with AI integration
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * Author: George Lerner
 * Author URI: https://website-tech.glerner.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gl-color-palette-generator
 * Domain Path: /languages
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GL_CPG_VERSION', '1.0.0');
define('GL_CPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GL_CPG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once GL_CPG_PLUGIN_DIR . 'includes/system/class-autoloader.php';

// Initialize the plugin
function gl_cpg_init() {
    $plugin = new GL_Color_Palette_Generator\Core\Setup();
    $plugin->init();
}
add_action('plugins_loaded', 'gl_cpg_init');
