<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Validator Interface
 *
 * Defines the contract for validating colors and color combinations.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorValidator {
    /**
     * Validates a single color value.
     *
     * @param string $color Color to validate (hex, rgb, hsl, etc.).
     * @param string $format Expected color format (default 'hex').
     * @return array {
     *     Validation results.
     *     @type bool   $is_valid    Whether the color is valid.
     *     @type string $format      Detected color format.
     *     @type array  $normalized  Normalized color values.
     *     @type array  $errors      Validation errors if any.
     * }
     */
    public function validate_color(string $color, string $format = 'hex'): array;

    /**
     * Validates color combinations for harmony and contrast.
     *
     * @param array $colors Array of colors to validate together.
     * @param array $rules {
     *     Optional. Validation rules.
     *     @type float  $min_contrast      Minimum contrast ratio.
     *     @type string $harmony_type      Expected harmony type.
     *     @type bool   $check_accessibility Check WCAG compliance.
     *     @type array  $custom_rules      Additional custom rules.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $passes_rules    Overall validation status.
     *     @type array  $harmony_scores  Harmony analysis scores.
     *     @type array  $contrast_ratios Contrast ratios between colors.
     *     @type array  $violations      Rule violations if any.
     * }
     */
    public function validate_combination(array $colors, array $rules = []): array;

    /**
     * Gets detailed color information.
     *
     * @param string $color Color value to analyze.
     * @return array {
     *     Color information.
     *     @type array  $formats     Color in various formats (hex, rgb, hsl).
     *     @type array  $properties  Color properties (brightness, saturation, etc.).
     *     @type string $name        Closest named color match.
     *     @type array  $metadata    Additional color metadata.
     * }
     */
    public function get_color_info(string $color): array;

    /**
     * Validates color against brand guidelines.
     *
     * @param string $color Color to validate.
     * @param array $guidelines {
     *     Brand guidelines.
     *     @type array  $allowed_colors   List of allowed colors.
     *     @type array  $allowed_ranges   Acceptable HSL ranges.
     *     @type float  $tolerance       Color matching tolerance.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $compliant     Whether color complies with guidelines.
     *     @type array  $matches       Matching guideline colors if any.
     *     @type array  $suggestions   Alternative compliant colors.
     *     @type array  $violations    Guideline violations if any.
     * }
     */
    public function validate_against_guidelines(string $color, array $guidelines): array;
} 
