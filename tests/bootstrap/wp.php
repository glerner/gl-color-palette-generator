<?php
/**
 * Bootstrap file for WordPress integration tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// Load common bootstrap functionality.
require_once __DIR__ . '/common.php';

// Prevent header output during tests
if (!headers_sent()) {
    header_remove();
}

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

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
    require dirname( __DIR__, 2 ) . '/gl-color-palette-generator.php';
}

// Add filter to load our plugin.
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
