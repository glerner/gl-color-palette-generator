<?php

namespace GLColorPalette;

/**
 * Color Palette Analyzer Class
 *
 * Analyzes color palettes for various properties and relationships.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteAnalyzer {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private $formatter;

    /**
     * Constructor.
     *
     * @param ColorPaletteFormatter $formatter Color formatter instance.
     */
    public function __construct(ColorPaletteFormatter $formatter) {
        $this->formatter = $formatter;
    }

    /**
     * Analyzes a color palette.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @param array $options {
     *     Optional. Analysis options.
     *     @type bool $contrast     Analyze contrast ratios.
     *     @type bool $harmony      Analyze color harmony.
     *     @type bool $distribution Analyze color distribution.
     *     @type bool $accessibility Analyze accessibility.
     * }
     * @return array {
     *     Analysis results.
     *     @type array  $contrast     Contrast analysis.
     *     @type array  $harmony      Harmony analysis.
     *     @type array  $distribution Distribution analysis.
     *     @type array  $accessibility Accessibility analysis.
     * }
     */
    public function analyze_palette(ColorPalette $palette, array $options = []): array {
        $options = array_merge([
            'contrast' => true,
            'harmony' => true,
            'distribution' => true,
            'accessibility' => true
        ], $options);

        $results = [];

        if ($options['contrast']) {
            $results['contrast'] = $this->analyze_contrast($palette);
        }

        if ($options['harmony']) {
            $results['harmony'] = $this->analyze_harmony($palette);
        }

        if ($options['distribution']) {
            $results['distribution'] = $this->analyze_distribution($palette);
        }

        if ($options['accessibility']) {
            $results['accessibility'] = $this->analyze_accessibility($palette);
        }

        return $results;
    }

    /**
     * Analyzes contrast relationships.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Contrast analysis results.
     */
    private function analyze_contrast(ColorPalette $palette): array {
        $colors = $palette->get_colors();
        $count = count($colors);
        $ratios = [];
        $stats = [
            'min' => PHP_FLOAT_MAX,
            'max' => 0,
            'avg' => 0,
            'wcag_aa_pass' => 0,
            'wcag_aaa_pass' => 0
        ];

        // Calculate contrast ratios between all color pairs
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $ratio = $this->calculate_contrast_ratio($colors[$i], $colors[$j]);
                $ratios[] = [
                    'colors' => [$colors[$i], $colors[$j]],
                    'ratio' => $ratio,
                    'wcag_aa' => $ratio >= 4.5,
                    'wcag_aaa' => $ratio >= 7.0
                ];

                $stats['min'] = min($stats['min'], $ratio);
                $stats['max'] = max($stats['max'], $ratio);
                $stats['avg'] += $ratio;

                if ($ratio >= 4.5) $stats['wcag_aa_pass']++;
                if ($ratio >= 7.0) $stats['wcag_aaa_pass']++;
            }
        }

        $pair_count = count($ratios);
        $stats['avg'] = $pair_count > 0 ? $stats['avg'] / $pair_count : 0;

        return [
            'ratios' => $ratios,
            'statistics' => $stats
        ];
    }

    /**
     * Analyzes color harmony.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Harmony analysis results.
     */
    private function analyze_harmony(ColorPalette $palette): array {
        $colors = $palette->get_colors();
        $hsl_colors = array_map(function($color) {
            return $this->color_to_hsl($color);
        }, $colors);

        $relationships = [];
        $harmony_types = [];

        // Analyze color relationships
        foreach ($hsl_colors as $i => $hsl1) {
            foreach (array_slice($hsl_colors, $i + 1) as $j => $hsl2) {
                $relationship = $this->analyze_color_relationship($hsl1, $hsl2);
                if ($relationship) {
                    $relationships[] = [
                        'colors' => [$colors[$i], $colors[$j + $i + 1]],
                        'type' => $relationship
                    ];
                    $harmony_types[$relationship] = ($harmony_types[$relationship] ?? 0) + 1;
                }
            }
        }

        // Analyze overall palette harmony
        $harmony_score = $this->calculate_harmony_score($hsl_colors);

        return [
            'relationships' => $relationships,
            'harmony_types' => $harmony_types,
            'harmony_score' => $harmony_score
        ];
    }

    /**
     * Analyzes color distribution.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Distribution analysis results.
     */
    private function analyze_distribution(ColorPalette $palette): array {
        $colors = $palette->get_colors();
        $hsl_colors = array_map(function($color) {
            return $this->color_to_hsl($color);
        }, $colors);

        // Analyze hue distribution
        $hue_distribution = $this->analyze_hue_distribution($hsl_colors);

        // Analyze saturation and lightness ranges
        $sat_range = $this->analyze_value_range(array_column($hsl_colors, 1));
        $light_range = $this->analyze_value_range(array_column($hsl_colors, 2));

        return [
            'hue_distribution' => $hue_distribution,
            'saturation_range' => $sat_range,
            'lightness_range' => $light_range
        ];
    }

    /**
     * Analyzes accessibility considerations.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Accessibility analysis results.
     */
    private function analyze_accessibility(ColorPalette $palette): array {
        $colors = $palette->get_colors();
        $issues = [];
        $recommendations = [];

        // Check for color blindness considerations
        $cvd_analysis = $this->analyze_color_blindness($colors);
        if (!empty($cvd_analysis['issues'])) {
            $issues = array_merge($issues, $cvd_analysis['issues']);
            $recommendations = array_merge($recommendations, $cvd_analysis['recommendations']);
        }

        // Check for sufficient contrast
        $contrast_analysis = $this->analyze_contrast($palette);
        if ($contrast_analysis['statistics']['wcag_aa_pass'] === 0) {
            $issues[] = 'No color combinations meet WCAG AA contrast requirements';
            $recommendations[] = 'Consider adding colors with higher contrast ratios';
        }

        return [
            'wcag_compliance' => [
                'aa_pass_rate' => $contrast_analysis['statistics']['wcag_aa_pass'],
                'aaa_pass_rate' => $contrast_analysis['statistics']['wcag_aaa_pass']
            ],
            'color_blindness' => $cvd_analysis,
            'issues' => $issues,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Calculates contrast ratio between two colors.
     *
     * @param string $color1 First color.
     * @param string $color2 Second color.
     * @return float Contrast ratio.
     */
    private function calculate_contrast_ratio(string $color1, string $color2): float {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Gets relative luminance of a color.
     *
     * @param string $color Color value.
     * @return float Relative luminance.
     */
    private function get_relative_luminance(string $color): float {
        $rgb = $this->formatter->format_color($color, 'rgb');
        preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $rgb, $matches);

        $r = $this->to_luminance_component((int)$matches[1]);
        $g = $this->to_luminance_component((int)$matches[2]);
        $b = $this->to_luminance_component((int)$matches[3]);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Converts RGB component to luminance component.
     *
     * @param int $value RGB component value.
     * @return float Luminance component value.
     */
    private function to_luminance_component(int $value): float {
        $value = $value / 255;
        return $value <= 0.03928
            ? $value / 12.92
            : pow(($value + 0.055) / 1.055, 2.4);
    }

    /**
     * Converts color to HSL array.
     *
     * @param string $color Color value.
     * @return array HSL values [h, s, l].
     */
    private function color_to_hsl(string $color): array {
        $hsl = $this->formatter->format_color($color, 'hsl');
        preg_match('/hsl\((\d+),\s*(\d+)%?,\s*(\d+)%?\)/', $hsl, $matches);
        return [
            (int)$matches[1], // hue
            (int)$matches[2], // saturation
            (int)$matches[3]  // lightness
        ];
    }

    /**
     * Analyzes relationship between two colors in HSL space.
     *
     * @param array $hsl1 First HSL color.
     * @param array $hsl2 Second HSL color.
     * @return string|null Relationship type or null.
     */
    private function analyze_color_relationship(array $hsl1, array $hsl2): ?string {
        $hue_diff = abs($hsl1[0] - $hsl2[0]);

        // Analyze hue relationships
        if ($hue_diff < 10) {
            return 'monochromatic';
        } elseif (abs($hue_diff - 180) < 10) {
            return 'complementary';
        } elseif (abs($hue_diff - 120) < 10) {
            return 'triadic';
        } elseif (abs($hue_diff - 90) < 10 || abs($hue_diff - 270) < 10) {
            return 'square';
        } elseif (abs($hue_diff - 60) < 10 || abs($hue_diff - 300) < 10) {
            return 'analogous';
        }

        return null;
    }

    /**
     * Calculates overall harmony score.
     *
     * @param array $hsl_colors Array of HSL colors.
     * @return float Harmony score (0-1).
     */
    private function calculate_harmony_score(array $hsl_colors): float {
        $score = 0;
        $count = count($hsl_colors);

        if ($count < 2) {
            return 1.0;
        }

        // Analyze hue spacing
        $hues = array_column($hsl_colors, 0);
        sort($hues);
        $hue_spacing_score = $this->calculate_hue_spacing_score($hues);

        // Analyze saturation and lightness consistency
        $sat_score = $this->calculate_value_consistency(array_column($hsl_colors, 1));
        $light_score = $this->calculate_value_consistency(array_column($hsl_colors, 2));

        // Weight the components
        $score = (
            $hue_spacing_score * 0.5 +
            $sat_score * 0.25 +
            $light_score * 0.25
        );

        return max(0, min(1, $score));
    }

    /**
     * Analyzes hue distribution.
     *
     * @param array $hsl_colors Array of HSL colors.
     * @return array Distribution analysis.
     */
    private function analyze_hue_distribution(array $hsl_colors): array {
        $hues = array_column($hsl_colors, 0);
        $distribution = array_fill(0, 12, 0); // 12 hue sectors

        foreach ($hues as $hue) {
            $sector = floor($hue / 30) % 12;
            $distribution[$sector]++;
        }

        return [
            'sectors' => $distribution,
            'coverage' => count(array_filter($distribution)) / 12,
            'balance' => $this->calculate_distribution_balance($distribution)
        ];
    }

    /**
     * Analyzes value range (saturation/lightness).
     *
     * @param array $values Array of values.
     * @return array Range analysis.
     */
    private function analyze_value_range(array $values): array {
        return [
            'min' => min($values),
            'max' => max($values),
            'range' => max($values) - min($values),
            'average' => array_sum($values) / count($values)
        ];
    }

    /**
     * Analyzes color blindness considerations.
     *
     * @param array $colors Array of colors.
     * @return array Analysis results.
     */
    private function analyze_color_blindness(array $colors): array {
        $issues = [];
        $recommendations = [];

        // Simulate different types of color blindness
        $deuteranopia = $this->simulate_color_blindness($colors, 'deuteranopia');
        $protanopia = $this->simulate_color_blindness($colors, 'protanopia');
        $tritanopia = $this->simulate_color_blindness($colors, 'tritanopia');

        // Analyze similarities in simulated colors
        foreach (['deuteranopia', 'protanopia', 'tritanopia'] as $type) {
            $simulated = ${$type};
            if ($this->has_similar_colors($simulated)) {
                $issues[] = "Colors may be difficult to distinguish for people with {$type}";
                $recommendations[] = "Consider adjusting colors for better {$type} distinction";
            }
        }

        return [
            'issues' => $issues,
            'recommendations' => $recommendations,
            'simulations' => [
                'deuteranopia' => $deuteranopia,
                'protanopia' => $protanopia,
                'tritanopia' => $tritanopia
            ]
        ];
    }
} 
