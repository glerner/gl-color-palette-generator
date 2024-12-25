<?php
/**
 * Common bootstrap functionality shared between all test types
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// Load Composer autoloader.
$autoloader = require dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Register additional PSR-4 prefixes if needed
if ($autoloader instanceof \Composer\Autoload\ClassLoader) {
    $autoloader->addPsr4('GL_Color_Palette_Generator\\Tests\\', dirname(__DIR__));
    $autoloader->register();
}

/**
 * Determine bootstrap type based on test file location or annotation
 *
 * @param string $test_file Full path to the test file.
 * @return string Bootstrap type ('wp' or 'wp-mock').
 */
function determine_bootstrap_type( $test_file ) {
    // Check for @bootstrap annotation.
    if ( file_exists( $test_file ) ) {
        $content = file_get_contents( $test_file );
        if ( preg_match( '/@bootstrap\s+(wp|wp-mock)/i', $content, $matches ) ) {
            return strtolower( $matches[1] );
        }
    }

    // If it's a directory, check all PHP files within it
    if ( is_dir( $test_file ) ) {
        $bootstrap_types = [];
        foreach ( glob( rtrim( $test_file, '/' ) . '/*.php' ) as $file ) {
            if ( file_exists( $file ) ) {
                $content = file_get_contents( $file );
                if ( preg_match( '/@bootstrap\s+(wp|wp-mock)/i', $content, $matches ) ) {
                    $bootstrap_types[] = strtolower( $matches[1] );
                }
            }
        }
        $bootstrap_types = array_unique( $bootstrap_types );
        if ( count( $bootstrap_types ) === 1 ) {
            return $bootstrap_types[0];
        }
        if ( count( $bootstrap_types ) > 1 ) {
            throw new \RuntimeException( 
                "Mixed bootstrap types found in directory: " . implode( ', ', $bootstrap_types )
            );
        }
    }

    // Fallback to directory-based detection
    if ( strpos( $test_file, '/unit/' ) !== false ) {
        return 'wp-mock';
    }
    if ( strpos( $test_file, '/integration/' ) !== false ) {
        return 'wp';
    }

    // Default to wp-mock for unknown locations
    return 'wp-mock';
}

// Load mock classes only if they exist
$mock_files = [
    __DIR__ . '/../mocks/class-wp-error.php',
    __DIR__ . '/../mocks/class-wp-rest-request.php'
];

foreach ($mock_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Load test base classes.
require_once __DIR__ . '/../class-test-case.php';
require_once __DIR__ . '/../class-test-case-integration.php';
