<?php

namespace GLColorPalette\Interfaces;

/**
 * Palette Generator Interface
 *
 * Defines the contract for generating and managing color palettes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface PaletteGenerator {
    /**
     * Generates a new color palette based on specified criteria.
     *
     * @param array $criteria {
     *     Optional. Criteria for palette generation.
     *     @type string $base_color    Base color to build palette around.
     *     @type string $harmony_type  Type of color harmony (see Color_Constants::COLOR_HARMONY_TYPES).
     *     @type int    $color_count   Number of colors in palette (default 5).
     *     @type array  $constraints   Color constraints (brightness, saturation ranges).
     *     @type bool   $accessibility Whether to enforce WCAG compliance.
     * }
     * @return array {
     *     Generated palette data.
     *     @type array  $colors        Array of hex color codes.
     *     @type array  $color_names   Human-readable color names.
     *     @type string $harmony_type  Harmony type used.
     *     @type array  $relationships Color relationships explanation.
     *     @type array  $metadata      Additional palette metadata.
     * }
     * @throws \InvalidArgumentException If criteria are invalid.
     */
    public function generate_palette(array $criteria = []): array;

    /**
     * Validates a color palette against specified rules.
     *
     * @param array $palette Array of hex color codes.
     * @param array $rules {
     *     Optional. Validation rules.
     *     @type bool   $check_contrast    Check color contrast.
     *     @type bool   $check_harmony     Verify color harmony.
     *     @type string $accessibility     WCAG compliance level (see Color_Constants::WCAG_LEVELS).
     *     @type array  $required_colors   Colors that must be included.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $is_valid      Overall validation status.
     *     @type array  $issues        Array of identified issues.
     *     @type array  $suggestions   Improvement suggestions.
     *     @type float  $harmony_score Color harmony score (0-1).
     * }
     */
    public function validate_palette(array $palette, array $rules = []): array;

    /**
     * Generates variations of an existing palette.
     *
     * @param array $palette Original palette hex colors.
     * @param array $options {
     *     Optional. Variation options.
     *     @type string $variation_type  Type of variation (see Color_Constants::VARIATION_TYPES).
     *     @type int    $variation_count Number of variations to generate.
     *     @type float  $intensity       Intensity of variation (0-1).
     *     @type bool   $preserve_harmony Maintain original color harmony.
     * }
     * @return array {
     *     Array of palette variations.
     *     @type array  $variations     Array of variation palettes.
     *     @type array  $relationships  Relationship to original palette.
     *     @type array  $scores         Harmony scores for variations.
     * }
     */
    public function generate_variations(array $palette, array $options = []): array;

    /**
     * Gets palette statistics and analysis.
     *
     * @param array $palette Palette hex colors.
     * @return array {
     *     Palette statistics.
     *     @type array  $color_distribution  Distribution of hues/saturations.
     *     @type array  $harmony_analysis    Analysis of color relationships.
     *     @type array  $contrast_metrics    Contrast ratios between colors.
     *     @type array  $accessibility_stats WCAG compliance statistics.
     *     @type array  $psychological_impact Predicted psychological effects.
     * }
     */
    public function get_palette_statistics(array $palette): array;
} 
