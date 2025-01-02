<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_Error;

/**
 * Color Palette Validator Interface
 *
 * Interface for validating color palettes and their properties.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */
interface Color_Palette_Validator_Interface {
    /**
     * Validate a color palette
     *
     * @param Color_Palette $palette Palette to validate
     * @param array        $options Validation options
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_palette(Color_Palette $palette, array $options = []): bool|WP_Error;

    /**
     * Validate color format
     *
     * @param string $color Color to validate
     * @param string $format Expected format (hex, rgb, rgba, hsl, hsla)
     * @return bool True if valid
     */
    public function validate_color_format(string $color, string $format = 'hex'): bool;

    /**
     * Validate color accessibility
     *
     * @param array  $colors Colors to validate
     * @param string $level  WCAG level ('A', 'AA', or 'AAA')
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_accessibility(array $colors, string $level = 'AA'): bool|WP_Error;

    /**
     * Validate color harmony
     *
     * @param array  $colors Colors to validate
     * @param string $scheme_type Type of color scheme to validate against
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_harmony(array $colors, string $scheme_type = Color_Constants::SCHEME_MONOCHROMATIC): bool|WP_Error;

    /**
     * Get last validation errors
     *
     * @return array List of validation errors with context
     */
    public function get_last_errors(): array;
}
