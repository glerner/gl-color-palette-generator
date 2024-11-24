<?php
/**
 * Color Palette Analyzer
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class Color_Palette_Analyzer
 *
 * Analyzes color palettes for various properties including:
 * - Color relationships (complementary, analogous, etc.)
 * - Contrast ratios and accessibility
 * - Color temperature and brightness
 * - Harmony and balance
 *
 * @since 1.0.0
 */
class Color_Palette_Analyzer {
    /**
     * Minimum contrast ratio for WCAG AA compliance
     */
    const WCAG_AA_CONTRAST = 4.5;

    /**
     * Minimum contrast ratio for WCAG AAA compliance
     */
    const WCAG_AAA_CONTRAST = 7.0;

    /**
     * Analyze a color palette
     *
     * @param Color_Palette $palette The palette to analyze
     * @return array Analysis results
     */
    public function analyze(Color_Palette $palette): array {
        $colors = $palette->get_colors();

        return [
            'relationships' => $this->analyze_relationships($colors),
            'contrast' => $this->analyze_contrast($colors),
            'temperature' => $this->analyze_temperature($colors),
            'harmony' => $this->analyze_harmony($colors),
            'accessibility' => $this->analyze_accessibility($colors)
        ];
    }

    /**
     * Analyze color relationships
     *
     * @param array $colors Array of hex colors
     * @return array Relationship analysis
     */
    public function analyze_relationships(array $colors): array {
        $relationships = [];

        foreach ($colors as $i => $color1) {
            foreach (array_slice($colors, $i + 1) as $color2) {
                $hue1 = $this->get_hue($color1);
                $hue2 = $this->get_hue($color2);

                $difference = abs($hue1 - $hue2);

                if ($difference <= 30) {
                    $relationships[] = [
                        'type' => 'analogous',
                        'colors' => [$color1, $color2]
                    ];
                }

                if (abs($difference - 180) <= 30) {
                    $relationships[] = [
                        'type' => 'complementary',
                        'colors' => [$color1, $color2]
                    ];
                }
            }
        }

        return $relationships;
    }

    /**
     * Analyze contrast ratios between colors
     *
     * @param array $colors Array of hex colors
     * @return array Contrast analysis
     */
    public function analyze_contrast(array $colors): array {
        $contrast_pairs = [];

        foreach ($colors as $i => $color1) {
            foreach (array_slice($colors, $i + 1) as $color2) {
                $ratio = $this->get_contrast_ratio($color1, $color2);

                $contrast_pairs[] = [
                    'colors' => [$color1, $color2],
                    'ratio' => $ratio,
                    'meets_aa' => $ratio >= self::WCAG_AA_CONTRAST,
                    'meets_aaa' => $ratio >= self::WCAG_AAA_CONTRAST
                ];
            }
        }

        return $contrast_pairs;
    }

    /**
     * Analyze color temperature
     *
     * @param array $colors Array of hex colors
     * @return array Temperature analysis
     */
    public function analyze_temperature(array $colors): array {
        $temperatures = [];

        foreach ($colors as $color) {
            $rgb = $this->hex_to_rgb($color);

            / Calculate relative warmth based on red vs blue components
            $warmth = ($rgb['r'] - $rgb['b']) / 255;

            $temperatures[] = [
                'color' => $color,
                'temperature' => $warmth > 0 ? 'warm' : 'cool',
                'value' => $warmth
            ];
        }

        return $temperatures;
    }

    /**
     * Analyze color harmony
     *
     * @param array $colors Array of hex colors
     * @return array Harmony analysis
     */
    public function analyze_harmony(array $colors): array {
        $hues = array_map([$this, 'get_hue'], $colors);
        $saturations = array_map([$this, 'get_saturation'], $colors);
        $values = array_map([$this, 'get_value'], $colors);

        return [
            'hue_variance' => $this->calculate_variance($hues),
            'saturation_variance' => $this->calculate_variance($saturations),
            'value_variance' => $this->calculate_variance($values),
            'is_monochromatic' => max($hues) - min($hues) <= 30,
            'is_balanced' => $this->is_palette_balanced($hues, $saturations, $values)
        ];
    }

