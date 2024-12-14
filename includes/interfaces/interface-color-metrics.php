<?php
/**
 * Color Metrics Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color metrics calculations
 */
interface Color_Metrics_Interface {
    /**
     * Calculate color difference using CIEDE2000
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float|WP_Error Difference value or error
     */
    public function calculate_color_difference($color1, $color2);

    /**
     * Calculate perceived brightness
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Brightness value (0-1) or error
     */
    public function calculate_brightness($color);

    /**
     * Calculate color saturation
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Saturation value (0-1) or error
     */
    public function calculate_saturation($color);

    /**
     * Calculate color temperature
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Temperature in Kelvin or error
     */
    public function calculate_temperature($color);

    /**
     * Calculate color harmony score
     *
     * @param array $colors Array of colors in hex format
     * @return float|WP_Error Harmony score (0-1) or error
     */
    public function calculate_harmony_score($colors);

    /**
     * Calculate color complexity
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Complexity score (0-1) or error
     */
    public function calculate_complexity($color);

    /**
     * Calculate color dominance in a palette
     *
     * @param string $color Color in hex format
     * @param array  $palette Array of palette colors
     * @return float|WP_Error Dominance score (0-1) or error
     */
    public function calculate_dominance($color, $palette);

    /**
     * Calculate color balance in a palette
     *
     * @param array $colors Array of colors in hex format
     * @return array|WP_Error Balance metrics or error
     */
    public function calculate_balance($colors);

    /**
     * Calculate color weight
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Weight value (0-1) or error
     */
    public function calculate_weight($color);

    /**
     * Calculate color energy
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Energy value (0-1) or error
     */
    public function calculate_energy($color);

    /**
     * Calculate color contrast ratio
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float|WP_Error Contrast ratio or error
     */
    public function calculate_contrast_ratio($color1, $color2);

    /**
     * Get comprehensive color metrics
     *
     * @param string $color Color in hex format
     * @return array|WP_Error Array of all metrics or error
     */
    public function get_color_metrics($color);

    /**
     * Get comprehensive palette metrics
     *
     * @param array $colors Array of colors in hex format
     * @return array|WP_Error Array of palette metrics or error
     */
    public function get_palette_metrics($colors);
}
