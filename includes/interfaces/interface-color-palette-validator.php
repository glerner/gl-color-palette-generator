<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Validator Interface
 *
 * Defines the contract for validating color palettes and their properties.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteValidator {
    /**
     * Validates color values.
     *
     * @param array $colors Colors to validate.
     * @param array $rules {
     *     Optional. Validation rules.
     *     @type array  $formats       Allowed formats.
     *     @type array  $ranges        Value ranges.
     *     @type array  $constraints   Value constraints.
     *     @type array  $custom        Custom rules.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $valid         Overall validity.
     *     @type array  $errors        Validation errors.
     *     @type array  $warnings      Validation warnings.
     *     @type array  $metadata      Validation metadata.
     * }
     */
    public function validate_colors(array $colors, array $rules = []): array;

    /**
     * Validates palette structure.
     *
     * @param array $palette Palette to validate.
     * @param array $schema {
     *     Optional. Schema rules.
     *     @type array  $required      Required fields.
     *     @type array  $optional      Optional fields.
     *     @type array  $types         Field types.
     *     @type array  $constraints   Field constraints.
     * }
     * @return array {
     *     Schema validation results.
     *     @type bool   $valid         Schema validity.
     *     @type array  $errors        Schema errors.
     *     @type array  $warnings      Schema warnings.
     *     @type array  $metadata      Validation metadata.
     * }
     */
    public function validate_structure(array $palette, array $schema = []): array;

    /**
     * Validates palette relationships.
     *
     * @param array $palette Palette to validate.
     * @param array $rules {
     *     Optional. Relationship rules.
     *     @type array  $harmony       Color harmony.
     *     @type array  $contrast      Contrast ratios.
     *     @type array  $distribution  Color distribution.
     *     @type array  $custom        Custom rules.
     * }
     * @return array {
     *     Relationship results.
     *     @type bool   $valid         Relationship validity.
     *     @type array  $errors        Relationship errors.
     *     @type array  $warnings      Relationship warnings.
     *     @type array  $metadata      Validation metadata.
     * }
     */
    public function validate_relationships(array $palette, array $rules = []): array;

    /**
     * Validates palette accessibility.
     *
     * @param array $palette Palette to validate.
     * @param array $standards {
     *     Optional. Accessibility standards.
     *     @type array  $wcag          WCAG requirements.
     *     @type array  $contrast      Contrast requirements.
     *     @type array  $readability   Readability rules.
     *     @type array  $custom        Custom standards.
     * }
     * @return array {
     *     Accessibility results.
     *     @type bool   $compliant     Standards compliance.
     *     @type array  $violations    Standard violations.
     *     @type array  $suggestions   Improvement suggestions.
     *     @type array  $metadata      Validation metadata.
     * }
     */
    public function validate_accessibility(array $palette, array $standards = []): array;
}
