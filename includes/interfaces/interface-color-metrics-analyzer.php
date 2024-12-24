<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Color Metrics Analyzer Interface
 *
 * Defines the contract for analyzing and measuring color metrics and relationships.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */
interface Color_Metrics_Analyzer {
    /**
     * Analyzes a single color's metrics.
     *
     * @param string $color Color to analyze (hex format)
     * @return array|WP_Error Analysis results or error
     */
    public function analyze_color(string $color);

    /**
     * Analyzes print output characteristics.
     *
     * @param string $color Color to analyze
     * @return array|WP_Error Print analysis results or error
     */
    public function analyze_print_output(string $color);

    /**
     * Analyzes screen display characteristics.
     *
     * @param string $color Color to analyze
     * @return array|WP_Error Screen analysis results or error
     */
    public function analyze_screen_display(string $color);

    /**
     * Analyzes color display across different devices.
     *
     * @param string $color Color to analyze
     * @return array|WP_Error Device analysis results or error
     */
    public function analyze_device_display(string $color);

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float|WP_Error Contrast ratio or error
     */
    public function calculate_contrast(string $color1, string $color2);

    /**
     * Check accessibility compliance for color combinations
     *
     * @param array $colors Array of colors to check
     * @return array|WP_Error Array containing 'overall_score' and 'combinations' or error
     */
    public function check_accessibility(array $colors);

    /**
     * Analyze color harmony relationships
     *
     * @param array $colors Array of colors to analyze
     * @return array|WP_Error Array containing harmony analysis including 'complementary' or error
     */
    public function analyze_harmony(array $colors);
}
