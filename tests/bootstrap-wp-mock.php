<?php 
/**
 * Bootstrap file for WP_Mock tests
 *
 * @package GL_Color_Palette_Generator
 */

// Load Composer autoloader
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';

// Load test base classes
require_once __DIR__ . '/providers/class-test-provider-mock.php';

// Load plugin autoloader
require_once dirname( __FILE__ ) . '/../includes/system/class-autoloader.php';
$autoloader = new GL_Color_Palette_Generator\System\Autoloader();
$autoloader->register();

// Load test classes
$test_dirs = [
    __DIR__ . '/providers',
    __DIR__ . '/integration',
    __DIR__ . '/api',
    __DIR__ . '/admin',
    __DIR__ . '/core'
];

foreach ($test_dirs as $dir) {
    if (is_dir($dir)) {
        foreach (glob("$dir/*.php") as $file) {
            if (basename($file) !== 'class-test-provider-mock.php') {
                require_once $file;
            }
        }
    }
}

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

// Load mock classes
require_once __DIR__ . '/mocks/class-wp-error.php';
require_once __DIR__ . '/mocks/class-wp-rest-request.php';
require_once __DIR__ . '/mocks/class-color-shade-generator.php';
