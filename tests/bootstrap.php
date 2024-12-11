<?php
/**
 * PHPUnit bootstrap file
 *
 * This file sets up the testing environment for both WP_Mock and WordPress integration tests.
 * For WP_Mock tests, it initializes the mocking framework and loads mock-specific test files.
 * For WordPress integration tests, it sets up the WordPress testing environment and loads
 * integration test files.
 *
 * @package GLColorPalette
 */

// Load Composer autoloader
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

// Determine if we're running wp-mock tests based on the --group parameter
$is_wp_mock = false;
foreach ($_SERVER['argv'] as $arg) {
    if (strpos($arg, '--group') === 0) {
        $group = substr($arg, strpos($arg, '=') !== false ? strpos($arg, '=') + 1 : 7);
        if ($group === 'wp-mock') {
            $is_wp_mock = true;
            break;
        }
    }
}

if ($is_wp_mock) {
    // Initialize WP_Mock for isolated tests
    \WP_Mock::setUsePatchwork(true);
    \WP_Mock::bootstrap();

    // Define minimal WordPress constants needed for WP_Mock
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/app/');
    }
    if (!defined('WP_CONTENT_DIR')) {
        define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
    }
    if (!defined('WP_PLUGIN_DIR')) {
        define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
    }
    if (!defined('WPINC')) {
        define('WPINC', 'wp-includes');
    }

    // Load mock test files
    $mock_test_directories = ['interfaces', 'providers', 'api', 'admin'];
    $test_root = dirname(__FILE__);
    
    foreach ($mock_test_directories as $dir) {
        $dir_path = $test_root . '/' . $dir;
        if (is_dir($dir_path)) {
            foreach (glob($dir_path . '/test-*.php') as $test_file) {
                require_once $test_file;
            }
        }
    }
} else {
    // WordPress integration tests
    $_tests_dir = getenv('WP_TESTS_DIR');
    if (!$_tests_dir) {
        $_tests_dir = '/app/wordpress-phpunit';
    }

    // Load WordPress test environment
    require_once $_tests_dir . '/includes/functions.php';
    require_once $_tests_dir . '/includes/bootstrap.php';

    // Load integration test files
    $wp_test_directories = [
        'integration',
        'system',
        '.'  // Root directory for files like test-color-analysis.php
    ];
    
    foreach ($wp_test_directories as $dir) {
        $dir_path = dirname(__FILE__) . '/' . $dir;
        if (is_dir($dir_path)) {
            foreach (glob($dir_path . '/test-*.php') as $test_file) {
                require_once $test_file;
            }
        }
    }
}
