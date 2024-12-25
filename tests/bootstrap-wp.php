<?php
/**
 * PHPUnit bootstrap file for WordPress integration tests
 *
 * @package GL_Color_Palette_Generator
 */

// Debug logging setup
$error_log = getenv('TEST_ERROR_LOG');
ini_set("log_errors", 1);
ini_set("error_log", $error_log ?: getenv('TEST_ERROR_LOG') ?: "/app/glcp-error.log");
error_log('Starting bootstrap-wp.php');

// Verify autoloader is loaded
if (!class_exists('Composer\\Autoload\\ClassLoader')) {
    error_log(' Autoloader not loaded. This should have been loaded by common.php');
    exit(1);
}
error_log(' Autoloader verified');

// Check required environment variables
$required_env_vars = array(
    'WP_TESTS_DIR' => getenv('WP_TESTS_DIR'),
    'WP_CORE_DIR' => getenv('WP_CORE_DIR'),
    'WP_TESTS_DB_NAME' => getenv('WP_TESTS_DB_NAME'),
    'WP_TESTS_DB_USER' => getenv('WP_TESTS_DB_USER'),
    'WP_TESTS_DB_PASSWORD' => getenv('WP_TESTS_DB_PASSWORD'),
    'WP_TESTS_DB_HOST' => getenv('WP_TESTS_DB_HOST')
);

foreach ($required_env_vars as $var => $value) {
    if (empty($value)) {
        error_log("Warning: {$var} environment variable is not set");
    } else {
        error_log("{$var}: {$value}");
    }
}

// Load WP testing environment
$tests_dir = getenv('WP_TESTS_DIR');

// Try to find WordPress test directory
$possible_dirs = array(
    getenv('WP_TESTS_DIR'),
    '/app/wordpress-phpunit',
    '/app/wp-content/plugins/wordpress-develop/tests/phpunit',
    dirname(__FILE__) . '/../../../../wordpress-develop/tests/phpunit',
    '/tmp/wordpress-tests-lib'
);

foreach ($possible_dirs as $dir) {
    if (!empty($dir) && is_dir($dir)) {
        $tests_dir = $dir;
        error_log("Found WordPress test directory at: {$dir}");
        break;
    }
}

if (empty($tests_dir) || !is_dir($tests_dir)) {
    error_log('Error: WordPress test directory not found. Tried: ' . implode(', ', array_filter($possible_dirs)));
    exit(1);
}

error_log('Using WP_TESTS_DIR: ' . $tests_dir);

// Define WordPress paths
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', '/app/wp-content');
    error_log('Defined WP_CONTENT_DIR as: ' . WP_CONTENT_DIR);
} else {
    error_log('WP_CONTENT_DIR already defined as: ' . WP_CONTENT_DIR);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(WP_CONTENT_DIR) . '/');
    error_log('Defined ABSPATH as: ' . ABSPATH);
} else {
    error_log('ABSPATH already defined as: ' . ABSPATH);
}

// Check if WordPress core files exist
$core_files = array(
    ABSPATH . 'wp-includes/PHPMailer/PHPMailer.php',
    ABSPATH . 'wp-includes/class-wp-error.php',
    WP_CONTENT_DIR . '/plugins/wordpress-develop/tests/phpunit/includes/bootstrap.php'
);

foreach ($core_files as $file) {
    if (!file_exists($file)) {
        error_log("Warning: Required WordPress file not found: {$file}");
    }
}

// Give access to tests_add_filter() function.
require_once $tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
    $plugin_dir = dirname(__DIR__);
    $plugin_file = $plugin_dir . '/gl-color-palette-generator.php';

    error_log('Loading plugin file: ' . $plugin_file);
    if (!file_exists($plugin_file)) {
        error_log('Error: Plugin file not found at: ' . $plugin_file);
        return;
    }

    require $plugin_file;
    error_log('Plugin loaded.');

    // Debug class loading
    error_log('Checking class existence after plugin load:');
    error_log('Ajax_Handler exists? ' . (class_exists('GL_Color_Palette_Generator\\Core\\Ajax_Handler') ? 'yes' : 'no'));

    // Debug class file
    $ajax_handler_file = $plugin_dir . '/includes/core/class-ajax-handler.php';
    error_log('Ajax_Handler file exists? ' . (file_exists($ajax_handler_file) ? 'yes' : 'no'));
    if (file_exists($ajax_handler_file)) {
        error_log('Ajax_Handler file path: ' . $ajax_handler_file);
    }
}

// Add filter to load plugin before any tests
tests_add_filter('muplugins_loaded', 'manually_load_plugin');

// Start up the WP testing environment
require_once $tests_dir . '/includes/bootstrap.php';

// Load test case classes
$test_files = [
    dirname(__FILE__) . '/class-test-case.php',
    dirname(__FILE__) . '/class-test-case-integration.php',
    dirname(__FILE__) . '/class-test-provider-integration.php'
];

foreach ($test_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Final class existence check
error_log('Final class existence check:');
error_log('Ajax_Handler exists? ' . (class_exists('GL_Color_Palette_Generator\\Core\\Ajax_Handler') ? 'yes' : 'no'));
error_log('Color_Utility exists? ' . (class_exists('GL_Color_Palette_Generator\\Color_Management\\Color_Utility') ? 'yes' : 'no'));
error_log('Test_Provider_Integration exists? ' . (class_exists('GL_Color_Palette_Generator\\Tests\\Test_Provider_Integration') ? 'yes' : 'no'));
