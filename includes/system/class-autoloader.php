<?php
/**
 * Legacy Autoloader for GL Color Palette Generator
 *
 * Note: This autoloader is no longer actively used as the plugin now uses Composer's
 * PSR-4 autoloader (configured in composer.json). This file is kept for:
 * 1. Historical reference of PSR-4 implementation in WordPress plugins
 * 2. Potential fallback if Composer autoloading is not available
 * 3. Documentation of the plugin's class loading strategy
 *
 * WARNING: Do not use this autoloader alongside Composer's autoloader!
 * Having multiple autoloaders for the same namespace causes subtle and hard-to-debug issues:
 * 1. Race conditions where different autoloaders try to load the same class
 * 2. Memory issues from classes being loaded multiple times
 * 3. Inconsistent class loading paths leading to "Class not found" errors
 * 4. Silent failures where the wrong version of a class is loaded
 *
 * These issues are particularly difficult to track down because:
 * - They may only occur in specific environments
 * - Error messages don't indicate autoloader conflicts
 * - Problems might manifest in seemingly unrelated parts of the code
 * - Issues could appear randomly depending on which autoloader wins the race
 *
 * @package GL_Color_Palette_Generator
 * @subpackage System
 * @deprecated Use Composer's autoloader instead
 */

namespace GL_Color_Palette_Generator\System;

/**
 * Class Autoloader
 *
 * Legacy PSR-4 autoloader implementation.
 * In this plugin's implementation, the namespace GL_Color_Palette_Generator
 * is mapped to the includes/ directory (as configured in composer.json),
 * with each sub-namespace mapping to a subdirectory within includes/.
 *
 * Note: This class is kept for reference but is not actively used.
 * The plugin now uses Composer's autoloader as configured in composer.json:
 * {
 *     "autoload": {
 *         "psr-4": {
 *             "GL_Color_Palette_Generator\\": "includes/"
 *         }
 *     }
 * }
 *
 * WARNING: Never use multiple autoloaders for the same namespace!
 * Having both this autoloader and Composer's autoloader active will cause:
 * - Race conditions in class loading
 * - Memory issues from duplicate class loading
 * - Inconsistent class resolution paths
 * - Hard-to-debug "Class not found" errors
 *
 * For example:
 * - GL_Color_Palette_Generator\Core\Ajax_Handler -> includes/core/class-ajax-handler.php
 * - GL_Color_Palette_Generator\Color_Management\Color_Utility -> includes/color-management/class-color-utility.php
 *
 * @deprecated Use Composer's autoloader instead
 */
class Autoloader {
	private $base_dir;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Go up two directories from the autoloader location to get to the plugin root
		$this->base_dir = dirname( dirname( __DIR__ ) ) . '/';
	}

	/**
	 * Register autoloader
	 */
	public static function register() {
		spl_autoload_register( array( new self(), 'autoload' ) );
	}

	/**
	 * Autoload classes
	 *
	 * @param string $class_name Full class name.
	 */
	public function autoload( $class_name ) {
		// Only handle our namespace
		if ( strpos( $class_name, 'GL_Color_Palette_Generator\\' ) !== 0 ) {
			return;
		}

		$file_path = $this->get_file_path( $class_name );
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		} else {
			error_log( "Failed to load class: $class_name at path: $file_path" );
		}
	}

	/**
	 * Get file path from class name
	 *
	 * Converts a fully qualified class name to its corresponding file path.
	 * Following this plugin's implementation:
	 * - Base namespace 'GL_Color_Palette_Generator' maps to includes/ (per composer.json)
	 * - Sub-namespaces map to subdirectories within includes/ (e.g., 'Core' -> 'core')
	 * - Class names are converted to lowercase, hyphenated filenames with appropriate prefix
	 *
	 * @param string $class_name Full class name (e.g., GL_Color_Palette_Generator\Core\Ajax_Handler)
	 * @return string File path (e.g., /plugin/root/includes/core/class-ajax-handler.php)
	 */
	private function get_file_path( $class_name ) {
		// Remove namespace prefix
		$class_name = str_replace( 'GL_Color_Palette_Generator\\', '', $class_name );

		// Split into parts
		$parts = explode( '\\', $class_name );

		// Get the actual class name (last part)
		$class_name = array_pop( $parts );

		// Convert directory names to lowercase and hyphenated
		$parts = array_map(
			function ( $part ) {
				return strtolower( str_replace( '_', '-', $part ) );
			},
			$parts
		);

		// Build path starting from plugin root
		// Add includes/ to match our composer.json PSR-4 configuration
		$path = $this->base_dir . 'includes/';
		if ( ! empty( $parts ) ) {
			$path .= implode( '/', $parts ) . '/';
		}

		// Determine file prefix based on type
		$prefix = 'class-';
		if ( strpos( $path, 'interfaces/' ) !== false ) {
			$prefix = 'interface-';
		} elseif ( strpos( $path, 'traits/' ) !== false ) {
			$prefix = 'trait-';
		}

		// Convert class name to file name format
		$file_name = $prefix . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		return $path . $file_name;
	}
}

// Register the autoloader
Autoloader::register();
