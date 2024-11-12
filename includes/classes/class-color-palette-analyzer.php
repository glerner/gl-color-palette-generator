<?php
/**
 * Color Palette Analyzer Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteAnalyzerInterface;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteFormatter;

/**
 * Analyzes color palettes for various properties.
 */
class ColorPaletteAnalyzer implements ColorPaletteAnalyzerInterface {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private ColorPaletteFormatter $formatter;

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
     * @return array Analysis results.
     */
    public function analyzePalette(ColorPalette $palette): array {
        return [
            'contrast_ratios' => $this->calculateContrastRatios($palette),
            'harmony' => $this->analyzeHarmony($palette),
            'accessibility' => $this->analyzeAccessibility($palette),
            'relationships' => $this->getColorRelationships($palette),
            'statistics' => $this->getPaletteStats($palette)
        ];
    }

    /**
     * Calculates contrast ratios between colors.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Contrast ratios.
     */
    public function calculateContrastRatios(ColorPalette $palette): array {
        $colors = $palette->getColors();
        $ratios = [];

        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i >= $j) {
                    continue;
                }
                $ratios[] = [
                    'colors' => [$color1, $color2],
                    'ratio' => $this->getContrastRatio($color1, $color2)
                ];
            }
        }

        return $ratios;
    }

    /**
     * Analyzes color harmony.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Harmony analysis.
     */
    public function analyzeHarmony(ColorPalette $palette): array {
        $colors = $palette->getColors();
        $hsls = array_map([$this->formatter, 'hexToHsl'], $colors);

        return [
            'complementary' => $this->findComplementaryPairs($hsls),
            'analogous' => $this->findAnalogousGroups($hsls),
            'triadic' => $this->findTriadicGroups($hsls),
            'harmony_score' => $this->calculateHarmonyScore($hsls)
        ];
    }

    /**
     * Analyzes accessibility compliance.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @param string       $level   WCAG level ('A', 'AA', or 'AAA').
     * @return array Accessibility analysis.
     */
    public function analyzeAccessibility(ColorPalette $palette, string $level = 'AA'): array {
        $min_ratios = [
            'A' => [
                'large' => 3,
                'normal' => 3
            ],
            'AA' => [
                'large' => 3,
                'normal' => 4.5
            ],
            'AAA' => [
                'large' => 4.5,
                'normal' => 7
            ]
        ];

        $contrast_ratios = $this->calculateContrastRatios($palette);
        $compliant_pairs = [];
        $non_compliant_pairs = [];

        foreach ($contrast_ratios as $pair) {
            if ($pair['ratio'] >= $min_ratios[$level]['normal']) {
                $compliant_pairs[] = $pair;
            } else {
                $non_compliant_pairs[] = $pair;
            }
        }

        return [
            'level' => $level,
            'compliant_pairs' => $compliant_pairs,
            'non_compliant_pairs' => $non_compliant_pairs,
            'compliance_rate' => count($compliant_pairs) / count($contrast_ratios)
        ];
    }

    /**
     * Gets color relationships.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Color relationships.
     */
    public function getColorRelationships(ColorPalette $palette): array {
        $colors = $palette->getColors();
        $hsls = array_map([$this->formatter, 'hexToHsl'], $colors);
        $relationships = [];

        foreach ($hsls as $i => $hsl1) {
            foreach ($hsls as $j => $hsl2) {
                if ($i >= $j) {
                    continue;
                }
                $relationships[] = [
                    'colors' => [$colors[$i], $colors[$j]],
                    'hue_difference' => $this->calculateHueDifference($hsl1[0], $hsl2[0]),
                    'saturation_difference' => abs($hsl1[1] - $hsl2[1]),
                    'lightness_difference' => abs($hsl1[2] - $hsl2[2])
                ];
            }
        }

        return $relationships;
    }

    /**
     * Gets palette statistics.
     *
     * @param ColorPalette $palette Palette to analyze.
     * @return array Palette statistics.
     */
    public function getPaletteStats(ColorPalette $palette): array {
        $colors = $palette->getColors();
        $hsls = array_map([$this->formatter, 'hexToHsl'], $colors);

        $hues = array_column($hsls, 0);
        $saturations = array_column($hsls, 1);
        $lightnesses = array_column($hsls, 2);

        return [
            'color_count' => count($colors),
            'hue_range' => [
                'min' => min($hues),
                'max' => max($hues),
                'average' => array_sum($hues) / count($hues)
            ],
            'saturation_range' => [
                'min' => min($saturations),
                'max' => max($saturations),
                'average' => array_sum($saturations) / count($saturations)
            ],
            'lightness_range' => [
                'min' => min($lightnesses),
                'max' => max($lightnesses),
                'average' => array_sum($lightnesses) / count($lightnesses)
            ],
            'contrast_range' => [
                'min' => $this->getMinContrastRatio($colors),
                'max' => $this->getMaxContrastRatio($colors)
            ]
        ];
    }

    /**
     * Calculates contrast ratio between two colors.
     *
     * @param string $color1 First color.
     * @param string $color2 Second color.
     * @return float Contrast ratio.
     */
    private function getContrastRatio(string $color1, string $color2): float {
        $l1 = $this->getLuminance($color1);
        $l2 = $this->getLuminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Gets relative luminance of a color.
     *
     * @param string $color Color in hex format.
     * @return float Relative luminance.
     */
    private function getLuminance(string $color): float {
        $rgb = $this->formatter->hexToRgb($color);
        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);

        return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
    }

    /**
     * Finds complementary color pairs.
     *
     * @param array $hsls Array of HSL values.
     * @return array Complementary pairs.
     */
    private function findComplementaryPairs(array $hsls): array {
        $pairs = [];
        foreach ($hsls as $i => $hsl1) {
            foreach ($hsls as $j => $hsl2) {
                if ($i >= $j) {
                    continue;
                }
                $diff = $this->calculateHueDifference($hsl1[0], $hsl2[0]);
                if (abs($diff - 180) < 15) {
                    $pairs[] = [$i, $j];
                }
            }
        }
        return $pairs;
    }

    /**
     * Finds analogous color groups.
     *
     * @param array $hsls Array of HSL values.
     * @return array Analogous groups.
     */
    private function findAnalogousGroups(array $hsls): array {
        $groups = [];
        for ($i = 0; $i < count($hsls); $i++) {
            $group = [$i];
            for ($j = 0; $j < count($hsls); $j++) {
                if ($i === $j) {
                    continue;
                }
                $diff = $this->calculateHueDifference($hsls[$i][0], $hsls[$j][0]);
                if ($diff <= 30) {
                    $group[] = $j;
                }
            }
            if (count($group) > 1) {
                $groups[] = $group;
            }
        }
        return $groups;
    }

    /**
     * Finds triadic color groups.
     *
     * @param array $hsls Array of HSL values.
     * @return array Triadic groups.
     */
    private function findTriadicGroups(array $hsls): array {
        $groups = [];
        for ($i = 0; $i < count($hsls); $i++) {
            for ($j = $i + 1; $j < count($hsls); $j++) {
                for ($k = $j + 1; $k < count($hsls); $k++) {
                    $diff1 = $this->calculateHueDifference($hsls[$i][0], $hsls[$j][0]);
                    $diff2 = $this->calculateHueDifference($hsls[$j][0], $hsls[$k][0]);
                    $diff3 = $this->calculateHueDifference($hsls[$k][0], $hsls[$i][0]);

                    if (abs($diff1 - 120) < 15 && abs($diff2 - 120) < 15 && abs($diff3 - 120) < 15) {
                        $groups[] = [$i, $j, $k];
                    }
                }
            }
        }
        return $groups;
    }

    /**
     * Calculates harmony score.
     *
     * @param array $hsls Array of HSL values.
     * @return float Harmony score between 0 and 1.
     */
    private function calculateHarmonyScore(array $hsls): float {
        $scores = [];

        // Check for complementary pairs
        $complementary_pairs = $this->findComplementaryPairs($hsls);
        $scores[] = count($complementary_pairs) > 0 ? 1 : 0;

        // Check for analogous groups
        $analogous_groups = $this->findAnalogousGroups($hsls);
        $scores[] = count($analogous_groups) > 0 ? 1 : 0;

        // Check for triadic groups
        $triadic_groups = $this->findTriadicGroups($hsls);
        $scores[] = count($triadic_groups) > 0 ? 1 : 0;

        // Check saturation and lightness consistency
        $saturations = array_column($hsls, 1);
        $lightnesses = array_column($hsls, 2);

        $sat_range = max($saturations) - min($saturations);
        $light_range = max($lightnesses) - min($lightnesses);

        $scores[] = 1 - ($sat_range / 100);
        $scores[] = 1 - ($light_range / 100);

        return array_sum($scores) / count($scores);
    }

    /**
     * Calculates hue difference.
     *
     * @param float $h1 First hue.
     * @param float $h2 Second hue.
     * @return float Hue difference.
     */
    private function calculateHueDifference(float $h1, float $h2): float {
        $diff = abs($h1 - $h2);
        return min($diff, 360 - $diff);
    }

    /**
     * Gets minimum contrast ratio in palette.
     *
     * @param array $colors Array of colors.
     * @return float Minimum contrast ratio.
     */
    private function getMinContrastRatio(array $colors): float {
        $min_ratio = PHP_FLOAT_MAX;
        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i >= $j) {
                    continue;
                }
                $ratio = $this->getContrastRatio($color1, $color2);
                $min_ratio = min($min_ratio, $ratio);
            }
        }
        return $min_ratio;
    }

    /**
     * Gets maximum contrast ratio in palette.
     *
     * @param array $colors Array of colors.
     * @return float Maximum contrast ratio.
     */
    private function getMaxContrastRatio(array $colors): float {
        $max_ratio = 0;
        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i >= $j) {
                    continue;
                }
                $ratio = $this->getContrastRatio($color1, $color2);
                $max_ratio = max($max_ratio, $ratio);
            }
        }
        return $max_ratio;
    }
}
