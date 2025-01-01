<?php declare(strict_types=1);
/**
 * Color Utility Interface
 *
 * Defines the contract for color utility operations.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface Color_Utility
 *
 * Defines methods for color manipulation and conversion.
 */
interface Color_Utility {
    /**
     * Convert RGB to HSL color space
     *
     * @param array $rgb RGB color values.
     * @return array HSL values.
     */
    public function rgb_to_hsl(array $rgb): array;

    /**
     * Convert HSL to RGB color space
     *
     * @param array $hsl HSL color values.
     * @return array RGB values.
     */
    public function hsl_to_rgb(array $hsl): array;

    /**
     * Convert hex color to RGB
     *
     * @param string $hex Hex color code.
     * @return array RGB values.
     */
    public function hex_to_rgb(string $hex): array;

    /**
     * Convert RGB to hex color
     *
     * @param array $rgb RGB color values.
     * @return string Hex color code.
     */
    public function rgb_to_hex(array $rgb): string;

    /**
     * Get relative luminance of a color
     * Based on WCAG 2.0 relative luminance calculation
     *
     * @param string $color Color in hex format.
     * @return float Relative luminance (0-1).
     */
    public function get_relative_luminance(string $color): float;

    /**
     * Convert multiple colors to a different color space
     *
     * @param array  $colors Array of colors to convert
     * @param string $from   Source color space (hex, rgb, hsl, lab)
     * @param string $to     Target color space (rgb, hsl, lab, cmyk)
     * @param array  $options Optional. Conversion options.
     * @return array Conversion results with metadata
     */
    public function convert_colors(array $colors, string $from, string $to, array $options = []): array;

    /**
     * Convert colors to different format
     *
     * @param array  $colors  Colors to convert
     * @param string $format  Target format (css, scss, json)
     * @param array  $options Format options
     * @return array Formatted colors with metadata
     */
    public function format_colors(array $colors, string $format, array $options = []): array;
}
