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

// Prevent wp_die from printing or logging
tests_add_filter( 'wp_die_handler', function() {
	return function() {};
} );

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

// Load plugin files
require_once dirname( __DIR__ ) . '/gl-color-palette-generator.php';
