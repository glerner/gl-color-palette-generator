<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Shade Generator Interface
 *
 * Defines the contract for generating tints and shades that meet accessibility requirements.
 * This is distinct from WordPress theme style variations - it focuses on creating
 * lighter and darker versions of a single color that meet WCAG standards.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Shade_Generator_Interface {
    /**
     * Generate accessible tints and shades
     *
     * @param string $color Base color in hex format
     * @param array  $options {
     *     Optional. Generation options.
     *     @type bool   $include_base     Include base color if accessible.
     *     @type string $contrast_level   'AA' or 'AAA'.
     *     @type bool   $small_text       Generate for small text if true.
     *     @type array  $custom_steps     Custom brightness steps.
     * }
     * @return array {
     *     Color variations.
     *     @type string $original Original color (for reference).
     *     @type array  $variations {
     *         Accessible variations.
     *         @type string $lighter Lighter tint.
     *         @type string $light   Light tint.
     *         @type string $base    Base color (if accessible).
     *         @type string $dark    Dark shade.
     *         @type string $darker  Darker shade.
     *     }
     * }
     */
    public function generate_tints_and_shades(string $color, array $options = []): array;

    /**
     * Check if a color meets contrast requirements
     *
     * @param string $color Color to check
     * @param array  $options {
     *     Optional. Check options.
     *     @type string $contrast_level 'AA' or 'AAA'.
     *     @type bool   $small_text     Check for small text if true.
     * }
     * @return bool True if color meets requirements
     */
    public function meets_contrast_requirements(string $color, array $options = []): bool;
}
