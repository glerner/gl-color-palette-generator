<?php
/**
 * Bootstrap file for unit tests using WP_Mock
 *
 * @package GL_Color_Palette_Generator
 */

echo "\n=== Phase 1: Environment Setup ===\n";

// Load environment variables
$env_path = dirname(__DIR__) . '/.env.local.testing';
if (file_exists($env_path)) {
    echo "Loading environment from: $env_path\n";
    $env_content = parse_ini_file($env_path);
    foreach ($env_content as $key => $value) {
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

echo "\n=== Phase 2: Composer Autoloader ===\n";
echo "Loading composer autoloader\n";
require_once dirname(__FILE__) . '/../vendor/autoload.php';

echo "\n=== Phase 3: WP_Mock Setup ===\n";
echo "Initializing WP_Mock\n";
WP_Mock::bootstrap();

echo "\n=== Phase 4: Plugin Autoloader Setup ===\n";
echo "Loading plugin autoloader\n";
require_once dirname(__FILE__) . '/../includes/system/class-autoloader.php';
$autoloader = new GL_Color_Palette_Generator\System\Autoloader();
$autoloader->register();

echo "\n=== Phase 5: Test Base Classes Setup ===\n";
echo "Loading test base classes:\n";

$test_classes = [
    'test-case.php' => 'Test_Case',
    'integration/class-test-case-integration.php' => 'Integration_Test_Case',
];

foreach ($test_classes as $file => $class) {
    $path = __DIR__ . '/' . $file;
    echo "Loading $class from $path\n";
    if (!file_exists($path)) {
        echo "WARNING: Test class file not found: $path\n";
        continue;
    }
    require_once $path;
    
    $full_class = "GL_Color_Palette_Generator\\Tests\\$class";
    if (!class_exists($full_class)) {
        echo "WARNING: Class $full_class not found after loading $file\n";
    } else {
        echo "Successfully loaded $full_class\n";
    }
}

echo "\n=== Phase 6: Mock Classes Setup ===\n";
echo "Loading mock classes:\n";
$mock_classes = [
    'class-wp-error.php' => 'WP_Error',
    'class-wp-rest-request.php' => 'WP_REST_Request',
];

foreach ($mock_classes as $file => $class) {
    $path = __DIR__ . '/mocks/' . $file;
    echo "Loading $class from $path\n";
    if (!file_exists($path)) {
        echo "WARNING: Mock class file not found: $path\n";
        continue;
    }
    require_once $path;
    
    $full_class = "GL_Color_Palette_Generator\\Tests\\Mocks\\$class";
    if (!class_exists($full_class)) {
        echo "WARNING: Class $full_class not found after loading $file\n";
    } else {
        echo "Successfully loaded $full_class\n";
    }
}

echo "\n=== Phase 7: WordPress Functions Setup ===\n";
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

echo "\n=== Phase 8: Constants Setup ===\n";
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
