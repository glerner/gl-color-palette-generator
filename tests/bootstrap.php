<?php
/**
 * Main bootstrap file that delegates to the appropriate bootstrap type
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

echo "\n=== Phase 1: Environment Setup ===\n";

// Add clear separator to error log
$timestamp = date( 'Y-m-d H:i:s' );
error_log( "\n" . str_repeat( '=', 80 ) . "\n" );
error_log( "Test Run Started: {$timestamp}" );
error_log( str_repeat( '=', 80 ) . "\n" );

// Load common functionality first.
require_once __DIR__ . '/bootstrap/common.php';

echo "\n=== Phase 2: Test Type Detection ===\n";
// Get all test files being run.
$GLOBALS['argv'] = $GLOBALS['argv'] ?? array();
$test_files      = array();
foreach ( $GLOBALS['argv'] as $arg ) {
	if ( substr( $arg, -8 ) === 'Test.php' || substr( $arg, -4 ) === '.php' ) {
		$test_files[] = $arg;
	}
}

// Check all test files for consistent bootstrap type.
$bootstrap_types = array();
foreach ( $test_files as $test_file ) {
	$bootstrap_types[] = determine_bootstrap_type( $test_file );
}
$bootstrap_types = array_unique( $bootstrap_types );

if ( count( $bootstrap_types ) > 1 ) {
	echo "\nError: Mixed bootstrap types detected in test files:\n";
	foreach ( $test_files as $test_file ) {
		$type = determine_bootstrap_type( $test_file );
		echo "  {$test_file}: {$type}\n";
	}
	echo "\nPlease run tests with consistent bootstrap types together.\n";
	exit( 1 );
}

echo "\n=== Phase 3: Bootstrap Type Determination ===\n";
// Use the determined bootstrap type or default to wp-mock.
$bootstrap_type = $bootstrap_types[0] ?? 'wp-mock';
echo "Detected bootstrap type: $bootstrap_type\n";

echo "\n=== Phase 4: Bootstrap File Loading ===\n";
// Load the appropriate bootstrap file.
$bootstrap_file = __DIR__ . '/bootstrap/' . $bootstrap_type . '.php';
echo "Loading bootstrap file: $bootstrap_file\n";

if ( file_exists( $bootstrap_file ) ) {
	require_once $bootstrap_file;
	echo "Bootstrap complete.\n";
} else {
	echo "Error: Bootstrap file not found: $bootstrap_file\n";
	exit( 1 );
}
