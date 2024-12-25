<?php
/**
 * PHPUnit bootstrap file for WordPress integration tests
 *
 * @package GL_Color_Palette_Generator
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
    require dirname( __DIR__ ) . '/gl-color-palette-generator.php';
}

tests_add_filter( 'muplugins_loaded', 'manually_load_plugin' );

// Now load our test case classes after WP_UnitTestCase is available
require_once dirname( __FILE__ ) . '/class-test-case.php';
require_once dirname( __FILE__ ) . '/class-test-case-integration.php';
require_once dirname( __FILE__ ) . '/class-test-provider-integration.php';

// Load Composer's autoloader last
require_once dirname( __DIR__ ) . '/vendor/autoload.php';
