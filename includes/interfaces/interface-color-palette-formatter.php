<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette Formatter Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

/**
 * Interface Color_Palette_Formatter_Interface
 * Defines the contract for formatting color palettes
 */
interface Color_Palette_Formatter_Interface {
    /**
     * Format palette for output
     *
     * @param array  $palette Array of colors.
     * @param string $format  Output format (css, scss, less, tailwind, json).
     * @param array  $options Optional formatting options.
     * @return string Formatted palette.
     * @throws \Exception If format is invalid.
     */
    public function format_palette(array $palette, string $format, array $options = []): string;

    /**
     * Get supported formats
     *
     * @return array List of supported formats
     */
    public function get_supported_formats(): array;

    /**
     * Get default formatting options
     *
     * @return array Default options
     */
    public function get_default_options(): array;
}
