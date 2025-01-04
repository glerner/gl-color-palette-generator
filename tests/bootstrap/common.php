<?php
/**
 * Common bootstrap functionality shared between all test types
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

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

$test_classes = [
    'unit/class-test-case.php' => 'Test_Case',
    'integration/class-test-case-integration.php' => 'Test_Case_Integration',
];

foreach ($test_classes as $file => $class) {
    $path = dirname(__DIR__) . '/' . $file;
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
 * @return string Bootstrap type ('wp' or 'wp-mock').
 */
function determine_bootstrap_type($test_file) {
    // Check for @bootstrap annotation.
    if (file_exists($test_file)) {
        $content = file_get_contents($test_file);
        if (preg_match('/@bootstrap\s+(wp|wp-mock)/i', $content, $matches)) {
            return strtolower($matches[1]);
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
            return $bootstrap_types[0];
        }
        if (count($bootstrap_types) > 1) {
            throw new \RuntimeException(
                "Mixed bootstrap types found in directory: " . implode(', ', $bootstrap_types)
            );
        }
    }

    // Fallback to directory-based detection
    if (strpos($test_file, '/unit/') !== false) {
        return 'wp-mock';
    }
    if (strpos($test_file, '/integration/') !== false) {
        return 'wp';
    }

    // Default to wp-mock for unknown locations
    return 'wp-mock';
}

echo "\n=== Common Bootstrap Complete ===\n";
