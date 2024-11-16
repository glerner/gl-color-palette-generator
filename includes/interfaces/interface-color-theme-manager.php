<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Theme Manager Interface
 *
 * Defines the contract for managing and applying color themes across platforms.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorThemeManager {
    /**
     * Creates a new theme from a color scheme.
     *
     * @param array $scheme Color scheme data.
     * @param array $options {
     *     Optional. Theme creation options.
     *     @type string $name           Theme name.
     *     @type string $platform       Target platform ('web', 'mobile', 'desktop').
     *     @type array  $variables      Custom variable naming.
     *     @type bool   $dark_variant   Generate dark variant.
     *     @type array  $breakpoints    Responsive breakpoint settings.
     * }
     * @return array {
     *     Theme data.
     *     @type string $id            Unique theme identifier.
     *     @type array  $light         Light theme variables.
     *     @type array  $dark          Dark theme variables.
     *     @type array  $breakpoints   Responsive settings.
     *     @type array  $metadata      Theme metadata.
     * }
     * @throws \InvalidArgumentException If scheme is invalid.
     */
    public function create_theme(array $scheme, array $options = []): array;

    /**
     * Applies theme to specified platform format.
     *
     * @param array $theme Theme data.
     * @param string $platform Target platform format.
     * @param array $options {
     *     Optional. Export options.
     *     @type string $format        Output format ('css', 'json', 'scss').
     *     @type bool   $minify        Minify output.
     *     @type array  $prefix        Variable prefix settings.
     *     @type bool   $include_dark  Include dark theme variant.
     * }
     * @return array {
     *     Applied theme data.
     *     @type string $content    Formatted theme content.
     *     @type string $filename   Suggested filename.
     *     @type array  $variables  Variable mappings.
     *     @type array  $metadata   Application metadata.
     * }
     */
    public function apply_theme(array $theme, string $platform, array $options = []): array;

    /**
     * Validates theme structure and compatibility.
     *
     * @param array $theme Theme to validate.
     * @param array $platforms Target platforms to validate against.
     * @return array {
     *     Validation results.
     *     @type bool   $is_valid     Overall validation status.
     *     @type array  $compatibility Platform compatibility results.
     *     @type array  $issues        Validation issues found.
     *     @type array  $suggestions   Improvement suggestions.
     * }
     */
    public function validate_theme(array $theme, array $platforms = []): array;

    /**
     * Generates theme variations.
     *
     * @param array $theme Base theme.
     * @param array $options {
     *     Optional. Variation options.
     *     @type array  $contrast_levels  Contrast variations.
     *     @type bool   $seasonal         Generate seasonal variants.
     *     @type array  $color_filters    Color filtering options.
     *     @type bool   $accessibility    Maintain accessibility.
     * }
     * @return array {
     *     Theme variations.
     *     @type array $variants     Generated theme variants.
     *     @type array $relationships Variant relationships.
     *     @type array $metadata     Generation metadata.
     * }
     */
    public function generate_variations(array $theme, array $options = []): array;
} 