    /**
     * Analyze accessibility compliance
     *
     * @param array $colors Array of hex colors
     * @return array Accessibility analysis
     */
    public function analyze_accessibility(array $colors): array {
        $analysis = [
            'wcag_aa_pairs' => [],
            'wcag_aaa_pairs' => [],
            'overall_compliance' => 'AA'
        ];

        foreach ($colors as $i => $color1) {
            foreach (array_slice($colors, $i + 1) as $color2) {
                $ratio = $this->get_contrast_ratio($color1, $color2);

                if ($ratio >= self::WCAG_AAA_CONTRAST) {
                    $analysis['wcag_aaa_pairs'][] = [$color1, $color2];
                } elseif ($ratio >= self::WCAG_AA_CONTRAST) {
                    $analysis['wcag_aa_pairs'][] = [$color1, $color2];
                }
            }
        }

        if (empty($analysis['wcag_aa_pairs']) && empty($analysis['wcag_aaa_pairs'])) {
            $analysis['overall_compliance'] = 'fail';
        } elseif (!empty($analysis['wcag_aaa_pairs'])) {
            $analysis['overall_compliance'] = 'AAA';
        }

        return $analysis;
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First hex color
     * @param string $color2 Second hex color
     * @return float Contrast ratio
     */
    private function get_contrast_ratio(string $color1, string $color2): float {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Calculate relative luminance of a color
     *
     * @param string $color Hex color
     * @return float Relative luminance
     */
    private function get_relative_luminance(string $color): float {
        $rgb = $this->hex_to_rgb($color);

        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);

        return 0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b'];
    }

    /**
     * Convert hex color to RGB values
     *
     * @param string $color Hex color
     * @return array RGB values
     */
    private function hex_to_rgb(string $color): array {
        $color = ltrim($color, '#');

        return [
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        ];
    }

    /**
     * Get hue value from hex color
     *
     * @param string $color Hex color
     * @return float Hue value (0-360)
     */
    private function get_hue(string $color): float {
        $rgb = $this->hex_to_rgb($color);
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        if ($delta == 0) {
            return 0;
        }

        if ($max == $r) {
            $hue = 60 * fmod((($g - $b) / $delta), 6);
        } elseif ($max == $g) {
            $hue = 60 * ((($b - $r) / $delta) + 2);
        } else {
            $hue = 60 * ((($r - $g) / $delta) + 4);
        }

        return $hue < 0 ? $hue + 360 : $hue;
    }

    /**
     * Get saturation value from hex color
     *
     * @param string $color Hex color
     * @return float Saturation value (0-1)
     */
    private function get_saturation(string $color): float {
        $rgb = $this->hex_to_rgb($color);
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        if ($max == 0) {
            return 0;
        }

        return $delta / $max;
    }

    /**
     * Get value/brightness from hex color
     *
     * @param string $color Hex color
     * @return float Value (0-1)
     */
    private function get_value(string $color): float {
        $rgb = $this->hex_to_rgb($color);
        return max($rgb['r'], $rgb['g'], $rgb['b']) / 255;
    }

    /**
     * Calculate variance of a set of numbers
     *
     * @param array $numbers Array of numbers
     * @return float Variance
     */
    private function calculate_variance(array $numbers): float {
        $count = count($numbers);
        if ($count === 0) {
            return 0;
        }

        $mean = array_sum($numbers) / $count;
        $squares = array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $numbers);

        return array_sum($squares) / $count;
    }

    /**
     * Check if a palette is balanced
     *
     * @param array $hues Hue values
     * @param array $saturations Saturation values
     * @param array $values Value/brightness values
     * @return bool Whether the palette is balanced
     */
    private function is_palette_balanced(array $hues, array $saturations, array $values): bool {
        / Check hue distribution
        $hue_variance = $this->calculate_variance($hues);
        if ($hue_variance > 2000) { / High variance indicates poor distribution
            return false;
        }

        / Check saturation consistency
        $saturation_variance = $this->calculate_variance($saturations);
        if ($saturation_variance > 0.1) { / High variance indicates inconsistent saturation
            return false;
        }

        / Check value/brightness range
        $value_range = max($values) - min($values);
        if ($value_range < 0.2) { / Too little contrast
            return false;
        }
        if ($value_range > 0.8) { / Too much contrast
            return false;
        }

        return true;
    }
} 
