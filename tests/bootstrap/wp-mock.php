<?php
/**
 * Bootstrap file for WP_Mock tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

echo "\n=== Phase 1: Environment Setup ===\n";

// Load environment variables
$env_path = dirname(__DIR__, 2) . '/.env.local.testing';
if (file_exists($env_path)) {
    echo "Loading environment from: $env_path\n";
    $env_content = parse_ini_file($env_path);
    foreach ($env_content as $key => $value) {
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

echo "\n=== Phase 2: Common Bootstrap ===\n";
echo "Loading common bootstrap functionality\n";
require_once __DIR__ . '/common.php';

echo "\n=== Phase 3: WP_Mock Setup ===\n";
echo "Initializing WP_Mock\n";
\WP_Mock::bootstrap();

echo "\n=== Phase 4: WordPress Functions Setup ===\n";
echo "Defining WordPress functions:\n";

if (!function_exists('esc_html')) {
    echo "Defining esc_html function\n";
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    echo "Defining esc_attr function\n";
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_create_nonce')) {
    echo "Defining wp_create_nonce function\n";
    function wp_create_nonce($action = -1) {
        return 'test_nonce';
    }
}

if (!function_exists('admin_url')) {
    echo "Defining admin_url function\n";
    function admin_url($path = 'admin.php') {
        return 'http://example.com/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('plugins_url')) {
    echo "Defining plugins_url function\n";
    function plugins_url($path = '', $plugin = '') {
        return 'http://example.com/wp-content/plugins/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_json_encode')) {
    echo "Defining wp_json_encode function\n";
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('sanitize_text_field')) {
    echo "Defining sanitize_text_field function\n";
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_parse_args')) {
    echo "Defining wp_parse_args function\n";
    function wp_parse_args($args, $defaults = '') {
        if (is_object($args)) {
            $parsed_args = get_object_vars($args);
        } elseif (is_array($args)) {
            $parsed_args = &$args;
        } else {
            parse_str($args, $parsed_args);
        }

        if (is_array($defaults)) {
            return array_merge($defaults, $parsed_args);
        }
        return $parsed_args;
    }
}

echo "\n=== Phase 5: Constants Setup ===\n";
echo "Defining constants:\n";

if (!defined('ABSPATH')) {
    echo "Defining ABSPATH constant\n";
    define('ABSPATH', '/app/');
}

if (!defined('WP_CONTENT_DIR')) {
    echo "Defining WP_CONTENT_DIR constant\n";
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    echo "Defining WP_PLUGIN_DIR constant\n";
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

echo "\n=== Bootstrap Complete ===\n";
