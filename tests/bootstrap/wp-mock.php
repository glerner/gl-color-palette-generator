<?php
/**
 * Bootstrap file for WP_Mock tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// Load common bootstrap functionality.
require_once __DIR__ . '/common.php';

// Initialize WP_Mock.
\WP_Mock::bootstrap();

// Define WordPress functions that we need to mock.
if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action = -1 ) {
        return 'test_nonce';
    }
}

if ( ! function_exists( 'admin_url' ) ) {
    function admin_url( $path = 'admin.php' ) {
        return 'http://example.com/wp-admin/' . ltrim( $path, '/' );
    }
}

if ( ! function_exists( 'plugins_url' ) ) {
    function plugins_url( $path = '', $plugin = '' ) {
        return 'http://example.com/wp-content/plugins/' . ltrim( $path, '/' );
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return json_encode( $data, $options, $depth );
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = '' ) {
        if ( is_object( $args ) ) {
            $parsed_args = get_object_vars( $args );
        } elseif ( is_array( $args ) ) {
            $parsed_args = &$args;
        } else {
            parse_str( $args, $parsed_args );
        }

        if ( is_array( $defaults ) ) {
            return array_merge( $defaults, $parsed_args );
        }
        return $parsed_args;
    }
}

// Define constants needed for testing.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/app/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}
