<?php
/**
 * Bootstrap file for PHPStan analysis
 *
 * This file defines constants that are normally defined by WordPress
 * to help PHPStan recognize them during static analysis.
 */

// Plugin constants
define('GL_CPG_VERSION', '1.0.0');
define('GL_CPG_PLUGIN_DIR', __DIR__ . '/');
define('GL_CPG_PLUGIN_URL', 'https://example.com/wp-content/plugins/gl-color-palette-generator/');

// WordPress constants
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/../../');
}
if (!defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}
