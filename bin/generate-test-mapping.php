<?php
/**
 * Test Mapping Generator
 *
 * This script analyzes source files and test files to create a comprehensive mapping
 * between them, identifying which source files have tests, which don't, and what
 * naming patterns are used for the tests.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Testing
 */

// Configuration
define( 'PROJECT_ROOT', '/home/george/sites/gl-color-palette-generator' );
define( 'TEST_ROOT', PROJECT_ROOT . '/tests' );
define( 'SOURCE_FILES', PROJECT_ROOT . '/source_files.txt' );
define( 'TEST_FILES', PROJECT_ROOT . '/test_files_to_analyze.txt' );
define( 'MAPPING_LOG', PROJECT_ROOT . '/test_mapping_log.md' );
define( 'DEBUG_LOG', PROJECT_ROOT . '/test_mapping_debug.log' );

// Debug levels
define( 'DEBUG_NONE', 0 );
define( 'DEBUG_ERROR', 1 );
define( 'DEBUG_WARNING', 2 );
define( 'DEBUG_INFO', 3 );
define( 'DEBUG_VERBOSE', 4 );

// Set debug level (change as needed)
define( 'DEBUG_LEVEL', DEBUG_ERROR );

/**
 * Note: This is a standalone utility script that runs independently of WordPress.
 * While it follows WordPress coding standards for consistency, it does not load the WordPress core
 * and therefore uses native PHP functions instead of WordPress equivalents.
 */
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date

/**
 * Main class for generating test mapping
 */
class Test_Mapping_Generator {
	/**
	 * Source files array.
	 *
	 * @var array
	 */
	private $source_files = array();

	/**
	 * Test files array.
	 *
	 * @var array
	 */
	private $test_files = array();

	/**
	 * Files with tests array.
	 *
	 * @var array
	 */
	private $files_with_tests = array();

	/**
	 * Files without tests array.
	 *
	 * @var array
	 */
	private $files_without_tests = array();

	/**
	 * Mapping between source files and test files.
	 *
	 * @var array
	 */
	private $mapping = array();

	/**
	 * Patterns used to match test files.
	 *
	 * @var array
	 */
	private $patterns = array();

