<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Palette Generator Interface
 *
 * Defines the contract for generating and managing color palettes.
 *
 * @package GL_Color_Palette_Generator
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
     *     @type string $scheme_type   Type of color scheme (see Color_Constants::COLOR_SCHEMES).
     *     @type int    $color_count   Number of colors in palette (default 5).
     *     @type array  $constraints   Color constraints (brightness, saturation ranges).
     *     @type array  $business_context {
     *         Optional. Business and brand context for AI generation.
     *         @type string $description    Business description.
     *         @type string $industry       Business industry.
     *         @type string $target_audience Target audience description.
     *         @type string $mood           Desired mood/emotional impact.
     *     }
     *     @type array  $image_data {
     *         Optional. Image-based generation data.
     *         @type string $image_path     Path to uploaded image.
     *         @type string $context_type   How to use image: 'extract' or 'inspire'.
     *     }
     * }
     * @return array {
     *     Generated palette data.
     *     @type array  $colors {
     *         Array of colors with roles as keys.
     *         @type array $primary {
     *             @type string $hex     Hex color code.
     *             @type string $name    Human-readable color name.
     *             @type string $emotion Description of emotional impact.
     *         }
     *         @type array $secondary Similar structure to primary.
     *         @type array $accent    Similar structure to primary.
     *         @type array $contrast  Similar structure to primary.
     *     }
     *     @type string $scheme_type   Scheme type used.
     *     @type array  $inspiration {
     *         Optional. Source of inspiration.
     *         @type string $type      'image' or 'business'.
     *         @type string $source    Description of inspiration source.
     *     }
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
     *     @type array  $business_context  Business context for contextual validation.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $valid            Overall validation result.
     *     @type array  $contrast_scores   WCAG contrast scores.
     *     @type array  $harmony_scores    Color harmony scores.
     *     @type array  $context_scores    How well colors fit business context.
     *     @type array  $messages         Validation messages and suggestions.
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
