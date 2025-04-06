<?php
// Test with multisite enabled.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', getenv( 'WP_ROOT' ) ?: '/app/' );
}
if ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'wordpress_test' );
}
if ( ! defined( 'DB_USER' ) ) {
	define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'wordpress' );
}
if ( ! defined( 'DB_PASSWORD' ) ) {
	define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: 'wordpress' );
}
if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: 'database' );
}
if ( ! defined( 'DB_CHARSET' ) ) {
	define( 'DB_CHARSET', 'utf8' );
}
if ( ! defined( 'DB_COLLATE' ) ) {
	define( 'DB_COLLATE', '' );
}

$table_prefix = 'wptests_';

if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
	define( 'WP_TESTS_DOMAIN', 'example.org' );
}
if ( ! defined( 'WP_TESTS_EMAIL' ) ) {
	define( 'WP_TESTS_EMAIL', 'admin@example.org' );
}
if ( ! defined( 'WP_TESTS_TITLE' ) ) {
	define( 'WP_TESTS_TITLE', 'Test Blog' );
}
if ( ! defined( 'WP_PHP_BINARY' ) ) {
	define( 'WP_PHP_BINARY', 'php' );
}
if ( ! defined( 'WPLANG' ) ) {
	define( 'WPLANG', '' );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}
