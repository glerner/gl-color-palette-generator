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
     * Generate tints and shades that meet accessibility requirements
     *
     * @param string $color Base color in hex format
     * @param array  $options Optional settings for generation
     * @return array Array of generated tints and shades
     */
    public function generate_tints_and_shades(string $color, array $options = []): array;

    /**
     * Generate a contrast color that meets WCAG requirements
     *
     * @param string $base_color Base color in hex format
     * @param bool   $is_dark_mode Whether in dark mode
     * @param array  $options Optional settings
     * @return string Contrast color in hex format
     */
    public function generate_contrast_color(string $base_color, bool $is_dark_mode, array $options = []): string;

    /**
     * Analyze color accessibility and characteristics
     *
     * @param string $color Color in hex format
     * @param array  $options Optional analysis settings
     * @return array Analysis results
     */
    public function analyze_color(string $color, array $options = []): array;

    /**
     * Check if colors meet contrast requirements
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @param bool   $is_decorative Whether the colors are for decorative purposes
     * @return array Contrast analysis results
     */
    public function check_contrast(string $color1, string $color2, bool $is_decorative = false): array;
}
