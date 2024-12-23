<?php
/**
 * PHPUnit bootstrap file
 *
 * @package GL_Color_Palette_Generator
 */

// Load composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WordPress test suite
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

// Load our plugin's autoloader
require_once dirname(__DIR__) . '/includes/system/class-autoloader.php';

// Start up the WP testing environment
require_once $_tests_dir . '/includes/bootstrap.php';

// Load test base classes
require_once __DIR__ . '/test-case.php';
require_once __DIR__ . '/integration/test-provider-integration.php';

// Activate our plugin
tests_add_filter('muplugins_loaded', function() {
    require dirname(__DIR__) . '/gl-color-palette-generator.php';
});

// Initialize the plugin
do_action('plugins_loaded');
