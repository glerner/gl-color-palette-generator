<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Shade Generator Interface
 *
 * Defines the contract for generating tints and shades that meet accessibility requirements.
 * Creates a range of visually distinct variations (lighter, light, dark, darker) while
 * maintaining proper spacing and contrast with text colors.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Shade_Generator_Interface {
    /**
     * Generate tints and shades that meet accessibility requirements
     * Creates a range of visually distinct variations: lighter, light, dark, darker
     * Base color is always stored but might be adjusted in the variations if needed
     *
     * @param string $color Base color in hex format
     * @param array  $options Optional settings for generation
     * @return array Array containing 'lighter', 'light', 'base', 'dark', 'darker' variations
     */
    public function generate_tints_and_shades(string $color, array $options = []): array;

    /**
     * Generate a contrasting text color (off-white or near-black) for backgrounds
     *
     * @param string $background_color Background color in hex format
     * @param bool   $is_dark_mode Whether in dark mode
     * @param array  $options Optional settings
     * @return string Text color in hex format (either off-white or near-black)
     */
    public function generate_contrast_color(string $background_color, bool $is_dark_mode, array $options = []): string;

    /**
     * Generate a contrasting theme color variation
     * Creates a contrasting color from the same base, useful for hover states
     * or emphasis while maintaining theme cohesion
     *
     * @param string $base_color Base color in hex format
     * @param bool   $prefer_lighter Whether to prefer lighter variations
     * @return string Contrasting color in hex format
     */
    public function generate_contrasting_variation(string $base_color, bool $prefer_lighter = true): string;

    /**
     * Analyze color accessibility and characteristics
     * Checks contrast ratios, lightness levels, and accessibility compliance
     *
     * @param string $color Color in hex format
     * @param array  $options Optional analysis settings
     * @return array Analysis results including contrast ratios and compliance levels
     */
    public function analyze_color(string $color, array $options = []): array;

    /**
     * Check if colors meet contrast requirements
     * Tests against both target (AAA) and minimum (AA) contrast ratios
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return bool Whether the colors meet contrast requirements
     */
    public function check_contrast(string $color1, string $color2): bool;
}
