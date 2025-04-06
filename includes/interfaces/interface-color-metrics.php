<?php
/**
 * Color Metrics Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface for web color metrics calculations
 *
 * Focuses on essential web color metrics including:
 * - Brightness and contrast calculations
 * - Color relationships and balance
 * - Basic color properties
 */
interface Color_Metrics_Interface {
	/**
	 * Calculate perceived brightness
	 *
	 * @param string $color Color in hex format
	 * @return float Brightness value (0-1)
	 */
	public function calculate_brightness( string $color ): float;

	/**
	 * Calculate color saturation
	 *
	 * @param string $color Color in hex format
	 * @return float Saturation value (0-1)
	 */
	public function calculate_saturation( string $color ): float;

	/**
	 * Calculate color balance in a palette
	 *
	 * @param array $colors Array of colors in hex format
	 * @return array Balance metrics
	 */
	public function calculate_balance( array $colors ): array;

	/**
	 * Calculate color weight
	 *
	 * @param string $color Color in hex format
	 * @return float Weight value (0-1)
	 */
	public function calculate_weight( string $color ): float;

	/**
	 * Calculate color contrast ratio
	 *
	 * @deprecated 2.0.0 Use Color_Utility::get_contrast_ratio() instead
	 * @see Color_Utility::get_contrast_ratio()
	 *
	 * @param string $color1 First color in hex format
	 * @param string $color2 Second color in hex format
	 * @return float Contrast ratio between 1 and 21
	 */
	public function calculate_contrast_ratio( string $color1, string $color2 ): float;
}
