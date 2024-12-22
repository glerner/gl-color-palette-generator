<?php

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Shade_Generator_Interface;
use GL_Color_Palette_Generator\Traits\Color_Shade_Generator_Trait;
use GL_Color_Palette_Generator\Color_Constants;
use GL_Color_Palette_Generator\Color_Utility;

/**
 * Color Variation Generator Class
 *
 * Generates tints and shades from base colors while ensuring WCAG compliance.
 * This is distinct from WordPress theme style variations - it focuses on creating
 * lighter and darker versions of a single color that meet accessibility standards.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */
class Color_Variation_Generator implements Color_Shade_Generator_Interface, Color_Constants {
    use Color_Shade_Generator_Trait;

    /**
     * @var AccessibilityChecker
     */
    private $accessibility_checker;

    /**
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Constructor
     *
     * @param AccessibilityChecker $accessibility_checker Accessibility checker instance
     * @param Color_Utility $color_utility Color utility instance
     */
    public function __construct(AccessibilityChecker $accessibility_checker, Color_Utility $color_utility) {
        $this->accessibility_checker = $accessibility_checker;
        $this->color_utility = $color_utility;
    }

    /**
     * Generate tints and shades that meet accessibility requirements
     *
     * @param string $color Base color in hex format
     * @param array  $options Optional settings for generation
     * @return array Array of generated tints and shades
     */
    public function generate_tints_and_shades(string $color, array $options = []): array {
        // Set default options
        $options = array_merge([
            'include_base' => true,
            'contrast_level' => 'AA',
            'small_text' => true,
            'is_dark_mode' => false,
            'custom_steps' => []
        ], $options);

        // Convert hex to RGB
        $rgb = $this->color_utility->hex_to_rgb($color);
        list($r, $g, $b) = $rgb;

        // Calculate current luminance
        $luminance = $this->calculate_relative_luminance($r, $g, $b);

        // Determine target luminance based on mode
        $target_min = $options['is_dark_mode'] ? self::DARK_MODE_MIN_LUMINANCE : self::LIGHT_MODE_MIN_LUMINANCE;
        $target_max = $options['is_dark_mode'] ? self::DARK_MODE_MAX_LUMINANCE : self::LIGHT_MODE_MAX_LUMINANCE;

        // Generate variations
        $variations = [];

        // Adjust base color to meet luminance requirements if needed
        $base_color = $this->adjust_color_to_target_luminance($rgb, $target_min, $target_max);

        // Generate contrast colors
        $contrast_color = $this->generate_contrast_color($base_color, $options['is_dark_mode']);

        $variations['base'] = $this->color_utility->rgb_to_hex($base_color);
        $variations['contrast'] = $this->color_utility->rgb_to_hex($contrast_color);

        // Generate additional shades based on the base color
        $shades = $this->generate_accessible_shades($base_color, $options);
        $variations = array_merge($variations, $shades);

        return [
            'original' => $color,
            'variations' => $variations
        ];
    }

    /**
     * Generate a contrast color that meets WCAG requirements while avoiding harsh contrast
     *
     * @param array $base_color RGB color array
     * @param bool $is_dark_mode Whether in dark mode
     * @return array RGB color array
     */
    private function generate_contrast_color(array $base_color, bool $is_dark_mode): array {
        $target_min = $is_dark_mode ? self::LIGHT_MODE_MIN_LUMINANCE : self::DARK_MODE_MIN_LUMINANCE;
        $target_max = $is_dark_mode ? self::LIGHT_MODE_MAX_LUMINANCE : self::DARK_MODE_MAX_LUMINANCE;

        // Start with optimal contrast target
        $color = $this->adjust_color_to_target_luminance($base_color, $target_min, $target_max);
        $contrast = $this->calculate_contrast_ratio($this->color_utility->rgb_to_hex($base_color), $this->color_utility->rgb_to_hex($color));

        // If contrast is too harsh, gradually reduce it while maintaining AAA standard
        if ($contrast > self::CONTRAST_THRESHOLD_MAX) {
            $color = $this->reduce_contrast_to_target($base_color, $color, self::CONTRAST_THRESHOLD_TARGET);
        }
        // If contrast is too low, increase it to at least meet AA standard
        elseif ($contrast < self::CONTRAST_THRESHOLD_MIN) {
            $color = $this->increase_contrast_to_minimum($base_color, $color, self::CONTRAST_THRESHOLD_MIN);
        }

        return $color;
    }

