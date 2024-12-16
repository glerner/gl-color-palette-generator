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

// Determine test type based on testsuite parameter
$is_unit_test = false;
foreach ($_SERVER['argv'] as $i => $arg) {
    if ($arg === '--testsuite' || $arg === '-t') {
        $next_arg = $_SERVER['argv'][$i + 1] ?? '';
        if ($next_arg === 'unit') {
            $is_unit_test = true;
            echo "\nRunning Unit Tests with WP_Mock...\n\n";
            break;
        } else if ($next_arg === 'integration') {
            echo "\nRunning Integration Tests with WordPress Test Framework...\n\n";
            break;
        }
    }
}

if ($is_unit_test) {
    // Load plugin autoloader for unit tests
    require_once dirname( __FILE__ ) . '/../includes/system/class-autoloader.php';

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

    // Load test dependencies
    require_once __DIR__ . '/class-test-case.php';
    require_once __DIR__ . '/providers/test-provider-mock.php';

    // Load only WP-Mock test files
    $mock_test_directories = ['providers', 'api', 'admin'];
    $test_root = dirname(__FILE__);

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
    // WordPress integration test setup
    $_tests_dir = getenv('WP_TESTS_DIR');

    // Support for the plugin being the root of a Git repository
    if (false !== $_tests_dir) {
        require_once $_tests_dir . '/includes/functions.php';

        // Load plugin autoloader before WordPress
        require_once dirname(__FILE__) . '/../vendor/autoload.php';

        // Load WordPress test environment
        require_once $_tests_dir . '/includes/bootstrap.php';

        // Load base test case first
        require_once dirname(__FILE__) . '/test-case.php';
        require_once dirname(__FILE__) . '/integration/test-provider-integration.php';

        // Then load other integration test files
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
                    // Skip already loaded files
                    if ($test_file !== dirname(__FILE__) . '/test-case.php' &&
                        $test_file !== dirname(__FILE__) . '/integration/test-provider-integration.php') {
                        require_once $test_file;
                    }
                }
            }
        }
    } else {
        echo "\nError: WP_TESTS_DIR environment variable is not set.\n";
        echo "Please run bin/install-wp-tests.sh to set up WordPress test environment.\n\n";
        exit(1);
    }
}
