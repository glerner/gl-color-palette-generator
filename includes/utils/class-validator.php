<?php
/**
 * Validator Class
 *
 * Provides validation methods for various data types used throughout the plugin.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Utils
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validator class
 */
class Validator {
	/**
	 * Validate a hex color code
	 *
	 * @param string $color Color code to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_hex_color( string $color ): bool {
		return preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) === 1;
	}

	/**
	 * Validate an RGB color array
	 *
	 * @param array $color RGB color array with r, g, b keys
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_rgb_color( array $color ): bool {
		if ( ! isset( $color['r'], $color['g'], $color['b'] ) ) {
			return false;
		}

		foreach ( array( 'r', 'g', 'b' ) as $component ) {
			if ( ! is_int( $color[ $component ] ) ||
				$color[ $component ] < 0 ||
				$color[ $component ] > 255 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate an HSL color array
	 *
	 * @param array $color HSL color array with h, s, l keys
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_hsl_color( array $color ): bool {
		if ( ! isset( $color['h'], $color['s'], $color['l'] ) ) {
			return false;
		}

		if ( ! is_numeric( $color['h'] ) || $color['h'] < 0 || $color['h'] >= 360 ) {
			return false;
		}

		foreach ( array( 's', 'l' ) as $component ) {
			if ( ! is_numeric( $color[ $component ] ) ||
				$color[ $component ] < 0 ||
				$color[ $component ] > 100 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate a color name
	 *
	 * @param string $name Color name to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_color_name( string $name ): bool {
		return preg_match( '/^[a-zA-Z][a-zA-Z0-9\s-]{2,31}$/', $name ) === 1;
	}

	/**
	 * Validate a tag
	 *
	 * @param string $tag Tag to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_tag( string $tag ): bool {
		return preg_match( '/^[a-zA-Z0-9-]{2,32}$/', $tag ) === 1;
	}

	/**
	 * Validate a palette name
	 *
	 * @param string $name Palette name to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_palette_name( string $name ): bool {
		return preg_match( '/^[a-zA-Z0-9\s-]{3,64}$/', $name ) === 1;
	}

	/**
	 * Validate a description
	 *
	 * @param string $description Description to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_description( string $description ): bool {
		return mb_strlen( $description ) <= 500;
	}

	/**
	 * Validate a theme name
	 *
	 * @param string $theme Theme name to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_theme( string $theme ): bool {
		return preg_match( '/^[a-zA-Z0-9-]{2,32}$/', $theme ) === 1;
	}

	/**
	 * Validate a provider name
	 *
	 * @param string $provider Provider name to validate
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_provider( string $provider ): bool {
		return preg_match( '/^[a-zA-Z0-9-]{2,32}$/', $provider ) === 1;
	}
}
