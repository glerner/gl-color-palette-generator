<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Formatter Interface
 *
 * Defines the contract for formatting color palettes and their values.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteFormatterInterface {
    /**
     * Formats a color to the specified format.
     *
     * @param string $color  Color value to format.
     * @param string $format Target format (hex, rgb, hsl, etc.).
     * @return string Formatted color value.
     */
    public function formatColor(string $color, string $format): string;

    /**
     * Validates a color format.
     *
     * @param string $color  Color to validate.
     * @param string $format Format to validate against.
     * @return bool True if valid.
     */
    public function isValidFormat(string $color, string $format): bool;

    /**
     * Gets supported color formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array;

    /**
     * Normalizes a color value.
     *
     * @param string $color Color to normalize.
     * @return string Normalized color value.
     */
    public function normalizeColor(string $color): string;
}
