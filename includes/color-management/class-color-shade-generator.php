<?php

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Shade_Generator_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;

/**
 * Color Variation Generator Class
 *
 * Generates WordPress theme color variations that meet accessibility standards.
 * Focuses on creating lighter and darker versions of theme colors that maintain
 * WCAG compliance for WordPress themes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */
class Color_Variation_Generator implements Color_Shade_Generator_Interface {
    /**
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Constructor
     *
     * @param Color_Utility $color_utility Color utility instance
     */
    public function __construct(Color_Utility $color_utility) {
        $this->color_utility = $color_utility;
    }

    /**
     * Generate standard tints and shades for WordPress theme colors
     * Creates a range of visually distinct variations: lighter, light, dark, darker
     * Base color is always stored but might be adjusted in the variations if needed
     *
     * @param string $color Base color in hex format
     * @param array  $options Optional settings for generation
     * @return array Array of generated tints and shades
     */
    public function generate_tints_and_shades(string $color, array $options = []): array {
        if (!$this->color_utility->is_valid_hex_color($color)) {
            return [];
        }

        $rgb = $this->color_utility->hex_to_rgb($color);
        $hsl = $this->color_utility->rgb_to_hsl($rgb);
        $variations = ['base' => $color]; // Always store base color

        // Determine base adjustments for light and dark variations
        $is_light = $hsl['l'] >= Color_Constants::COLOR_METRICS['lightness']['threshold'];
        $base_hsl_light = $is_light ? $hsl : $this->adjust_for_spacing($hsl, true);
        $base_hsl_dark = $is_light ? $this->adjust_for_spacing($hsl, false) : $hsl;

        // Generate variations ensuring proper spacing
        $lighter = $this->ensure_spacing_from_white($base_hsl_light);
        $light = $this->ensure_spacing_between($base_hsl_light, $lighter, false);
        $dark = $this->ensure_spacing_between($base_hsl_dark, $light, false);
        $darker = $this->ensure_spacing_from_black($base_hsl_dark);

        // Ensure proper contrast with text colors
        $lighter = $this->adjust_for_text_contrast($lighter, true);
        $light = $this->adjust_for_text_contrast($light, true);
        $dark = $this->adjust_for_text_contrast($dark, false);
        $darker = $this->adjust_for_text_contrast($darker, false);

        // Build the variations array
        $variations['lighter'] = $this->color_utility->hsl_to_hex($lighter);
        $variations['light'] = $this->color_utility->hsl_to_hex($light);
        $variations['dark'] = $this->color_utility->hsl_to_hex($dark);
        $variations['darker'] = $this->color_utility->hsl_to_hex($darker);

        return $variations;
    }

    /**
     * Ensure color has proper spacing from white
     *
     * @param array $hsl HSL color values
     * @return array Adjusted HSL values
     */
    private function ensure_spacing_from_white(array $hsl): array {
        $off_white_min = Color_Constants::COLOR_METRICS['lightness']['off_white_min'];
        $spacing = Color_Constants::COLOR_METRICS['lightness']['spacing_min'];

        // Ensure we're not too close to off-white
        if ($hsl['l'] > $off_white_min - $spacing) {
            $hsl['l'] = $off_white_min - $spacing;
        }

        return $hsl;
    }

    /**
     * Ensure color has proper spacing from black
     *
     * @param array $hsl HSL color values
     * @return array Adjusted HSL values
     */
    private function ensure_spacing_from_black(array $hsl): array {
        $near_black_max = Color_Constants::COLOR_METRICS['lightness']['near_black_max'];
        $spacing = Color_Constants::COLOR_METRICS['lightness']['spacing_min'];

        // Ensure we're not too close to near-black
        if ($hsl['l'] < $near_black_max + $spacing) {
            $hsl['l'] = $near_black_max + $spacing;
        }

        return $hsl;
    }

    /**
     * Ensure proper spacing between two colors
     *
     * @param array $hsl HSL color to adjust
     * @param array $compare_hsl HSL color to compare against
     * @param bool $make_lighter Whether to adjust lighter or darker
     * @return array Adjusted HSL values
     */
    private function ensure_spacing_between(array $hsl, array $compare_hsl, bool $make_lighter): array {
        $spacing = Color_Constants::COLOR_METRICS['lightness']['spacing_min'];
        $diff = abs($hsl['l'] - $compare_hsl['l']);

        if ($diff < $spacing) {
            $adjustment = $spacing - $diff;
            $hsl['l'] += $make_lighter ? $adjustment : -$adjustment;

            // Check bounds
            $hsl['l'] = max(
                Color_Constants::COLOR_METRICS['lightness']['min'],
                min(
                    Color_Constants::COLOR_METRICS['lightness']['max'],
                    $hsl['l']
                )
            );
        }

        return $hsl;
    }

