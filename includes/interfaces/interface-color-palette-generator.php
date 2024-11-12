<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Generator Interface
 *
 * Defines the contract for generating color palettes programmatically.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteGenerator {
    /**
     * Generates palette from base color.
     *
     * @param string $base_color Base color value.
     * @param array $options {
     *     Optional. Generation options.
     *     @type string $scheme        Color scheme.
     *     @type int    $count         Colors count.
     *     @type array  $rules         Generation rules.
     *     @type array  $constraints   Color constraints.
     * }
     * @return array {
     *     Generation results.
     *     @type array  $palette       Generated palette.
     *     @type array  $relationships Color relationships.
     *     @type array  $metrics       Generation metrics.
     *     @type array  $metadata      Generation metadata.
     * }
     */
    public function generate_from_color(string $base_color, array $options = []): array;

    /**
     * Generates palette from theme.
     *
     * @param string $theme Theme identifier.
     * @param array $options {
     *     Optional. Theme options.
     *     @type array  $mood          Theme mood.
     *     @type array  $style         Theme style.
     *     @type array  $constraints   Theme constraints.
     *     @type array  $metadata      Theme metadata.
     * }
     * @return array {
     *     Theme results.
     *     @type array  $palette       Generated palette.
     *     @type array  $theme         Theme details.
     *     @type array  $variations    Theme variations.
     *     @type array  $metadata      Generation metadata.
     * }
     */
    public function generate_from_theme(string $theme, array $options = []): array;

    /**
     * Generates random palette.
     *
     * @param array $options {
     *     Optional. Random options.
     *     @type int    $count         Colors count.
     *     @type array  $constraints   Color constraints.
     *     @type array  $rules         Generation rules.
     *     @type array  $metadata      Generation metadata.
     * }
     * @return array {
     *     Random results.
     *     @type array  $palette       Generated palette.
     *     @type array  $metrics       Generation metrics.
     *     @type array  $validation    Palette validation.
     *     @type array  $metadata      Generation metadata.
     * }
     */
    public function generate_random(array $options = []): array;

    /**
     * Generates variations of palette.
     *
     * @param array $palette Base palette.
     * @param array $options {
     *     Optional. Variation options.
     *     @type array  $types         Variation types.
     *     @type array  $constraints   Color constraints.
     *     @type array  $count         Variations count.
     *     @type array  $metadata      Generation metadata.
     * }
     * @return array {
     *     Variation results.
     *     @type array  $variations    Generated variations.
     *     @type array  $relationships Variation relationships.
     *     @type array  $metrics       Generation metrics.
     *     @type array  $metadata      Generation metadata.
     * }
     */
    public function generate_variations(array $palette, array $options = []): array;
} 
