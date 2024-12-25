<?php
/**
 * PHPUnit bootstrap file for WP_Mock tests
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
require_once dirname(__DIR__) . '/vendor/autoload.php';

echo "\n=== Phase 3: WP_Mock Setup ===\n";
echo "Initializing WP_Mock\n";
WP_Mock::bootstrap();

echo "\n=== Phase 4: Test Base Classes Setup ===\n";
echo "Loading test base classes:\n";

$test_classes = [
    'test-case.php' => 'Test_Case',
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

echo "\n=== Phase 5: Mock Classes Setup ===\n";
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

    if (!class_exists($class)) {
        echo "WARNING: Class $class not found after loading $file\n";
    } else {
        echo "Successfully loaded $class\n";
    }
}

echo "\n=== Phase 6: Test Autoloader Setup ===\n";
// Register test autoloader
spl_autoload_register(function ($class) {
    // Only handle our test namespace
    if (strpos($class, 'GL_Color_Palette_Generator\\Tests\\') !== 0) {
        return;
    }

    // Convert namespace to path
    $path = str_replace('\\', '/', $class);
    $path = str_replace('GL_Color_Palette_Generator/Tests/', '', $path);
    $path = __DIR__ . '/' . $path . '.php';

    echo "Attempting to autoload: $class\n";
    echo "Looking for file: $path\n";

    if (file_exists($path)) {
        require_once $path;
        if (class_exists($class)) {
            echo "Successfully loaded $class\n";
        } else {
            echo "WARNING: Class $class not found after loading $path\n";
        }
    } else {
        echo "WARNING: File not found: $path\n";
    }
});

echo "\n=== Phase 7: WordPress Functions Setup ===\n";
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
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

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return 'http://example.com/wp-content/plugins/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_parse_args')) {
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
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/../');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

echo "\n=== Phase 9: Test Classes Setup ===\n";
$test_dirs = [
    __DIR__ . '/providers',
    __DIR__ . '/api',
    __DIR__ . '/admin',
    __DIR__ . '/core',
    __DIR__ . '/cache',
    __DIR__ . '/types',
    __DIR__ . '/system',
    __DIR__ . '/classes',
    __DIR__ . '/export',
    __DIR__ . '/interfaces',
    __DIR__ . '/education'
];

$wp_test_files = [
    'test-theme-json-generator.php',
    'class-test-color-analysis.php',
    'test-sample.php'
];

foreach ($test_dirs as $dir) {
    if (is_dir($dir)) {
        foreach (glob("$dir/*.php") as $file) {
            $basename = basename($file);
            // Skip base classes and WordPress test files
            if ($basename !== 'class-test-provider-mock.php' &&
                !in_array($basename, $wp_test_files)) {
                require_once $file;
            }
        }
    }
}