    /**
     * Adjust base color for proper spacing
     *
     * @param array $hsl HSL color values
     * @param bool $make_lighter Whether to adjust lighter or darker
     * @return array Adjusted HSL values
     */
    private function adjust_for_spacing(array $hsl, bool $make_lighter): array {
        $spacing = Color_Constants::COLOR_METRICS['lightness']['spacing_min'] * 2; // Need room for two variations

        if ($make_lighter) {
            $max_allowed = Color_Constants::COLOR_METRICS['lightness']['off_white_min'] - $spacing;
            if ($hsl['l'] > $max_allowed) {
                $hsl['l'] = $max_allowed;
            }
        } else {
            $min_allowed = Color_Constants::COLOR_METRICS['lightness']['near_black_max'] + $spacing;
            if ($hsl['l'] < $min_allowed) {
                $hsl['l'] = $min_allowed;
            }
        }

        return $hsl;
    }

    /**
     * Adjust color until it has sufficient contrast with text colors
     * If we hit the bounds, we'll return the last valid value
     *
     * @param array $hsl HSL color values
     * @param bool $lighter Whether to adjust lighter or darker
     * @return array Adjusted HSL values
     */
    private function adjust_for_text_contrast(array $hsl, bool $lighter): array {
        $rgb = $this->color_utility->hsl_to_rgb($hsl);
        $hex = $this->color_utility->rgb_to_hex($rgb);
        $text_color = $lighter ? Color_Constants::COLOR_NEAR_BLACK : Color_Constants::COLOR_OFF_WHITE;
        $last_valid_hsl = $hsl;

        $attempts = 0;
        while ($attempts < Color_Constants::COLOR_METRICS['lightness']['max_attempts']) {
            $contrast = $this->color_utility->get_contrast_ratio($hex, $text_color);

            // Store last valid value before potentially going too far
            if ($contrast >= Color_Constants::WCAG_CONTRAST_MIN) {
                $last_valid_hsl = $hsl;
            }

            if ($contrast >= Color_Constants::WCAG_CONTRAST_TARGET) {
                break;
            }

            // Check if we're about to hit the bounds
            $new_l = $hsl['l'] + ($lighter ?
                Color_Constants::COLOR_METRICS['lightness']['step'] :
                -Color_Constants::COLOR_METRICS['lightness']['step']
            );

            if ($new_l <= Color_Constants::COLOR_METRICS['lightness']['min'] ||
                $new_l >= Color_Constants::COLOR_METRICS['lightness']['max']) {
                return $last_valid_hsl;
            }

            $hsl = $this->adjust_lightness($hsl, $lighter ?
                Color_Constants::COLOR_METRICS['lightness']['step'] :
                -Color_Constants::COLOR_METRICS['lightness']['step']
            );
            $rgb = $this->color_utility->hsl_to_rgb($hsl);
            $hex = $this->color_utility->rgb_to_hex($rgb);
            $attempts++;
        }

        return $contrast >= Color_Constants::WCAG_CONTRAST_MIN ? $hsl : $last_valid_hsl;
    }

    /**
     * Generate a contrasting text color (off-white or near-black) for backgrounds
     *
     * @param string $background_color Background color in hex format
     * @param bool   $is_dark_mode Whether in dark mode
     * @param array  $options Optional settings
     * @return string Text color in hex format (either off-white or near-black)
     */
    public function generate_contrast_color(string $background_color, bool $is_dark_mode, array $options = []): string {
        $rgb = $this->color_utility->hex_to_rgb($background_color);
        $text_color = $is_dark_mode ? Color_Constants::COLOR_OFF_WHITE : Color_Constants::COLOR_NEAR_BLACK;

        $contrast = $this->color_utility->get_contrast_ratio(
            $this->color_utility->rgb_to_hex($rgb),
            $text_color
        );

        // If contrast is insufficient, switch to opposite
        if ($contrast < Color_Constants::WCAG_CONTRAST_TARGET) {
            $text_color = $is_dark_mode ? Color_Constants::COLOR_NEAR_BLACK : Color_Constants::COLOR_OFF_WHITE;
        }

        return $text_color;
    }

