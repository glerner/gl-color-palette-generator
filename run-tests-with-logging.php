<?php
ini_set('error_log', '/home/george/sites/gl-color-palette-generator/php-error.log');
ini_set('log_errors', 'On');
ini_set('display_errors', 'On');
error_reporting(E_ALL);

// Now run the tests
require_once __DIR__ . '/vendor/bin/phpunit';
