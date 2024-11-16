<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Optimizer Interface
 *
 * Defines the contract for optimizing color palettes for various purposes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteOptimizerInterface {
    /**
     * Optimizes a color palette.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param array        $options Optimization options.
     * @return ColorPalette Optimized palette.
     */
    public function optimizePalette(ColorPalette $palette, array $options = []): ColorPalette;

    /**
     * Optimizes for accessibility.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param string       $level   WCAG level ('A', 'AA', or 'AAA').
     * @return ColorPalette Optimized palette.
     */
    public function optimizeForAccessibility(ColorPalette $palette, string $level = 'AA'): ColorPalette;

    /**
     * Optimizes for harmony.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param string       $type    Harmony type (complementary, analogous, etc.).
     * @return ColorPalette Optimized palette.
     */
    public function optimizeForHarmony(ColorPalette $palette, string $type = 'complementary'): ColorPalette;

    /**
     * Optimizes for contrast.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param float        $target  Target contrast ratio.
     * @return ColorPalette Optimized palette.
     */
    public function optimizeForContrast(ColorPalette $palette, float $target = 4.5): ColorPalette;

    /**
     * Gets available optimization strategies.
     *
     * @return array List of available strategies.
     */
    public function getAvailableStrategies(): array;

    /**
     * Gets optimization options.
     *
     * @param string $strategy Strategy to get options for.
     * @return array Strategy options.
     */
    public function getStrategyOptions(string $strategy): array;
}
