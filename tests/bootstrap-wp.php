<?php
/**
 * PHPUnit bootstrap file for WordPress integration tests
 *
 * @package GL_Color_Palette_Generator
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Point WordPress to our config file
$config_file = dirname( __FILE__ ) . '/wp-tests-config.php';
if ( ! file_exists( $config_file ) ) {
    echo "Error: wp-tests-config.php is missing from {$config_file}!\n";
    echo "Please run bin/install-wp-tests.sh to set up the testing environment." . PHP_EOL;
    exit( 1 );
}

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', $config_file );
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/gl-color-palette-generator.php';
}

// Start up the WP testing environment.
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
