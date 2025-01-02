<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_Error;

/**
 * Color Palette Analyzer Interface
 *
 * Defines the contract for analyzing color palettes and their properties.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Analyzer_Interface {
    /**
     * Analyzes a color palette.
     *
     * @param Color_Palette $palette Palette to analyze.
     * @param array        $options Analysis options.
     * @return array|WP_Error Analysis results or error.
     */
    public function analyze_palette(Color_Palette $palette, array $options = []): array|WP_Error;

    /**
     * Calculates contrast ratios between colors.
     *
     * @param array $colors List of colors to analyze.
     * @return array|WP_Error Contrast analysis results or error.
     */
    public function analyze_contrast(array $colors): array|WP_Error;

    /**
     * Analyzes color harmony.
     *
     * @param array $colors List of colors to analyze.
     * @return array|WP_Error Harmony analysis results or error.
     */
    public function analyze_harmony(array $colors): array|WP_Error;

    /**
     * Analyzes accessibility compliance.
     *
     * @param array $colors List of colors to analyze.
     * @param string       $level   WCAG level ('A', 'AA', or 'AAA').
     * @return array|WP_Error Accessibility analysis results or error.
     */
    public function analyze_accessibility(array $colors, string $level = 'AA'): array|WP_Error;

    /**
     * Gets color relationships.
     *
     * @param Color_Palette $palette Palette to analyze.
     * @return array Color relationships.
     */
    public function get_color_relationships(Color_Palette $palette): array;

    /**
     * Gets palette statistics.
     *
     * @param Color_Palette $palette Palette to analyze.
     * @return array Palette statistics.
     */
    public function get_palette_stats(Color_Palette $palette): array;
}
