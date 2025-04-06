<?php declare(strict_types=1);
/**
 * Color Calculator Interface
 *
 * Defines the contract for color calculations and transformations.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface Color_Calculator
 *
 * Defines methods for color calculations including:
 * - Color space transformations
 * - Distance metrics
 * - Mixing and blending operations
 * - Harmony relationships
 */
interface Color_Calculator {
	/**
	 * Calculate color distance using CIEDE2000 formula
	 *
	 * @param string $color1 First color in hex format
	 * @param string $color2 Second color in hex format
	 * @return float Color difference value
	 */
	public function calculate_color_difference( string $color1, string $color2 ): float;

	/**
	 * Mix two colors with optional weight
	 *
	 * @param string $color1 First color in hex format
	 * @param string $color2 Second color in hex format
	 * @param float  $weight Mixing weight (0.0 to 1.0)
	 * @return string Resulting color in hex format
	 */
	public function mix_colors( string $color1, string $color2, float $weight = 0.5 ): string;

	/**
	 * Calculate color harmony relationships
	 *
	 * @param string $color Base color in hex format
	 * @param string $type Harmony type (complementary, analogous, triadic, etc.)
	 * @return array Array of related colors in hex format
	 */
	public function calculate_harmony( string $color, string $type ): array;

	/**
	 * Calculate weighted color blend
	 *
	 * @param array $colors Array of colors in hex format
	 * @param array $weights Array of corresponding weights
	 * @return string Resulting color in hex format
	 */
	public function calculate_weighted_blend( array $colors, array $weights ): string;
}