	/**
	 * Duplicate test files found.
	 *
	 * @var array
	 */
	private $duplicate_tests = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->patterns = array(
			'test-$base_name'               => function ( $base_name ) {
				return "test-{$base_name}";
			},
			'test-${base_name/-class/}'     => function ( $base_name ) {
				return 'test-' . str_replace( '-class', '', $base_name );
			},
			'test-$simple_name'             => function ( $base_name, $simple_name ) {
				return "test-{$simple_name}";
			},
			'test_$base_name'               => function ( $base_name ) {
				return "test_{$base_name}";
			},
			'test_$simple_name'             => function ( $base_name, $simple_name ) {
				return "test_{$simple_name}";
			},
			'${simple_name}_test'           => function ( $base_name, $simple_name ) {
				return "{$simple_name}_test";
			},
			'test-${simple_name}-validator' => function ( $base_name, $simple_name ) {
				return "test-{$simple_name}-validator";
			},
			'test-${simple_name}-manager'   => function ( $base_name, $simple_name ) {
				return "test-{$simple_name}-manager";
			},
			'test-settings-$simple_name'    => function ( $base_name, $simple_name ) {
				return "test-settings-{$simple_name}";
			},
		);
	}

	/**
	 * Log a debug message
	 *
	 * @param string $message The message to log.
	 * @param int    $level The debug level.
	 */
	private function debug( $message, $level = DEBUG_INFO ) {
		if ( $level <= DEBUG_LEVEL ) {
			$level_names = array(
				DEBUG_ERROR   => 'ERROR',
				DEBUG_WARNING => 'WARNING',
				DEBUG_INFO    => 'INFO',
				DEBUG_VERBOSE => 'VERBOSE',
			);

			$level_name  = isset( $level_names[ $level ] ) ? $level_names[ $level ] : 'UNKNOWN';
			$timestamp   = gmdate( 'Y-m-d H:i:s' );
			$log_message = "[$timestamp] [$level_name] $message" . PHP_EOL;

			// Using native PHP file operations since this is a standalone script without WordPress core loaded
			file_put_contents( DEBUG_LOG, $log_message, FILE_APPEND );

			if ( $level <= DEBUG_ERROR ) {
				// Using native PHP escaping since this is a standalone script without WordPress core loaded
				echo htmlspecialchars( $log_message, ENT_QUOTES, 'UTF-8' );
			}
		}
	}

	/**
	 * Load source files from file
	 *
	 * @return bool Success.
	 */
	public function load_source_files() {
		if ( ! file_exists( SOURCE_FILES ) ) {
			$this->debug( 'Source files list not found: ' . SOURCE_FILES, DEBUG_ERROR );
			return false;
		}

		$this->source_files = array_filter(
			file( SOURCE_FILES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ),
			function ( $file ) {
				// Skip files in .git/, .github/, node_modules/ directories and index.php files
				return ! preg_match( '/(\.git\/|\.github\/|\/node_modules\/|\/index\.php$)/', $file );
			}
		);

		$this->debug( 'Loaded ' . count( $this->source_files ) . ' source files' );
		return true;
	}

	/**
	 * Load test files from file
	 *
	 * @return bool Success.
	 */
	public function load_test_files() {
		if ( ! file_exists( TEST_FILES ) ) {
			$this->debug( 'Test files list not found: ' . TEST_FILES, DEBUG_ERROR );
			return false;
		}

		$this->test_files = file( TEST_FILES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

		// Check for duplicate test files
		$test_basenames = array();
		foreach ( $this->test_files as $test_file ) {
			$base_name = basename( $test_file );
			if ( isset( $test_basenames[ $base_name ] ) ) {
				$this->duplicate_tests[ $base_name ][] = $test_file;
				$this->duplicate_tests[ $base_name ][] = $test_basenames[ $base_name ];
				$this->debug( "Duplicate test file found: $base_name", DEBUG_WARNING );
			} else {
				$test_basenames[ $base_name ] = $test_file;
			}
		}

		$this->debug( 'Loaded ' . count( $this->test_files ) . ' test files' );
		if ( count( $this->duplicate_tests ) > 0 ) {
			$this->debug( 'Found ' . count( $this->duplicate_tests ) . ' duplicate test files', DEBUG_WARNING );
		}

		return true;
	}

	/**
	 * Generate mapping between source files and test files
	 *
	 * @return bool Success.
	 */
	public function generate_mapping() {
		$this->debug( 'Generating mapping...' );

		foreach ( $this->source_files as $source_file ) {
			$base_name   = basename( $source_file, '.php' );
			$simple_name = $this->get_simple_name( $base_name );

			$found           = false;
			$matched_tests   = array();
			$matched_pattern = '';

			// Try each pattern
			foreach ( $this->patterns as $pattern_name => $pattern_callback ) {
				$pattern = $pattern_callback( $base_name, $simple_name );

				// Look for test files matching this pattern
				foreach ( $this->test_files as $test_file ) {
					$test_basename = basename( $test_file, '.php' );
					if ( $test_basename === $pattern ) {
						$found           = true;
						$matched_pattern = $pattern_name;
						$matched_tests[] = $test_file;
						$this->debug( "Match found for $source_file: $test_file using pattern $pattern_name", DEBUG_VERBOSE );
					}
				}
			}

			if ( $found ) {
				$this->files_with_tests[ $source_file ] = array(
					'file'    => $source_file,
					'tests'   => $matched_tests,
					'pattern' => $matched_pattern,
				);
				$this->debug( "Added $source_file to files with tests" );
			} else {
				$this->files_without_tests[ $source_file ] = $source_file;
				$this->debug( "Added $source_file to files without tests" );
			}
		}

		$this->debug(
			'Mapping complete. ' . count( $this->files_with_tests ) . ' files with tests, ' .
					count( $this->files_without_tests ) . ' files without tests'
		);

		return true;
	}

	/**
	 * Get simple name by removing class prefix
	 *
	 * @param string $base_name The base name.
	 * @return string The simple name.
	 */
	private function get_simple_name( $base_name ) {
		$simple_name = $base_name;

		// Remove common prefixes
		$prefixes = array( 'class-', 'abstract-', 'interface-', 'trait-' );
		foreach ( $prefixes as $prefix ) {
			if ( 0 === strpos( $simple_name, $prefix ) ) {
				$simple_name = substr( $simple_name, strlen( $prefix ) );
				break;
			}
		}

		return $simple_name;
	}

	/**
	 * Generate the mapping log
	 *
	 * @return bool Success.
	 */
	public function generate_mapping_log() {
		$this->debug( 'Generating mapping log...' );

		$output   = array();
		$output[] = '# Test Mapping Log';
		// Use local time instead of GMT
		$local_time = date( 'Y-m-d H:i:s' );
		$timezone = date('T');
		$output[] = '# Generated on: ' . $local_time . ' (' . $timezone . ')';
		$output[] = '# Format: SOURCE_FILE: [Matching Test Files] or [NO TESTS FOUND]';
		$output[] = '';

		// Files with tests
		$output[] = '## Files with Tests';
		$output[] = '';

		foreach ( $this->files_with_tests as $source_file => $data ) {
			$output[] = "### $source_file:";
			foreach ( $data['tests'] as $test_file ) {
				$output[] = "- $test_file";
			}
			$output[] = "- Pattern matched: \"{$data['pattern']}\"";
			$output[] = '';
		}

		// Files without tests
		$output[] = '## Files without Tests';
		$output[] = '';

		foreach ( $this->files_without_tests as $source_file ) {
			$output[] = "### $source_file: NO TESTS FOUND";
			$output[] = '';
		}

		// Duplicate tests (if any)
		if ( count( $this->duplicate_tests ) > 0 ) {
			$output[] = '## Duplicate Test Files';
			$output[] = '';
			$output[] = 'The following test files have duplicate basenames, which may cause confusion:';
			$output[] = '';

			foreach ( $this->duplicate_tests as $base_name => $files ) {
				$output[] = "### $base_name:";
				foreach ( $files as $file ) {
					$output[] = "- $file";
				}
				$output[] = '';
			}
		}

		// Summary statistics
		$output[] = '## Summary Statistics';
		$output[] = '';
		$output[] = '- Total source files: ' . count( $this->source_files );
		$output[] = '- Files with tests: ' . count( $this->files_with_tests );
		$output[] = '- Files without tests: ' . count( $this->files_without_tests );
		$output[] = '- Total test files: ' . count( $this->test_files );

		$coverage = count( $this->source_files ) > 0
			? round( ( count( $this->files_with_tests ) / count( $this->source_files ) ) * 100, 1 )
			: 0;

		$output[] = "- Source file coverage: {$coverage}%";

		if ( count( $this->duplicate_tests ) > 0 ) {
			$output[] = '- Duplicate test files: ' . count( $this->duplicate_tests );
		}

		// Write to file
		// Using native PHP file operations since this is a standalone script without WordPress core loaded
		file_put_contents( MAPPING_LOG, implode( PHP_EOL, $output ) );

		$this->debug( 'Mapping log generated at ' . MAPPING_LOG );

		return true;
	}

	/**
	 * Run the generator
	 *
	 * @return bool Success.
	 */
	public function run() {
		// Initialize debug log
		// Using native PHP file operations since this is a standalone script without WordPress core loaded
		file_put_contents( DEBUG_LOG, "\n" . str_repeat( '-', 80 ) . "\n\n" . gmdate( 'Y-m-d H:i:s' ) . " - Test mapping generator started\n\n" );

		$this->debug( 'Starting test mapping generation' );

		if ( ! $this->load_source_files() ) {
			return false;
		}

		if ( ! $this->load_test_files() ) {
			return false;
		}

		if ( ! $this->generate_mapping() ) {
			return false;
		}

		if ( ! $this->generate_mapping_log() ) {
			return false;
		}

		$this->debug( 'Test mapping generation completed successfully' );

		return true;
	}
}

// Run the generator
$generator = new Test_Mapping_Generator();
if ( $generator->run() ) {
	// Using native PHP escaping since this is a standalone script without WordPress core loaded
	echo 'Test mapping log generated successfully at ' . htmlspecialchars( MAPPING_LOG, ENT_QUOTES, 'UTF-8' ) . PHP_EOL;
	echo 'Debug log available at ' . htmlspecialchars( DEBUG_LOG, ENT_QUOTES, 'UTF-8' ) . PHP_EOL;
	exit( 0 );
} else {
	// Using native PHP escaping since this is a standalone script without WordPress core loaded
	echo 'Error generating test mapping log. See ' . htmlspecialchars( DEBUG_LOG, ENT_QUOTES, 'UTF-8' ) . ' for details.' . PHP_EOL;
	exit( 1 );
}
