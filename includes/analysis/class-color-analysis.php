<?php
/**
 * Color Analysis Class
 *
 * Handles color analysis, including accessibility, harmony, and psychological aspects.
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

class Color_Analysis {
    /**
     * Analyze color harmony and relationships
     *
     * @param array $colors Base colors
     * @return array Harmony analysis
     */
    public function analyze_harmony(array $colors): array {
        $hex_colors = array_column($colors, 'hex');

        $harmony = [
            'scheme_type' => $this->detect_color_scheme($hex_colors),
            'balance' => $this->analyze_color_balance($colors),
            'temperature' => $this->analyze_temperature($colors),
            'contrast_levels' => $this->analyze_contrast_levels($colors),
            'recommendations' => []
        ];

        // Analyze potential harmony issues
        $issues = [];

        // Check contrast between adjacent colors
        for ($i = 0; $i < count($hex_colors) - 1; $i++) {
            $contrast = $this->calculate_contrast_ratio($hex_colors[$i], $hex_colors[$i + 1]);
            if ($contrast < 1.5) {
                $issues[] = [
                    'type' => 'low_contrast',
                    'colors' => [$hex_colors[$i], $hex_colors[$i + 1]],
                    'value' => $contrast
                ];
            }
        }

        // Check balance distribution
        if ($harmony['balance']['lightness']['std_dev'] > 30) {
            $issues[] = [
                'type' => 'unbalanced_lightness',
                'value' => $harmony['balance']['lightness']['std_dev']
            ];
        }

        if ($harmony['balance']['saturation']['std_dev'] > 30) {
            $issues[] = [
                'type' => 'unbalanced_saturation',
                'value' => $harmony['balance']['saturation']['std_dev']
            ];
        }

        // Generate recommendations based on issues
        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'low_contrast':
                    $harmony['recommendations'][] = sprintf(
                        __('Consider increasing contrast between colors %s and %s', 'gl-color-palette-generator'),
                        $issue['colors'][0],
                        $issue['colors'][1]
                    );
                    break;
                case 'unbalanced_lightness':
                    $harmony['recommendations'][] = __('Consider balancing the lightness values across the palette', 'gl-color-palette-generator');
                    break;
                case 'unbalanced_saturation':
                    $harmony['recommendations'][] = __('Consider evening out the saturation levels', 'gl-color-palette-generator');
                    break;
            }
        }

        return $harmony;
    }

    /**
     * Analyze contrast levels between colors
     *
     * @param array $colors Color data
     * @return array Contrast analysis
     */
    private function analyze_contrast_levels(array $colors): array {
        $hex_colors = array_column($colors, 'hex');
        $contrasts = [];

        for ($i = 0; $i < count($hex_colors); $i++) {
            for ($j = $i + 1; $j < count($hex_colors); $j++) {
                $contrast = $this->calculate_contrast_ratio($hex_colors[$i], $hex_colors[$j]);
                $contrasts[] = [
                    'colors' => [$hex_colors[$i], $hex_colors[$j]],
                    'ratio' => $contrast,
                    'meets_aa' => $contrast >= 4.5,
                    'meets_aaa' => $contrast >= 7
                ];
            }
        }

        return [
            'pairs' => $contrasts,
            'average' => array_sum(array_column($contrasts, 'ratio')) / count($contrasts),
            'min' => min(array_column($contrasts, 'ratio')),
            'max' => max(array_column($contrasts, 'ratio'))
        ];
    }

    /**
     * Detect color scheme type
     *
     * @param array $hex_colors Array of hex colors
     * @return string Color scheme type
     */
    private function detect_color_scheme(array $hex_colors): string {
        $hues = array_map(function($hex) {
            return $this->hex_to_hsl($hex)['h'];
        }, $hex_colors);

        sort($hues);
        $hue_differences = [];

        // Calculate differences between adjacent hues
        for ($i = 0; $i < count($hues) - 1; $i++) {
            $diff = $hues[$i + 1] - $hues[$i];
            if ($diff < 0) {
                $diff += 360;
            }
            $hue_differences[] = $diff;
        }

        // Analyze hue relationships
        if (max($hue_differences) <= 30) {
            return 'monochromatic';
        } elseif (count(array_unique($hue_differences)) === 1) {
            if ($hue_differences[0] === 180) {
                return 'complementary';
            } elseif ($hue_differences[0] === 120) {
                return 'triadic';
            }
        } elseif (count($hue_differences) === 3) {
            // Check for split-complementary
            $total_diff = array_sum($hue_differences);
            if (abs($total_diff - 360) < 30) {
                return 'split-complementary';
            }
        }

        return 'custom';
    }

    /**
     * Analyze color balance
     *
     * @param array $colors Color data
     * @return array Balance analysis
     */
    private function analyze_color_balance(array $colors): array {
        $lightness_values = [];
        $saturation_values = [];

        foreach ($colors as $color) {
            $hsl = $this->hex_to_hsl($color['hex']);
            $lightness_values[] = $hsl['l'];
            $saturation_values[] = $hsl['s'];
        }

        return [
            'lightness' => [
                'average' => array_sum($lightness_values) / count($colors),
                'std_dev' => $this->calculate_std_dev($lightness_values),
                'distribution' => $lightness_values
            ],
            'saturation' => [
                'average' => array_sum($saturation_values) / count($colors),
                'std_dev' => $this->calculate_std_dev($saturation_values),
                'distribution' => $saturation_values
            ]
        ];
    }

    /**
     * Calculate standard deviation
     *
     * @param array $values Array of numbers
     * @return float Standard deviation
     */
    private function calculate_std_dev(array $values): float {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $squares = array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values);

        return sqrt(array_sum($squares) / ($count - 1));
    }

    /**
     * Analyze temperature characteristics of colors
     *
     * @param array $colors Color data
     * @return array Temperature analysis
     */
    private function analyze_temperature(array $colors): array {
        $temperatures = [];
        $warm_hues = [0, 60];  // Red to Yellow
        $cool_hues = [180, 240];  // Cyan to Blue

        foreach ($colors as $color) {
            $hsl = $this->hex_to_hsl($color['hex']);
            $hue = $hsl['h'];

            // Determine temperature based on hue
            if ($hue >= $warm_hues[0] && $hue <= $warm_hues[1]) {
                $temperatures[] = 'warm';
            } elseif ($hue >= $cool_hues[0] && $hue <= $cool_hues[1]) {
                $temperatures[] = 'cool';
            } else {
                $temperatures[] = 'neutral';
            }
        }

        $temp_counts = array_count_values($temperatures);
        $dominant = array_search(max($temp_counts), $temp_counts);

        return [
            'dominant' => $dominant,
            'distribution' => $temp_counts,
            'balance' => count(array_unique($temperatures)) > 1 ? 'mixed' : 'uniform'
        ];
    }
}
