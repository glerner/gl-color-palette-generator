<?php
/**
 * Common bootstrap functionality shared between all test types
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// No use statement needed here since we're dynamically loading classes

echo "\n=== GL_Color_Palette_Generator Common Testing Bootstrap ===\n";
echo "\n=== Phase 1: Composer Autoloader ===\n";
echo "Loading composer autoloader\n";

// Load Composer autoloader.
$autoloader = require dirname(__DIR__, 2) . '/vendor/autoload.php';

// Register additional PSR-4 prefixes if needed
if ($autoloader instanceof \Composer\Autoload\ClassLoader) {
    echo "Registering PSR-4 prefixes\n";
    $autoloader->addPsr4('GL_Color_Palette_Generator\\Tests\\', dirname(__DIR__));
    $autoloader->register();
}

echo "\n=== Phase 2: Test Base Classes Setup ===\n";
echo "Loading test base classes:\n";

// Determine which bootstrap file included this file
$trace = debug_backtrace();
$including_file = basename($trace[0]['file']);
$bootstrap_type = match($including_file) {
    'unit.php' => ['Unit', 'unit'], // Unit tests without WordPress
    'wp-mock.php' => ['WP_Mock', 'wp-mock'],  // WP_Mock tests mock WordPress functions
    'wp.php' => ['Integration', 'integration'],  // Integration tests with actual WordPress code
    'integration.php' => ['Integration', 'integration'],  // Integration tests with actual WordPress code
    default => throw new \RuntimeException("Unknown bootstrap file: $including_file")
};
[$namespace_prefix, $path_prefix] = $bootstrap_type;

// Load test base classes
$test_classes = [];
if ($path_prefix === 'wp-mock') {
    $test_classes['class-wp-mock-test-case.php'] = 'WP_Mock_Test_Case';
} elseif ($path_prefix === 'unit') {
    $test_classes['class-unit-test-case.php'] = 'Unit_Test_Case';
} elseif ($path_prefix === 'integration') {
    $test_classes['class-test-case-integration.php'] = 'Test_Case_Integration';
}

echo "For test file $including_file, Detected bootstrap type: $namespace_prefix and $path_prefix\n";

// Files that are part of test infrastructure (not to be tested)
$excluded_test_files = [
    'bootstrap.php',
    'bootstrap/common.php',
    'bootstrap/unit.php',
    'bootstrap/wp-mock.php',
    'bootstrap/wp.php',
    'bootstrap/wp-functions.php',
    'base/class-unit-test-case.php',
    'base/class-wp-mock-test-case.php',
    'base/class-test-helpers.php',
    'base/class-test-printer.php'
];


foreach ($test_classes as $file => $class) {

    $paths = [
        dirname(__DIR__) . "/$file",
        dirname(__DIR__) . "/unit/$file",
        dirname(__DIR__) . "/integration/$file",
        dirname(__DIR__) . "/wp-mock/$file"
    ];

    $loaded = false;
    foreach ($paths as $path) {

        if (file_exists($path)) {
            echo "Loading $class from $path\n";
            require_once $path;
            $loaded = true;

            // Determine namespace based on file location
            $class_namespace = $namespace_prefix; // default to bootstrap's namespace
            if (str_contains($path, '/integration/')) {
                $class_namespace = 'Integration';
            } elseif (str_contains($path, '/unit/')) {
                $class_namespace = 'Unit';
            } elseif (str_contains($path, '/wp-mock/')) {
                $class_namespace = 'WP_Mock';
            }
            $full_class = "GL_Color_Palette_Generator\\Tests\\{$class_namespace}\\$class";
            if (class_exists($full_class)) {
                echo "Successfully loaded $full_class\n";
                break;
            }
            echo "WARNING: Class $full_class not found after loading $file\n";
            break;
        }
    }
}


// While test files are still using wrong class "use" statements, make aliases
if (class_exists("GL_Color_Palette_Generator\\Tests\\Unit\\Unit_Test_Case")) {
    class_alias(
        "GL_Color_Palette_Generator\\Tests\\Unit\\Unit_Test_Case",
        "GL_Color_Palette_Generator\\Tests\\Unit_Test_Case"
    );
}
if (class_exists("GL_Color_Palette_Generator\\Tests\\WP_Mock\\WP_Mock_Test_Case")) {
    class_alias(
        "GL_Color_Palette_Generator\\Tests\\WP_Mock\\WP_Mock_Test_Case",
        "GL_Color_Palette_Generator\\Tests\\WP_Mock_Test_Case"
    );
}

echo "\n=== Phase 3: Mock Classes Setup ===\n";
echo "Loading mock classes:\n";

$mock_classes = [
    'class-wp-error.php' => 'WP_Error',
    'class-wp-rest-request.php' => 'WP_REST_Request'
];

foreach ($mock_classes as $file => $class) {
    $path = dirname(__DIR__) . '/unit/mocks/' . $file;

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

/**
 * Determine bootstrap type based on test file location or annotation
 *
 * @param string $test_file Full path to the test file.
 * @return array Bootstrap type info [namespace, path].
 */
function determine_bootstrap_type($test_file) {
    // Check for @bootstrap annotation.
    if (file_exists($test_file)) {
        $content = file_get_contents($test_file);
        if (preg_match('/@bootstrap\s+(wp|wp-mock)/i', $content, $matches)) {
            return match(strtolower($matches[1])) {
                'wp-mock' => ['WP_Mock', 'wp-mock'],
                'wp' => ['Integration', 'integration']
            };
        }
    }

    // If it's a directory, check all PHP files within it
    if (is_dir($test_file)) {
        $bootstrap_types = [];
        foreach (glob(rtrim($test_file, '/') . '/*.php') as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/@bootstrap\s+(wp|wp-mock)/i', $content, $matches)) {
                    $bootstrap_types[] = strtolower($matches[1]);
                }
            }
        }
        $bootstrap_types = array_unique($bootstrap_types);
        if (count($bootstrap_types) === 1) {
            return match($bootstrap_types[0]) {
                'wp-mock' => ['Unit', 'unit'],
                'wp' => ['Integration', 'integration']
            };
        }
        if (count($bootstrap_types) > 1) {
            throw new \RuntimeException(
                "Mixed bootstrap types found in directory: " . implode(', ', $bootstrap_types)
            );
        }
    }

    // Fallback to directory-based detection
    if (strpos($test_file, '/unit/') !== false) {
        return ['Unit', 'unit'];
    }
    if (strpos($test_file, '/integration/') !== false) {
        return ['Integration', 'integration'];
    }

    // Default to wp-mock for unknown locations
    return ['Unit', 'unit'];
}

echo "\n=== Common Bootstrap Complete ===\n";
