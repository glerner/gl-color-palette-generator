<?php
/**
 * Color Accessibility Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color accessibility checks
 */
interface Color_Accessibility_Interface {
	/**
	 * Check contrast ratio between two colors
	 *
	 * @param string $color1 First color (hex format)
	 * @param string $color2 Second color (hex format)
	 * @return float|WP_Error Contrast ratio or error
	 */
	public function get_contrast_ratio( $color1, $color2 );

	/**
	 * Check if color combination meets WCAG contrast requirements
	 *
	 * @param string $foreground Foreground color (hex format)
	 * @param string $background Background color (hex format)
	 * @param array  $options Optional parameters for contrast check
	 * @return bool|WP_Error True if meets requirements, error if not
	 */
	public function meets_wcag_contrast( $foreground, $background, $options = array() ): bool|WP_Error;

	/**
	 * Check if color is readable on background
	 *
	 * @param string $color Text color (hex format)
	 * @param string $background Background color (hex format)
	 * @param array  $options Readability options
	 * @return bool|WP_Error True if readable, error if not
	 */
	public function is_readable( $color, $background, $options = array() );

	/**
	 * Get color combinations that meet contrast requirements
	 *
	 * @param string $base_color Base color to find combinations for (hex format)
	 * @param array  $options Search options
	 * @return array|WP_Error Array of valid color combinations or error
	 */
	public function get_accessible_combinations( $base_color, $options = array() );

	/**
	 * Check if palette is colorblind friendly
	 *
	 * @param array $colors Array of colors (hex format)
	 * @param array $types Types of colorblindness to check
	 * @return array|WP_Error Results for each type or error
	 */
	public function check_colorblind_friendly( $colors, $types = array( 'protanopia', 'deuteranopia', 'tritanopia' ) );

	/**
	 * Simulate how colors appear with different types of colorblindness
	 *
	 * @param array  $colors Array of colors (hex format)
	 * @param string $type Type of colorblindness
	 * @return array|WP_Error Simulated colors or error
	 */
	public function simulate_colorblind_vision( $colors, $type );

	/**
	 * Get color luminance
	 *
	 * @param string $color Color in hex format
	 * @return float|WP_Error Relative luminance value or error
	 */
	public function get_luminance( $color );

	/**
	 * Suggest accessible alternatives for a color
	 *
	 * @param string $color Color to find alternatives for (hex format)
	 * @param string $background Background color (hex format)
	 * @param array  $options Search options
	 * @return array|WP_Error Array of alternative colors or error
	 */
	public function suggest_accessible_alternatives( $color, $background, $options = array() );

	/**
	 * Check if colors are distinguishable
	 *
	 * @param array $colors Array of colors to check (hex format)
	 * @param array $options Check options
	 * @return bool|WP_Error True if distinguishable, error if not
	 */
	public function are_colors_distinguishable( $colors, $options = array() );

	/**
	 * Get accessibility report for colors
	 *
	 * @param array $colors Array of colors to check
	 * @param array $options Report options
	 * @return array|WP_Error Detailed accessibility report or error
	 */
	public function get_accessibility_report( $colors, $options = array() ): array|WP_Error;

	/**
	 * Check if colors meet brightness requirements
	 *
	 * @param string $color1 First color in hex format
	 * @param string $color2 Second color in hex format
	 * @param array  $options Optional parameters
	 * @return bool|WP_Error Whether the colors meet brightness requirements
	 */
	public function meets_brightness_requirements( string $color1, string $color2, array $options = array() ): bool|WP_Error;
}
