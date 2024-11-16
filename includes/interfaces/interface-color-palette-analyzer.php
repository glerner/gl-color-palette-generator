<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Analyzer Interface
 *
 * Defines the contract for analyzing color palettes and their properties.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteAnalyzerInterface {
    /**
     * Analyzes a color palette.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Analysis results.
     */
    public function analyzePalette(ColorPalette $palette): array;

    /**
     * Calculates contrast ratios between colors.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Contrast ratios.
     */
    public function calculateContrastRatios(ColorPalette $palette): array;

    /**
     * Analyzes color harmony.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Harmony analysis.
     */
    public function analyzeHarmony(ColorPalette $palette): array;

    /**
     * Analyzes accessibility compliance.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @param string       $level   WCAG level ('A', 'AA', or 'AAA').
     * @return array Accessibility analysis.
     */
    public function analyzeAccessibility(ColorPalette $palette, string $level = 'AA'): array;

    /**
     * Gets color relationships.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Color relationships.
     */
    public function getColorRelationships(ColorPalette $palette): array;

    /**
     * Gets palette statistics.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Palette statistics.
     */
    public function getPaletteStats(ColorPalette $palette): array;
}
