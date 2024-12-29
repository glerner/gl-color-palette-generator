<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Metrics Analyzer Interface
 *
 * Defines the contract for analyzing and measuring web color metrics and relationships.
 * Focused on web-essential features like contrast ratios, accessibility,
 * and basic color analysis.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */
interface Color_Metrics_Analyzer {
    /**
     * Get comprehensive color analysis
     *
     * @param string $color Hex color code
     * @return array Color analysis data including RGB, HSL, and metrics
     */
    public function get_color_analysis(string $color): array;

    /**
     * Calculate perceived brightness
     *
     * @param array $rgb RGB values
     * @return float Perceived brightness (0-255)
     */
    public function calculate_perceived_brightness(array $rgb): float;

    /**
     * Analyze contrast between colors
     *
     * @param array $colors Array of hex color codes
     * @return array Contrast analysis results with accessibility scores
     */
    public function analyze_contrast(array $colors): array;

    /**
     * Check if colors are visually distinct
     *
     * @param array $colors Array of hex color codes
     * @return bool True if colors are visually distinct
     */
    public function are_colors_distinct(array $colors): bool;
}
