<?php
/**
 * Color Harmonizer Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color harmony operations
 */
interface Color_Harmonizer_Interface {
	/**
	 * Get complementary color
	 *
	 * @param string $color Color in hex format
	 * @return string|WP_Error Complementary color or error
	 */
	public function get_complementary( $color );

	/**
	 * Get analogous colors
	 *
	 * @param string $color Color in hex format
	 * @param int    $count Number of colors (default 2)
	 * @return array|WP_Error Array of analogous colors or error
	 */
	public function get_analogous( $color, $count = 2 );

	/**
	 * Get triadic colors
	 *
	 * @param string $color Color in hex format
	 * @return array|WP_Error Array of triadic colors or error
	 */
	public function get_triadic( $color );

	/**
	 * Get tetradic colors
	 *
	 * @param string $color Color in hex format
	 * @return array|WP_Error Array of tetradic colors or error
	 */
	public function get_tetradic( $color );

	/**
	 * Get split complementary colors
	 *
	 * @param string $color Color in hex format
	 * @return array|WP_Error Array of split complementary colors or error
	 */
	public function get_split_complementary( $color );

	/**
	 * Get monochromatic variations
	 *
	 * @param string $color Color in hex format
	 * @param int    $count Number of variations
	 * @return array|WP_Error Array of monochromatic colors or error
	 */
	public function get_monochromatic( $color, $count = 5 );

	/**
	 * Adjust color harmony
	 *
	 * @param array $colors Array of colors to harmonize
	 * @param array $options Harmonization options
	 * @return array|WP_Error Harmonized colors or error
	 */
	public function harmonize_colors( $colors, $options = array() );

	/**
	 * Check if colors are harmonious
	 *
	 * @param array $colors Array of colors to check
	 * @param array $rules Harmony rules to apply
	 * @return bool|WP_Error True if harmonious, error if not
	 */
	public function are_harmonious( $colors, $rules = array() );

	/**
	 * Get harmony type between colors
	 *
	 * @param array $colors Array of colors to analyze
	 * @return string|WP_Error Harmony type or error
	 */
	public function get_harmony_type( $colors );

	/**
	 * Suggest harmony improvements
	 *
	 * @param array $colors Array of colors to improve
	 * @param array $options Improvement options
	 * @return array|WP_Error Suggested improvements or error
	 */
	public function suggest_harmony_improvements( $colors, $options = array() );

	/**
	 * Get available harmony rules
	 *
	 * @return array List of available harmony rules
	 */
	public function get_harmony_rules();

	/**
	 * Apply specific harmony rule
	 *
	 * @param string $rule_name Rule to apply
	 * @param array  $colors Colors to apply rule to
	 * @return array|WP_Error Modified colors or error
	 */
	public function apply_harmony_rule( $rule_name, $colors );
}
