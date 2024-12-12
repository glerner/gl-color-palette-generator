<?php
/**
 * Bootstrap file for WP_Mock tests
 *
 * @package GL_Color_Palette_Generator
 */

// Load Composer autoloader
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

// Verify WP_Mock is available
if ( ! class_exists( '\WP_Mock' ) ) {
    throw new RuntimeException(
        'WP_Mock is not available. Please run composer install.'
    );
}

// Initialize WP_Mock
\WP_Mock::setUsePatchwork( true );
\WP_Mock::bootstrap();

// Define minimal WordPress constants needed for WP_Mock
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/app/' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}
if ( ! defined( 'WPINC' ) ) {
    define( 'WPINC', 'wp-includes' );
}

// Load test dependencies
require_once __DIR__ . '/test-case.php';  // Base test case class for all tests
require_once __DIR__ . '/providers/test-provider-mock.php';  // Base class for provider tests