    /**
     * Reduce contrast while maintaining minimum threshold
     *
     * @param array $base_color Base RGB color
     * @param array $contrast_color Contrast RGB color
     * @param float $target_contrast Target contrast ratio
     * @return array Adjusted RGB color
     */
    private function reduce_contrast_to_target(array $base_color, array $contrast_color, float $target_contrast): array {
        $base_luminance = $this->calculate_relative_luminance(...$base_color);
        $contrast_luminance = $this->calculate_relative_luminance(...$contrast_color);

        // Determine if we need to increase or decrease luminance
        $should_increase = $contrast_luminance < $base_luminance;

        while ($this->calculate_contrast_ratio(
            $this->color_utility->rgb_to_hex($base_color),
            $this->color_utility->rgb_to_hex($contrast_color)
        ) > $target_contrast) {
            if ($should_increase) {
                $contrast_color = $this->adjust_brightness($contrast_color, 5);
            } else {
                $contrast_color = $this->adjust_brightness($contrast_color, -5);
            }
        }

        return $contrast_color;
    }

    /**
     * Increase contrast to meet minimum threshold
     *
     * @param array $base_color Base RGB color
     * @param array $contrast_color Contrast RGB color
     * @param float $min_contrast Minimum contrast ratio
     * @return array Adjusted RGB color
     */
    private function increase_contrast_to_minimum(array $base_color, array $contrast_color, float $min_contrast): array {
        $base_luminance = $this->calculate_relative_luminance(...$base_color);
        $contrast_luminance = $this->calculate_relative_luminance(...$contrast_color);

        // Determine if we need to increase or decrease luminance
        $should_increase = $contrast_luminance > $base_luminance;

        while ($this->calculate_contrast_ratio(
            $this->color_utility->rgb_to_hex($base_color),
            $this->color_utility->rgb_to_hex($contrast_color)
        ) < $min_contrast) {
            if ($should_increase) {
                $contrast_color = $this->adjust_brightness($contrast_color, 5);
            } else {
                $contrast_color = $this->adjust_brightness($contrast_color, -5);
            }
        }

        return $contrast_color;
    }

    /**
     * Adjust color to meet target luminance range
     *
     * @param array $color RGB color array
     * @param float $min_luminance Minimum target luminance
     * @param float $max_luminance Maximum target luminance
     * @return array Adjusted RGB color array
     */
    private function adjust_color_to_target_luminance(array $color, float $min_luminance, float $max_luminance): array {
        list($r, $g, $b) = $color;
        $current_luminance = $this->calculate_relative_luminance($r, $g, $b);

        if ($current_luminance < $min_luminance) {
            return $this->increase_luminance($color, $min_luminance);
        } elseif ($current_luminance > $max_luminance) {
            return $this->decrease_luminance($color, $max_luminance);
        }

        return $color;
    }

    /**
     * Generate accessible shade variations
     *
     * @param array $base_color RGB color array
     * @param array $options Generation options
     * @return array Array of shade variations
     */
    private function generate_accessible_shades(array $base_color, array $options): array {
        $shades = [];
        $steps = [-20, -10, 10, 20];

        foreach ($steps as $step) {
            $shade = $this->adjust_brightness($base_color, $step);
            if ($this->meets_contrast_requirements($this->color_utility->rgb_to_hex($shade), $options)) {
                $key = $step < 0 ? 'darker' . abs($step) : 'lighter' . $step;
                $shades[$key] = $this->color_utility->rgb_to_hex($shade);
            }
        }

        return $shades;
    }

