<?php
/**
 * Bootstrap file for WordPress integration tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

// Start output buffering before any output
ob_start();

// Set up WordPress test environment
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Load WordPress test bootstrap
require_once $_tests_dir . '/includes/functions.php';
require_once $_tests_dir . '/includes/bootstrap.php';

// Set up plugin test environment
$_plugin_dir = dirname(__DIR__);

// Load autoloader
require_once $_plugin_dir . '/vendor/autoload.php';

// Load test dependencies in correct order
$test_files = [
    'class-test-case.php',                  // Base test case
    'class-test-case-integration.php',      // Integration test case
    'class-test-provider-integration.php'   // Provider integration test case
];

foreach ($test_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log("Warning: Could not find test file: $file");
    }
}

// Load plugin files
require_once $_plugin_dir . '/gl-color-palette-generator.php';

// Initialize plugin
add_action('plugins_loaded', function() {
    do_action('gl_color_palette_generator_init');
});

// Clean output buffer
ob_end_clean();
