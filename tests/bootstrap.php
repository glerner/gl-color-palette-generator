<?php
/**
 * PHPUnit bootstrap file
 */

/ Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

/ Load dotenv
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

/ Load WordPress test suite
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

/ Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname(__DIR__) . '/gl-color-palette-generator.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

/ Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php'; 