    /**
     * Check if a color meets contrast requirements
     *
     * @param string $color Color to check
     * @param bool $is_dark_mode Whether in dark mode
     * @return bool Whether color meets requirements
     */
    private function meets_contrast_requirements(string $color, bool $is_dark_mode): bool {
        // Check contrast against base background
        $base_contrast = $this->accessibility_checker->calculate_contrast_ratio(
            $color,
            $is_dark_mode ? self::COLOR_NEAR_BLACK : self::COLOR_OFF_WHITE
        );

        if ($base_contrast < self::CONTRAST_THRESHOLD_MIN) {
            return false;
        }

        // Check contrast against text colors
        $text_contrast = $this->accessibility_checker->calculate_contrast_ratio(
            $color,
            $is_dark_mode ? self::COLOR_OFF_WHITE : self::COLOR_NEAR_BLACK
        );

        return $text_contrast >= self::CONTRAST_THRESHOLD_TARGET;
    }

    /**
     * Adjust brightness of an RGB color
     *
     * @param array $rgb RGB color values
     * @param int   $steps Steps to adjust (-100 to 100)
     * @return string Hex color code
     */
    private function adjust_brightness(array $rgb, int $steps): string {
        $steps = max(-100, min(100, $steps));

        if ($steps > 0) {
            $rgb = array_map(function($value) use ($steps) {
                return $value + ((255 - $value) * ($steps / 100));
            }, $rgb);
        } else {
            $rgb = array_map(function($value) use ($steps) {
                return $value * (1 + ($steps / 100));
            }, $rgb);
        }

        $rgb = array_map('round', $rgb);
        return $this->color_utility->rgb_to_hex($rgb);
    }

    /**
     * Calculate relative luminance of an RGB color
     *
     * @param int $r Red component
     * @param int $g Green component
     * @param int $b Blue component
     * @return float Relative luminance
     */
    private function calculate_relative_luminance(int $r, int $g, int $b): float {
        $r_linear = $this->linearize_component($r);
        $g_linear = $this->linearize_component($g);
        $b_linear = $this->linearize_component($b);

        return 0.2126 * $r_linear + 0.7152 * $g_linear + 0.0722 * $b_linear;
    }

    /**
     * Linearize a color component
     *
     * @param int $component Color component (0-255)
     * @return float Linearized component
     */
    private function linearize_component(int $component): float {
        $component /= 255;

        if ($component <= 0.03928) {
            return $component / 12.92;
        }

        return pow(($component + 0.055) / 1.055, 2.4);
    }

    /**
     * Increase luminance of an RGB color
     *
     * @param array $color RGB color array
     * @param float $target_luminance Target luminance
     * @return array Adjusted RGB color array
     */
    private function increase_luminance(array $color, float $target_luminance): array {
        list($r, $g, $b) = $color;
        $current_luminance = $this->calculate_relative_luminance($r, $g, $b);

        $ratio = $target_luminance / $current_luminance;

        $r = round($r * $ratio);
        $g = round($g * $ratio);
        $b = round($b * $ratio);

        return [$r, $g, $b];
    }

    /**
     * Decrease luminance of an RGB color
     *
     * @param array $color RGB color array
     * @param float $target_luminance Target luminance
     * @return array Adjusted RGB color array
     */
    private function decrease_luminance(array $color, float $target_luminance): array {
        list($r, $g, $b) = $color;
        $current_luminance = $this->calculate_relative_luminance($r, $g, $b);

        $ratio = $target_luminance / $current_luminance;

        $r = round($r * $ratio);
        $g = round($g * $ratio);
        $b = round($b * $ratio);

        return [$r, $g, $b];
    }

    /**
     * Generate theme base colors
     *
     * @param string $primary_color Primary color in hex
     * @param bool $is_dark_mode Whether to generate dark mode colors
     * @return array Generated base colors
     */
    public function generate_theme_base_colors(string $primary_color, bool $is_dark_mode = false): array {
        $rgb = $this->color_utility->hex_to_rgb($primary_color);
        $hsl = $this->color_utility->rgb_to_hsl($rgb);

        // Generate base color (background)
        if ($is_dark_mode) {
            $base_hsl = [
                'h' => $hsl['h'],
                's' => min(15, $hsl['s']),
                'l' => 15
            ];
        } else {
            $base_hsl = [
                'h' => $hsl['h'],
                's' => min(10, $hsl['s']),
                'l' => 98
            ];
        }

        $base_rgb = $this->color_utility->hsl_to_rgb($base_hsl);
        $base_color = $this->color_utility->rgb_to_hex($base_rgb);

        // Generate contrast color (text)
        $contrast_rgb = $this->generate_contrast_color($base_rgb, !$is_dark_mode);
        $contrast_color = $this->color_utility->rgb_to_hex($contrast_rgb);

        return [
            'base' => $base_color,
            'contrast' => $contrast_color
        ];
    }

