<?php
/**
 * Color Wheel Class
 *
 * Implements a comprehensive color wheel system for managing color relationships
 * and generating harmonious color combinations. Provides tools for calculating
 * complementary colors, analogous colors, and other color harmony patterns.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Wheel_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Scheme_Types;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;

/**
 * Color Wheel Class
 *
 * Handles color wheel operations and harmony calculations
 *
 * @package GL_Color_Palette_Generator
 * @since 1.0.0
 */
class Color_Wheel implements Color_Wheel_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private Color_Utility $color_utility;

    /**
     * Constructor
     *
     * @param Color_Utility $color_utility Color utility instance
     */
    public function __construct(Color_Utility $color_utility) {
        $this->color_utility = $color_utility;
    }

    /**
     * Calculate color harmonies
     *
     * @param string $base_color Base color in hex format
     * @param string $harmony_type Type of harmony to calculate
     * @param array  $options Additional options for harmony calculation
     * @return array Array of harmonious colors
     * @throws \InvalidArgumentException If harmony type is invalid
     */
    public function calculate_harmonies(string $base_color, string $harmony_type, array $options = []): array {
        $hsl = $this->color_utility->hex_to_hsl($base_color);
        $harmonies = [];

        // Validate harmony type
        $valid_harmonies = array_keys(Color_Constants::COLOR_HARMONY_RULES);
        if (!in_array($harmony_type, $valid_harmonies, true)) {
            throw new \InvalidArgumentException("Invalid harmony type: {$harmony_type}");
        }

        switch ($harmony_type) {
            case 'complementary':
                $harmonies = $this->calculate_complementary($hsl, $options);
                break;
            case 'analogous':
                $harmonies = $this->calculate_analogous($hsl, $options);
                break;
            case 'triadic':
                $harmonies = $this->calculate_triadic($hsl, $options);
                break;
            case 'tetradic':
                $harmonies = $this->calculate_tetradic($hsl, $options);
                break;
            case 'split-complementary':
                $harmonies = $this->calculate_split_complementary($hsl, $options);
                break;
        }

        return $this->apply_harmony_variations($harmonies, $options);
    }

    /**
     * Calculate harmony score for a set of colors
     *
     * @param array $colors Array of colors to analyze
     * @return float Score between 0 and 1
     */
    public function calculate_harmony_score(array $colors): float {
        $score = 0.0;
        $total_colors = count($colors);

        if ($total_colors < 2) {
            return $score;
        }

        // Convert all colors to HSL for easier analysis
        $hsl_colors = array_map(
            function($color) {
                return $this->color_utility->hex_to_hsl($color);
            },
            $colors
        );

        // Analyze hue relationships
        for ($i = 0; $i < $total_colors; $i++) {
            for ($j = $i + 1; $j < $total_colors; $j++) {
                $hue_diff = abs($hsl_colors[$i]['h'] - $hsl_colors[$j]['h']);
                $hue_diff = min($hue_diff, 360 - $hue_diff);

                // Score based on common harmony angles (60°, 120°, 180°)
                $angle_scores = [
                    60 => 0.8,  // Analogous
                    120 => 0.9, // Triadic
                    180 => 1.0  // Complementary
                ];

                foreach ($angle_scores as $angle => $max_score) {
                    $diff_from_ideal = abs($hue_diff - $angle);
                    if ($diff_from_ideal <= 15) {
                        $score += $max_score * (1 - ($diff_from_ideal / 15));
                    }
                }
            }
        }

        // Normalize score
        $total_comparisons = ($total_colors * ($total_colors - 1)) / 2;
        return $score / $total_comparisons;
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float Contrast ratio between 1 and 21
     */
    public function calculate_contrast_ratio(string $color1, string $color2): float {
        return $this->color_utility->get_contrast_ratio($color1, $color2);
    }

    /**
     * Calculate color balance score
     *
     * @param array $colors Array of colors to analyze
     * @return float Score between 0 and 1
     */
    public function calculate_color_balance(array $colors): float {
        if (count($colors) < 2) {
            return 0.0;
        }

        $hsl_colors = array_map(
            function($color) {
                return $this->color_utility->hex_to_hsl($color);
            },
            $colors
        );

        // Calculate average and variance of hue distribution
        $hues = array_column($hsl_colors, 'h');
        $avg_hue = array_sum($hues) / count($hues);

        $variance = array_reduce(
            $hues,
            function($carry, $hue) use ($avg_hue) {
                $diff = min(abs($hue - $avg_hue), 360 - abs($hue - $avg_hue));
                return $carry + pow($diff, 2);
            },
            0.0
        ) / count($hues);

        // Lower variance indicates better balance
        return 1.0 - min($variance / 180.0, 1.0);
    }

    /**
     * Calculate vibrance score based on saturation and lightness
     *
     * @param array $colors Array of colors to analyze
     * @return float Score between 0 and 1
     */
    public function calculate_vibrance_score(array $colors): float {
        if (count($colors) === 0) {
            return 0.0;
        }

        $hsl_colors = array_map(
            function($color) {
                return $this->color_utility->hex_to_hsl($color);
            },
            $colors
        );

        $total_score = array_reduce(
            $hsl_colors,
            function($carry, $hsl) {
                // Saturation contributes 60% to vibrance
                $saturation_score = $hsl['s'] * 0.6;

                // Lightness contributes 40% to vibrance (optimal around 50%)
                $lightness_score = (1 - abs(0.5 - $hsl['l'])) * 0.4;

                return $carry + $saturation_score + $lightness_score;
            },
            0.0
        );

        return $total_score / count($colors);
    }

    /**
     * Calculate complementary colors
     *
     * @param array $hsl HSL values of base color
     * @param array $options Additional options
     * @return array Array of complementary colors
     */
    public function calculate_complementary(array $hsl, array $options = []): array {
        $complementary = $hsl;
        $complementary['h'] = ($hsl['h'] + 180) % 360;

        return [
            $this->color_utility->hsl_to_hex($hsl),
            $this->color_utility->hsl_to_hex($complementary)
        ];
    }

    /**
     * Calculate analogous colors
     *
     * @param array $hsl HSL values of base color
     * @param array $options Additional options
     * @return array Array of analogous colors
     */
    public function calculate_analogous(array $hsl, array $options = []): array {
        $angle = $options['angle'] ?? Color_Constants::COLOR_HARMONY_RULES['analogous']['angle'];
        $colors = [$this->color_utility->hsl_to_hex($hsl)];

        // Add colors on both sides
        for ($i = -1; $i <= 1; $i += 2) {
            $new_hsl = $hsl;
            $new_hsl['h'] = ($hsl['h'] + ($angle * $i) + 360) % 360;
            $colors[] = $this->color_utility->hsl_to_hex($new_hsl);
        }

        return $colors;
    }

    /**
     * Calculate triadic colors
     *
     * @param array $hsl HSL values of base color
     * @param array $options Additional options
     * @return array Array of triadic colors
     */
    public function calculate_triadic(array $hsl, array $options = []): array {
        $colors = [$this->color_utility->hsl_to_hex($hsl)];
        $angle = Color_Constants::COLOR_HARMONY_RULES['triadic']['angle'];

        for ($i = 1; $i <= 2; $i++) {
            $new_hsl = $hsl;
            $new_hsl['h'] = ($hsl['h'] + ($angle * $i)) % 360;
            $colors[] = $this->color_utility->hsl_to_hex($new_hsl);
        }

        return $colors;
    }

    /**
     * Calculate tetradic colors
     *
     * @param array $hsl HSL values of base color
     * @param array $options Additional options
     * @return array Array of tetradic colors
     */
    public function calculate_tetradic(array $hsl, array $options = []): array {
        $colors = [$this->color_utility->hsl_to_hex($hsl)];
        $angle = Color_Constants::COLOR_HARMONY_RULES['tetradic']['angle'];

        for ($i = 1; $i <= 3; $i++) {
            $new_hsl = $hsl;
            $new_hsl['h'] = ($hsl['h'] + ($angle * $i)) % 360;
            $colors[] = $this->color_utility->hsl_to_hex($new_hsl);
        }

        return $colors;
    }

    /**
     * Calculate split-complementary colors
     *
     * @param array $hsl HSL values of base color
     * @param array $options Additional options
     * @return array Array of split-complementary colors
     */
    public function calculate_split_complementary(array $hsl, array $options = []): array {
        $colors = [$this->color_utility->hsl_to_hex($hsl)];
        $angle = Color_Constants::COLOR_HARMONY_RULES['split-complementary']['angle'];

        // Add complementary color
        $comp_hsl = $hsl;
        $comp_hsl['h'] = ($hsl['h'] + 180) % 360;

        // Add split colors
        for ($i = -1; $i <= 1; $i += 2) {
            $new_hsl = $comp_hsl;
            $new_hsl['h'] = ($comp_hsl['h'] + ($angle * $i) + 360) % 360;
            $colors[] = $this->color_utility->hsl_to_hex($new_hsl);
        }

        return $colors;
    }

    /**
     * Apply variations to harmony colors
     *
     * @param array $colors Array of colors
     * @param array $options Variation options
     * @return array Colors with variations applied
     */
    public function apply_harmony_variations(array $colors, array $options = []): array {
        if (!isset($options['variations'])) {
            return $colors;
        }

        $result = [];
        foreach ($colors as $color) {
            $result[] = $color;
            if (isset($options['variations']['lighter'])) {
                $result[] = $this->color_utility->lighten_color($color, $options['variations']['lighter']);
            }
            if (isset($options['variations']['darker'])) {
                $result[] = $this->color_utility->darken_color($color, $options['variations']['darker']);
            }
        }

        return $result;
    }
}