    /**
     * Generate a contrasting theme color variation
     * Creates a contrasting color from the same base, useful for hover states
     * or emphasis while maintaining theme cohesion
     *
     * @param string $base_color Base color in hex format
     * @param bool   $prefer_lighter Whether to prefer lighter variations
     * @return string Contrasting color in hex format
     */
    public function generate_contrasting_variation(string $base_color, bool $prefer_lighter = true): string {
        $rgb = $this->color_utility->hex_to_rgb($base_color);
        $hsl = $this->color_utility->rgb_to_hsl($rgb);

        // Start with moderate adjustment
        $adjustment = $prefer_lighter ? Color_Constants::COLOR_METRICS['lightness']['initial_shift'] : -Color_Constants::COLOR_METRICS['lightness']['initial_shift'];
        $contrast_hsl = $this->adjust_lightness($hsl, $adjustment);

        // If contrast is insufficient, increase adjustment gradually
        $contrast_rgb = $this->color_utility->hsl_to_rgb($contrast_hsl);
        $attempts = 0;
        while ($attempts < Color_Constants::COLOR_METRICS['lightness']['max_attempts']) {
            $contrast = $this->color_utility->get_contrast_ratio(
                $this->color_utility->rgb_to_hex($rgb),
                $this->color_utility->rgb_to_hex($contrast_rgb)
            );

            if ($contrast >= Color_Constants::WCAG_CONTRAST_TARGET) {
                break;
            }

            $contrast_hsl = $this->adjust_lightness($contrast_hsl, $prefer_lighter ?
                Color_Constants::COLOR_METRICS['lightness']['step'] :
                -Color_Constants::COLOR_METRICS['lightness']['step']
            );
            $contrast_rgb = $this->color_utility->hsl_to_rgb($contrast_hsl);
            $attempts++;
        }

        return $this->color_utility->hsl_to_hex($contrast_hsl);
    }

    /**
     * Analyze color accessibility for WordPress themes
     *
     * @param string $color Color in hex format
     * @param array  $options Optional analysis settings
     * @return array Analysis results
     */
    public function analyze_color(string $color, array $options = []): array {
        $rgb = $this->color_utility->hex_to_rgb($color);
        $hsl = $this->color_utility->rgb_to_hsl($rgb);

        $contrast_light = $this->color_utility->get_contrast_ratio(
            $this->color_utility->rgb_to_hex($rgb),
            Color_Constants::COLOR_OFF_WHITE
        );

        $contrast_dark = $this->color_utility->get_contrast_ratio(
            $this->color_utility->rgb_to_hex($rgb),
            Color_Constants::COLOR_NEAR_BLACK
        );

        return [
            'contrast_ratio_light' => $contrast_light,
            'contrast_ratio_dark' => $contrast_dark,
            'is_light' => $hsl['l'] > Color_Constants::COLOR_METRICS['lightness']['threshold'],
            'is_dark' => $hsl['l'] <= Color_Constants::COLOR_METRICS['lightness']['threshold'],
            'meets_target' => $contrast_light >= Color_Constants::WCAG_CONTRAST_TARGET ||
                            $contrast_dark >= Color_Constants::WCAG_CONTRAST_TARGET,
            'meets_minimum' => $contrast_light >= Color_Constants::WCAG_CONTRAST_MIN ||
                             $contrast_dark >= Color_Constants::WCAG_CONTRAST_MIN
        ];
    }

    /**
     * Check if colors meet contrast requirements
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return bool Whether colors meet contrast requirements
     */
    public function check_contrast(string $color1, string $color2): bool {
        return $this->color_utility->get_contrast_ratio($color1, $color2) >= Color_Constants::WCAG_CONTRAST_MIN;
    }

    /**
     * Adjust lightness of a color while maintaining hue and saturation
     *
     * @param array $hsl HSL color values
     * @param float $adjustment Amount to adjust lightness
     * @return array Adjusted HSL values
     */
    private function adjust_lightness(array $hsl, float $adjustment): array {
        $hsl['l'] = max(
            Color_Constants::COLOR_METRICS['lightness']['min'],
            min(
                Color_Constants::COLOR_METRICS['lightness']['max'],
                $hsl['l'] + $adjustment
            )
        );
        return $hsl;
    }
}
