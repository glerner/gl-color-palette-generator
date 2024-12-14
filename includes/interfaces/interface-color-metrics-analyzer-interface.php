<?php declare(strict_types=1);
/**
 * Color Metrics Analyzer Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface Color_Metrics_Analyzer
 *
 * Defines the contract for analyzing color metrics
 *
 * @since 1.0.0
 */
interface Color_Metrics_Analyzer {
    /**
     * Calculate color contrast ratio
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float|WP_Error Contrast ratio or error
     */
    public function calculate_contrast_ratio($color1, $color2);

    /**
     * Calculate color difference
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @param string $method Method to use (e.g., 'CIE76', 'CIE94', 'CIEDE2000')
     * @return float|WP_Error Color difference or error
     */
    public function calculate_color_difference($color1, $color2, $method = 'CIEDE2000');

    /**
     * Analyze color harmony
     *
     * @param array $colors Array of colors to analyze
     * @return array|WP_Error Harmony analysis results or error
     */
    public function analyze_harmony($colors);

    /**
     * Calculate color saturation
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Saturation value or error
     */
    public function calculate_saturation($color);

    /**
     * Calculate color brightness
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Brightness value or error
     */
    public function calculate_brightness($color);
}
