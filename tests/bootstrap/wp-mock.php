<?php
/**
 * Bootstrap file for WP_Mock tests
 *
 * This file sets up the testing environment for WordPress WP_Mock tests.
 * It handles:
 * - Environment variable loading
 * - WordPress function mocking
 * - WP_Mock expectations and configurations
 * - Test class setup
 * - Constants definition
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 * @since 1.0.0
 *
 * @see https://wp-mock.com/ WP_Mock Documentation
 * @see https://phpunit.de/ PHPUnit Documentation
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap {

	// Guard against multiple inclusion
	if ( defined( 'GL_COLOR_PALETTE_GENERATOR_BOOTSTRAP_LOADED' ) ) {
		return;
	}
	define( 'GL_COLOR_PALETTE_GENERATOR_BOOTSTRAP_LOADED', true );

	use GL_Color_Palette_Generator\Tests\Unit\Test_Case;

	echo "\n=== GL_Color_Palette_Generator WP_Mock Testing Bootstrap ===\n";
	echo "\n=== WP_Mock Phase 1: Environment Setup ===\n";

	// Load environment variables
	$env_path = dirname( __DIR__, 2 ) . '/.env.local.testing';
	if ( file_exists( $env_path ) ) {
		echo "Loading environment from: $env_path\n";
		$env_content = parse_ini_file( $env_path );
		foreach ( $env_content as $key => $value ) {
			if ( ! getenv( $key ) ) {
				putenv( "$key=$value" );
			}
		}
	}

	echo "\n=== WP_Mock Phase 2: Common Bootstrap ===\n";
	echo "Loading common bootstrap functionality\n";
	require_once __DIR__ . '/common.php';

	echo "\n=== WP_Mock Phase 3: WordPress Test Classes Setup ===\n";
}

namespace {
	// WP_UnitTestCase is defined in the global namespace
	if ( ! class_exists( 'WP_UnitTestCase' ) ) {
		// Initialize WP_Mock before defining test case class
		\WP_Mock::bootstrap();

		// Load WordPress function mocks
		require_once __DIR__ . '/wp-functions.php';

		$debug = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		foreach ( $debug as $trace ) {
			if ( isset( $trace['file'] ) &&
				strpos( $trace['file'], 'gl-color-palette-generator/tests/' ) !== false &&
				( strpos( $trace['file'], 'test-class-' ) !== false || strpos( $trace['file'], 'Test.php' ) !== false ) ) {
				echo 'Found test file: ' . $trace['file'] . "\n";
				if ( ! class_exists( 'GL_Color_Palette_Generator\Tests\Unit\Test_Case' ) ) {
					echo "NOTE: Consider updating this test file to use:\n";
					echo "    use GL_Color_Palette_Generator\\Tests\\Unit\\Test_Case;\n";
					echo "    class YourTestClass extends Unit_Test_Case\n";
				}
				break;
			}
		}

		class WP_UnitTestCase extends \PHPUnit\Framework\TestCase {
			protected function setUp(): void {
				parent::setUp();
				\WP_Mock::setUp();
			}

			protected function tearDown(): void {
				\WP_Mock::tearDown();
				parent::tearDown();
			}
		}

		echo "Created WP_UnitTestCase based on PHPUnit TestCase\n";
	}
}

namespace GL_Color_Palette_Generator\Tests\Bootstrap {

	echo "\n=== WP_Mock Phase 4: WP_Mock Setup ===\n";
	echo "Initializing WP_Mock\n";
	\WP_Mock::bootstrap();


	echo "\n=== WP_Mock Phase 5: WP_Mock Expectations ===\n";

	// Set up basic nonce expectation with fixed return value
	\WP_Mock::userFunction(
		'wp_create_nonce',
		array(
			'return' => 'test_nonce',
		)
	);

	// Mock admin_url to return a consistent URL format
	\WP_Mock::userFunction(
		'admin_url',
		array(
			'return_arg' => 0,
			'return'     => function ( $path = 'admin.php' ) {
				return 'http://example.com/wp-admin/' . ltrim( $path, '/' );
			},
		)
	);

	\WP_Mock::userFunction(
		'plugins_url',
		array(
			'return_arg' => 0,
			'return'     => function ( $path = '', $plugin = '' ) {
				return 'http://example.com/wp-content/plugins/' . ltrim( $path, '/' );
			},
		)
	);
	\WP_Mock::userFunction(
		'wp_json_encode',
		array(
			'return_arg' => 0,
			'return'     => function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
			},
		)
	);
	\WP_Mock::userFunction(
		'sanitize_text_field',
		array(
			'return_arg' => 0,
			'return'     => function ( $str ) {
				return trim( strip_tags( $str ) );
			},
		)
	);
	\WP_Mock::userFunction(
		'wp_parse_args',
		array(
			'return_arg' => 0,
			'return'     => function ( $args, $defaults = '' ) {
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
			},
		)
	);

	\WP_Mock::userFunction(
		'esc_html',
		array(
			'return_arg' => 0,
			'return'     => function ( $text ) {
				return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
			},
		)
	);
	\WP_Mock::userFunction(
		'esc_attr',
		array(
			'return_arg' => 0,
			'return'     => function ( $text ) {
				return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
			},
		)
	);

		// Mock translation functions
		\WP_Mock::userFunction(
			'__',
			array(
				'return_arg' => 0,
				'return'     => function ( $text ) {
					return $text; },
			)
		);

		\WP_Mock::userFunction(
			'_x',
			array(
				'return_arg' => 0,
				'return'     => function ( $text ) {
					return $text; },
			)
		);

		\WP_Mock::userFunction(
			'esc_html__',
			array(
				'return_arg' => 0,
				'return'     => function ( $text ) {
					return $text; },
			)
		);

		\WP_Mock::userFunction(
			'esc_attr__',
			array(
				'return_arg' => 0,
				'return'     => function ( $text ) {
					return $text; },
			)
		);

	echo "\n=== WP_Mock Phase 6: Constants Setup ===\n";
	echo "Defining constants:\n";

	if ( ! defined( 'ABSPATH' ) ) {
		echo "Defining ABSPATH constant\n";
		define( 'ABSPATH', '/app/' );
	}

	if ( ! defined( 'WP_CONTENT_DIR' ) ) {
		echo "Defining WP_CONTENT_DIR constant\n";
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	}

	if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
		echo "Defining WP_PLUGIN_DIR constant\n";
		define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	}

	echo "\n=== WP-Mock Bootstrap Complete ===\n";

}
