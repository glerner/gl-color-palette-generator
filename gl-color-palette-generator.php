<?php
/**
 * Plugin Name:       GL Color Palette Generator
  * Plugin URI:        https://github.com/GeorgeLerner/ gl-color-palette-generator
 * Description:       AI-powered color palette generator for WordPress
 * Version:          1.0.0
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            George Lerner
 * Author URI:        https://website-tech.glerner.com/
 * License:          GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      gl-color-palette-generator
 * Domain Path:      /languages
 *
 * @package GLColorPalette
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Use existing autoloader if available (e.g., through Composer)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback autoloader
    spl_autoload_register(function ($class) {
        $namespace = 'GLColorPalette\\';
        if (strpos($class, $namespace) !== 0) {
            return;
        }

        $class = str_replace($namespace, '', $class);
        $class = str_replace('_', '-', $class);
        $class = strtolower($class);

        $file = plugin_dir_path(__FILE__) . 'includes/class-' . $class . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}
