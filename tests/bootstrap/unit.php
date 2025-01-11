<?php
/**
 * Bootstrap file for Unit tests
 *
 * Handles initialization of testing environment for unit tests.
 * Loads common dependencies and sets up WP_Mock.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace {
/*
None of this code requires the plugin's namespace (GL_Color_Palette_Generator\Tests\Bootstrap). All WordPress function mocks must be in the root namespace to properly intercept calls from WordPress code, and the rest of the code is just initialization that works fine in the root namespace.

See docs/development-guidelines.md for a grep command to find all the functions your plugin uses, that should be mocked.
*/

// Initialize error reporting
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);

// Define plugin directory
if (!defined('GL_CPG_PLUGIN_DIR')) {
    define('GL_CPG_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}

// Define WordPress constants
define('ABSPATH', dirname(__DIR__, 2) . '/');
define('WP_DEBUG', true);

// Load composer autoloader
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Initialize WP_Mock
\WP_Mock::bootstrap();

// Load WordPress function mocks
require_once __DIR__ . '/wp-functions.php';

echo "\n=== Unit Testing Bootstrap Complete ===\n";
}
