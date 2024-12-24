<?php
/**
 * Bootstrap file for WordPress integration tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// Start output buffering before anything else
ob_start();

// Mock header functions BEFORE WordPress loads
if (!function_exists('header_remove')) {
    function header_remove($name = null) { return; }
}
if (!function_exists('header')) {
    function header($string, $replace = true, $http_response_code = null) { return; }
}
if (!function_exists('headers_sent')) {
    function headers_sent(&$file = null, &$line = null) { return false; }
}

// Load common bootstrap functionality.
require_once __DIR__ . '/common.php';

// Load local environment variables.
$dotenv = \Dotenv\Dotenv::createImmutable( dirname( __DIR__, 2 ), '.env.local.testing' );
$dotenv->load();

// Load WordPress test suite.
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Add filters to prevent header modifications BEFORE WordPress loads
tests_add_filter('wp_headers', '__return_empty_array', PHP_INT_MAX);
tests_add_filter('nocache_headers', '__return_empty_array', PHP_INT_MAX);
tests_add_filter('wp_redirect', '__return_false', PHP_INT_MAX);
tests_add_filter('wp_redirect_status', '__return_false', PHP_INT_MAX);

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
    $plugin_file = dirname( __DIR__, 2 ) . '/gl-color-palette-generator.php';
    require_once $plugin_file;

    // Initialize plugin after loading
    do_action('plugins_loaded');
    do_action('init');
}

// Add filter to load our plugin.
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