    /**
     * Get text color based on background luminance
     *
     * @param float $bg_luminance Background luminance
     * @param bool $is_dark_mode Whether in dark mode
     * @return string Text color hex
     */
    private function get_text_color(float $bg_luminance, bool $is_dark_mode = false): string {
        $threshold = $is_dark_mode 
            ? Color_Constants::COLOR_METRICS['luminance']['dark_mode_threshold']
            : Color_Constants::COLOR_METRICS['luminance']['threshold'];
            
        return $bg_luminance > $threshold
            ? Color_Constants::COLOR_METRICS['colors']['dark'] 
            : Color_Constants::COLOR_METRICS['colors']['light'];
    }

    /**
     * Ensure sufficient contrast for text color
     *
     * @param string $background_color Background color in hex
     * @param bool $is_dark_mode Whether in dark mode
     * @return string Text color in hex
     */
    private function ensure_text_contrast(string $background_color, bool $is_dark_mode): string {
        $bg_rgb = $this->color_utility->hex_to_rgb($background_color);
        $bg_luminance = $this->calculate_relative_luminance(...$bg_rgb);

        // Start with appropriate neutral text color based on background luminance
        $text_color = $this->get_text_color($bg_luminance, $is_dark_mode);

        // Calculate contrast
        $contrast = $this->accessibility_checker->calculate_contrast_ratio(
            $text_color,
            $background_color
        );

        // If contrast is insufficient, use high contrast colors
        if ($contrast < Color_Constants::ACCESSIBILITY_CONFIG['contrast']['min_ratio']) {
            return $bg_luminance > Color_Constants::COLOR_METRICS['luminance']['threshold']
                ? Color_Constants::COLOR_METRICS['colors']['dark']
                : Color_Constants::COLOR_METRICS['colors']['light'];
        }

        return $text_color;
    }

    /**
     * Generate system colors that harmonize with the theme
     *
     * @param array $base_colors Array of base theme colors
     * @param bool $is_dark_mode Whether generating for dark mode
     * @return array Generated system colors
     */
    private function generate_system_colors(array $base_colors, bool $is_dark_mode): array {
        $system_colors = [];

        // Get primary color's HSL for reference
        $primary_hsl = $this->color_utility->rgb_to_hsl($this->color_utility->hex_to_rgb($base_colors['primary']));
        $primary_saturation = $primary_hsl[1];

        foreach (self::SYSTEM_COLOR_RULES as $type => $rules) {
            // Generate default variant
            $default_hsl = [
                $rules['hue'],
                $primary_saturation * $rules['variants']['default']['saturation'],
                $is_dark_mode
                    ? $rules['variants']['default']['lightness_dark']
                    : $rules['variants']['default']['lightness_light']
            ];

            $default_rgb = $this->color_utility->hsl_to_rgb($default_hsl);
            $system_colors["system-{$type}"] = $this->color_utility->rgb_to_hex($default_rgb);

            // Generate light variant
            $light_hsl = [
                $rules['hue'],
                $primary_saturation * $rules['variants']['light']['saturation'],
                $is_dark_mode
                    ? $rules['variants']['light']['lightness_dark']
                    : $rules['variants']['light']['lightness_light']
            ];

            $light_rgb = $this->color_utility->hsl_to_rgb($light_hsl);
            $system_colors["system-{$type}-light"] = $this->color_utility->rgb_to_hex($light_rgb);

            // Generate text colors ensuring contrast
            $text_color = $this->ensure_text_contrast(
                $system_colors["system-{$type}"],
                $is_dark_mode
            );
            $system_colors["system-{$type}-text"] = $text_color;

            $light_text_color = $this->ensure_text_contrast(
                $system_colors["system-{$type}-light"],
                $is_dark_mode
            );
            $system_colors["system-{$type}-light-text"] = $light_text_color;
        }

        return $system_colors;
    }
}
