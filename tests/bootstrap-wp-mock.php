<?php
/**
 * Bootstrap file for WP_Mock tests
 *
 * @package GL_Color_Palette_Generator
 */

// Load Composer autoloader
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

// Load plugin autoloader
require_once dirname( __FILE__ ) . '/../includes/system/class-autoloader.php';
$autoloader = new GL_Color_Palette_Generator\System\Autoloader();
$autoloader->register();

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
if ( ! defined( 'GL_CPG_VERSION' ) ) {
    define( 'GL_CPG_VERSION', '1.0.0' );
}

// Mock WordPress functions
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'test_nonce';
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = 'admin.php') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}

// Load test dependencies
require_once __DIR__ . '/mocks/class-wp-error.php';  // WordPress error class mock
require_once __DIR__ . '/mocks/class-wp-rest-request.php';  // WordPress REST request class mock
require_once __DIR__ . '/mocks/class-color-shade-generator.php';  // Color shade generator mock
require_once __DIR__ . '/test-case.php';  // Base test case class for all tests
require_once __DIR__ . '/providers/test-provider-mock.php';  // Base class for provider tests
