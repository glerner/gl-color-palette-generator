<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Scheme Generator Interface
 *
 * Defines the contract for generating complete color schemes with semantic roles.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorSchemeGenerator {
    /**
     * Generates a complete color scheme based on criteria.
     *
     * @param array $criteria {
     *     Optional. Generation criteria.
     *     @type string $base_color     Primary/brand color to build around.
     *     @type string $style          Design style ('modern', 'classic', etc.).
     *     @type string $purpose        Intended use ('web', 'print', 'brand').
     *     @type array  $constraints    Color constraints and requirements.
     *     @type bool   $accessibility  Enforce WCAG compliance.
     * }
     * @return array {
     *     Generated color scheme.
     *     @type array $primary    Primary color variations.
     *     @type array $secondary  Secondary color variations.
     *     @type array $accent     Accent colors.
     *     @type array $neutral    Neutral/gray scale colors.
     *     @type array $semantic {
     *         Semantic color assignments.
     *         @type string $success  Success state color.
     *         @type string $warning  Warning state color.
     *         @type string $error    Error state color.
     *         @type string $info     Information state color.
     *     }
     *     @type array $metadata   Generation metadata and rationale.
     * }
     * @throws \InvalidArgumentException If criteria are invalid.
     */
    public function generate_scheme(array $criteria = []): array;

    /**
     * Gets color variations for a specific role.
     *
     * @param string $base_color Base color to generate variations from.
     * @param string $role Color role ('primary', 'secondary', 'accent').
     * @param array $options {
     *     Optional. Variation options.
     *     @type int   $steps          Number of variations to generate.
     *     @type array $value_range    Brightness range for variations.
     *     @type bool  $include_base   Include base color in variations.
     * }
     * @return array {
     *     Color variations.
     *     @type array  $colors     Generated color variations.
     *     @type array  $metadata   Variation generation details.
     * }
     */
    public function get_color_variations(string $base_color, string $role, array $options = []): array;

    /**
     * Generates semantic color assignments.
     *
     * @param array $base_scheme Base color scheme to derive from.
     * @param array $options {
     *     Optional. Semantic options.
     *     @type bool  $use_defaults    Use standard semantic colors.
     *     @type array $custom_mapping  Custom semantic color mappings.
     *     @type bool  $ensure_contrast Ensure contrast with backgrounds.
     * }
     * @return array {
     *     Semantic color assignments.
     *     @type array $colors      Assigned semantic colors.
     *     @type array $rationale   Assignment reasoning.
     *     @type array $contrast    Contrast analysis results.
     * }
     */
    public function generate_semantic_colors(array $base_scheme, array $options = []): array;

    /**
     * Validates and analyzes a color scheme.
     *
     * @param array $scheme Color scheme to analyze.
     * @return array {
     *     Analysis results.
     *     @type bool   $is_valid       Whether scheme is valid.
     *     @type array  $accessibility  Accessibility analysis.
     *     @type array  $harmony        Harmony analysis.
     *     @type array  $coverage       Color space coverage analysis.
     *     @type array  $improvements   Suggested improvements.
     * }
     */
    public function analyze_scheme(array $scheme): array;
} 
