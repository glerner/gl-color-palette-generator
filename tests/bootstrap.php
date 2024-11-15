<?php
/**
 * PHPUnit bootstrap file
 */

// First, load WordPress test suite
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _register_theme() {
    // ...
}
tests_add_filter('muplugins_loaded', '_register_theme');

require_once $_tests_dir . '/includes/bootstrap.php';

// Now load our plugin
require dirname(dirname(__FILE__)) . '/gl-color-palette-generator.php';
