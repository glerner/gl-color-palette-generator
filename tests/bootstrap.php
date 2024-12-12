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
        // Handle both --group wp-mock and --group=wp-mock formats
        if (strpos($arg, '=') !== false) {
            // Format: --group=wp-mock
            $group = substr($arg, strpos($arg, '=') + 1);
        } else if (isset($_SERVER['argv'][array_search($arg, $_SERVER['argv']) + 1])) {
            // Format: --group wp-mock
            $group = $_SERVER['argv'][array_search($arg, $_SERVER['argv']) + 1];
        }

        if (isset($group) && $group === 'wp-mock') {
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

    // Load only WP-Mock test files
    $mock_test_directories = ['providers', 'api', 'admin'];
    $test_root = dirname(__FILE__);

    // Load the base provider mock test class first
    require_once $test_root . '/providers/test-provider-mock.php';

    // Then load all other WP-Mock test files
    foreach ($mock_test_directories as $dir) {
        $dir_path = $test_root . '/' . $dir;
        if (is_dir($dir_path)) {
            foreach (glob($dir_path . '/test-*.php') as $test_file) {
                // Skip integration test files and already loaded files
                if (strpos($test_file, 'integration') === false &&
                    basename($test_file) !== 'test-provider-mock.php') {
                    require_once $test_file;
                }
            }
        }
    }
} else {
    // Only load WordPress test suite for non-WP-Mock tests
    $_tests_dir = getenv('WP_TESTS_DIR');
    if (!$_tests_dir) {
        $_tests_dir = '/app/wordpress-phpunit';
    }

    // Check if we're running integration tests
    if (!file_exists($_tests_dir . '/includes/functions.php')) {
        die("WordPress test suite not found at {$_tests_dir}. Please run setup-plugin-tests.sh first.\n");
    }

    // Load WordPress test environment
    require_once $_tests_dir . '/includes/functions.php';
    require_once $_tests_dir . '/includes/bootstrap.php';

    // Load integration test files
    $wp_test_directories = [
        'integration',
        'interfaces',
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
