<?php
/**
 * PHPUnit bootstrap file
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = '/app/wordpress-phpunit';
}

// Load test config
require_once dirname( __FILE__ ) . '/wp-tests-config.php';

// Load Composer autoloader
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

// Get the test suite from environment variable
$test_suite = getenv('TESTSUITE') ?: 'wp-unit';

if ($test_suite === 'wp-mock') {
    // Initialize WP_Mock for isolated tests
    \WP_Mock::setUsePatchwork( true );
    \WP_Mock::bootstrap();
} else {
    // Load WordPress test environment for integration and unit tests
    require_once $_tests_dir . '/includes/functions.php';
    require $_tests_dir . '/includes/bootstrap.php';
}

// Load base test classes
require_once dirname( __FILE__ ) . '/color-management/class-test-color-metrics-analyzer.php';
require_once dirname( __FILE__ ) . '/color-management/class-test-color-palette-generator.php';
require_once dirname( __FILE__ ) . '/color-management/class-test-color-utility.php';
require_once dirname( __FILE__ ) . '/settings/class-test-settings-manager.php';
require_once dirname( __FILE__ ) . '/core/class-test-setup.php';
require_once dirname( __FILE__ ) . '/integration/class-test-color-metrics-analyzer-integration.php';
require_once dirname( __FILE__ ) . '/integration/class-test-provider-integration.php';
require_once dirname( __FILE__ ) . '/admin/class-test-color-palette-admin.php';
require_once dirname( __FILE__ ) . '/api/class-test-rest-controller-export-import.php';
require_once dirname( __FILE__ ) . '/api/class-test-rest-controller-accessibility.php';
require_once dirname( __FILE__ ) . '/system/class-test-autoloader.php';
require_once dirname( __FILE__ ) . '/class-test-color-analysis.php';
