<?php
/**
 * Color Converter Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color space conversion
 */
interface Color_Converter_Interface {
    /**
     * Convert color from one format to another
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format ('hex', 'rgb', 'hsl', etc.)
     * @param string $to_format Target format ('hex', 'rgb', 'hsl', etc.)
     * @return string|WP_Error Converted color value or error
     */
    public function convert($color, $from_format, $to_format);

    /**
     * Convert color to RGB
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format
     * @return array|WP_Error RGB values [r, g, b] or error
     */
    public function to_rgb($color, $from_format);

    /**
     * Convert color to HSL
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format
     * @return array|WP_Error HSL values [h, s, l] or error
     */
    public function to_hsl($color, $from_format);

    /**
     * Convert color to HSV
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format
     * @return array|WP_Error HSV values [h, s, v] or error
     */
    public function to_hsv($color, $from_format);

    /**
     * Convert color to CMYK
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format
     * @return array|WP_Error CMYK values [c, m, y, k] or error
     */
    public function to_cmyk($color, $from_format);

    /**
     * Convert color to LAB
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format
     * @return array|WP_Error LAB values [l, a, b] or error
     */
    public function to_lab($color, $from_format);

    /**
     * Convert color to XYZ
     *
     * @param string $color Color value to convert
     * @param string $from_format Source format
     * @return array|WP_Error XYZ values [x, y, z] or error
     */
    public function to_xyz($color, $from_format);

    /**
     * Convert color to hex
     *
     * @param string|array $color Color value to convert
     * @param string $from_format Source format
     * @return string|WP_Error Hex color value or error
     */
    public function to_hex($color, $from_format);

    /**
     * Get supported color formats
     *
     * @return array List of supported formats
     */
    public function get_supported_formats();

    /**
     * Check if format is supported
     *
     * @param string $format Format to check
     * @return bool True if format is supported
     */
    public function is_format_supported($format);

    /**
     * Get format of color value
     *
     * @param string|array $color Color value to check
     * @return string|WP_Error Format name or error if format cannot be determined
     */
    public function get_format($color);
}
