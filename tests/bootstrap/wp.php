<?php
/**
 * Bootstrap file for WordPress test framework support
 *
 * This bootstrap file sets up the WordPress testing environment for any test
 * that extends WP_UnitTestCase. This includes unit tests, integration tests,
 * and any other tests that need WordPress core functionality.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// Start output buffering before anything else
ob_start();

echo "\n=== GL_Color_Palette_Generator WordPress Integration Testing Bootstrap ===\n";
echo "\n=== Phase 1: Environment Setup ===\n";

// Mock header functions BEFORE WordPress loads
if ( ! function_exists( 'header_remove' ) ) {
	echo "Defining header_remove function\n";
	function header_remove( $name = null ) {
		return; }
}
if ( ! function_exists( 'header' ) ) {
	echo "Defining header function\n";
	function header( $string, $replace = true, $http_response_code = null ) {
		return; }
}
if ( ! function_exists( 'headers_sent' ) ) {
	echo "Defining headers_sent function\n";
	function headers_sent( &$file = null, &$line = null ) {
		return false; }
}

echo "\n=== Phase 2: Common Bootstrap ===\n";
echo "Loading common bootstrap functionality\n";
require_once __DIR__ . '/common.php';

echo "\n=== Phase 3: Environment Variables ===\n";
// Load local environment variables.
$env_path = dirname( __DIR__, 2 ) . '/.env.testing';
if ( file_exists( $env_path ) ) {
	echo "Loading environment from: $env_path\n";
	$dotenv = \Dotenv\Dotenv::createImmutable( dirname( __DIR__, 2 ), '.env.testing' );
	$dotenv->load();
} else {
	echo "WARNING: Environment file not found: $env_path\n";
	echo "If you want to use environment variables, please copy .env.sample.testing to .env.testing and edit for your local environment\n";
}

echo "\n=== Phase 4: WordPress Test Suite Setup ===\n";
// Load WordPress test suite.
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
	echo "WP_TESTS_DIR not set, using default: $_tests_dir\n";
} else {
	echo "Using WP_TESTS_DIR: $_tests_dir\n";
}

// Give access to tests_add_filter() function.
$functions_path = $_tests_dir . '/includes/functions.php';
if ( file_exists( $functions_path ) ) {
	echo "Loading WordPress test functions\n";
	require_once $functions_path;
} else {
	echo "ERROR: WordPress test functions not found at: $functions_path\n";
	exit( 1 );
}

echo "\n=== Phase 5: WordPress Header Filters ===\n";
echo "Adding filters to prevent header modifications\n";
// Add filters to prevent header modifications BEFORE WordPress loads
tests_add_filter( 'wp_headers', '__return_empty_array', PHP_INT_MAX );
tests_add_filter( 'nocache_headers', '__return_empty_array', PHP_INT_MAX );
tests_add_filter( 'wp_redirect', '__return_false', PHP_INT_MAX );
tests_add_filter( 'wp_redirect_status', '__return_false', PHP_INT_MAX );

echo "\n=== Phase 6: Plugin Loading ===\n";
/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
	echo "Loading plugin file\n";
	$plugin_file = dirname( __DIR__, 2 ) . '/gl-color-palette-generator.php';
	if ( ! file_exists( $plugin_file ) ) {
		echo "ERROR: Plugin file not found: $plugin_file\n";
		exit( 1 );
	}
	require_once $plugin_file;

	echo "Initializing plugin\n";
	// Only trigger plugins_loaded, let WordPress handle init
	do_action( 'plugins_loaded' );
}

// Add filter to load our plugin.
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\manually_load_plugin' );

echo "\n=== Phase 7: WordPress Bootstrap ===\n";
echo "Loading WordPress test bootstrap\n";
$bootstrap_path = $_tests_dir . '/includes/bootstrap.php';
if ( file_exists( $bootstrap_path ) ) {
	require $bootstrap_path;
} else {
	echo "ERROR: WordPress bootstrap file not found at: $bootstrap_path\n";
	exit( 1 );
}

echo "\n=== WordPress Bootstrap Complete ===\n";

// Verify WP_UnitTestCase is available
if ( ! class_exists( 'WP_UnitTestCase' ) ) {
	echo "\n=== ERROR: WP_UnitTestCase Not Found ===\n";
	echo "WordPress test suite classes not properly loaded.\n";
	echo "Please check that:\n";
	echo "1. WP_TESTS_DIR is set correctly: $_tests_dir\n";
	echo "2. WordPress test bootstrap.php was loaded successfully\n";
	echo "3. WP_UnitTestCase class is available in the test suite\n";
	exit( 1 );
}

echo "\n=== WordPress Test Suite Verification Complete ===\n";
