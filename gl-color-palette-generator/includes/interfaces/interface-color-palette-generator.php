<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Generator Interface
 *
 * Defines the contract for generating color palettes programmatically.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteGeneratorInterface {
    /**
     * Generates a new color palette.
     *
     * @param array $options Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generatePalette(array $options = []): ColorPalette;

    /**
     * Generates a complementary color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateComplementary(string $base_color, array $options = []): ColorPalette;

    /**
     * Generates an analogous color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateAnalogous(string $base_color, array $options = []): ColorPalette;

    /**
     * Generates a triadic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateTriadic(string $base_color, array $options = []): ColorPalette;

    /**
     * Generates a monochromatic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateMonochromatic(string $base_color, array $options = []): ColorPalette;

    /**
     * Gets available generation algorithms.
     *
     * @return array List of available algorithms.
     */
    public function getAvailableAlgorithms(): array;

    /**
     * Gets default generation options.
     *
     * @return array Default options.
     */
    public function getDefaultOptions(): array;
}
