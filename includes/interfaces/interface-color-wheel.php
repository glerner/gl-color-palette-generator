<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Wheel Interface
 *
 * Defines the contract for color wheel operations and harmony calculations.
 *
 * @package GL_Color_Palette_Generator
 * @since 1.0.0
 */
interface Color_Wheel_Interface {
	/**
	 * Calculate color harmonies
	 *
	 * @param string $base_color Base color in hex format
	 * @param string $harmony_type Type of harmony to calculate
	 * @param array  $options Additional options for harmony calculation
	 * @return array Array of harmonious colors
	 * @throws \InvalidArgumentException If harmony type is invalid
	 */
	public function calculate_harmonies( string $base_color, string $harmony_type, array $options = array() ): array;

	/**
	 * Calculate complementary colors
	 *
	 * @param array $hsl HSL values of base color
	 * @param array $options Additional options
	 * @return array Array of complementary colors
	 */
	public function calculate_complementary( array $hsl, array $options = array() ): array;

	/**
	 * Calculate analogous colors
	 *
	 * @param array $hsl HSL values of base color
	 * @param array $options Additional options
	 * @return array Array of analogous colors
	 */
	public function calculate_analogous( array $hsl, array $options = array() ): array;

	/**
	 * Calculate triadic colors
	 *
	 * @param array $hsl HSL values of base color
	 * @param array $options Additional options
	 * @return array Array of triadic colors
	 */
	public function calculate_triadic( array $hsl, array $options = array() ): array;

	/**
	 * Calculate tetradic colors
	 *
	 * @param array $hsl HSL values of base color
	 * @param array $options Additional options
	 * @return array Array of tetradic colors
	 */
	public function calculate_tetradic( array $hsl, array $options = array() ): array;

	/**
	 * Calculate split-complementary colors
	 *
	 * @param array $hsl HSL values of base color
	 * @param array $options Additional options
	 * @return array Array of split-complementary colors
	 */
	public function calculate_split_complementary( array $hsl, array $options = array() ): array;

	/**
	 * Apply variations to harmony colors
	 *
	 * @param array $colors Array of colors
	 * @param array $options Variation options
	 * @return array Colors with variations applied
	 */
	public function apply_harmony_variations( array $colors, array $options = array() ): array;
}
