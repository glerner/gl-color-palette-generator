<?php
/**
 * Color Validator Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color validation
 */
interface Color_Validator_Interface {
    /**
     * Validate a color value
     *
     * @param string $color Color value to validate
     * @param string $format Expected format ('hex', 'rgb', 'hsl', etc.)
     * @return bool|WP_Error True if valid, error with details if invalid
     */
    public function validate_color($color, $format);

    /**
     * Validate a color palette
     *
     * @param array $colors Array of color values
     * @param array $options Validation options
     * @return bool|WP_Error True if valid, error with details if invalid
     */
    public function validate_palette($colors, $options = []);

    /**
     * Check if color is in valid hex format
     *
     * @param string $color Color value to check
     * @return bool True if valid hex color
     */
    public function is_valid_hex($color);

    /**
     * Check if color is in valid RGB format
     *
     * @param string|array $color Color value to check
     * @return bool True if valid RGB color
     */
    public function is_valid_rgb($color);

    /**
     * Check if color is in valid HSL format
     *
     * @param string|array $color Color value to check
     * @return bool True if valid HSL color
     */
    public function is_valid_hsl($color);

    /**
     * Check if color is in valid HSV format
     *
     * @param string|array $color Color value to check
     * @return bool True if valid HSV color
     */
    public function is_valid_hsv($color);

    /**
     * Check if color is in valid CMYK format
     *
     * @param string|array $color Color value to check
     * @return bool True if valid CMYK color
     */
    public function is_valid_cmyk($color);

    /**
     * Check if color is in valid LAB format
     *
     * @param string|array $color Color value to check
     * @return bool True if valid LAB color
     */
    public function is_valid_lab($color);

    /**
     * Get validation errors for a color
     *
     * @param string $color Color value to validate
     * @param string $format Expected format
     * @return array Array of validation errors
     */
    public function get_validation_errors($color, $format);

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
}
