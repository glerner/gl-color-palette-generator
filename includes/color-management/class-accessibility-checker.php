<?php
/**
 * Accessibility Checker Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Interfaces\Accessibility_Checker as Accessibility_Checker_Interface;

/**
 * Class Accessibility_Checker
 */
class Accessibility_Checker implements Accessibility_Checker_Interface, Color_Constants {

    /**
     * Check color combination accessibility
     *
     * @param string $foreground Foreground color hex.
     * @param string $background Background color hex.
     * @return array Accessibility results.
     */
    public function check_combination(string $foreground, string $background): array {
        $contrast_ratio = $this->calculate_contrast_ratio($foreground, $background);

        return [
            'contrast_ratio' => round($contrast_ratio, 2),
            'aa_large' => $contrast_ratio >= self::WCAG_CONTRAST_AA_LARGE,
            'aa_small' => $contrast_ratio >= self::WCAG_CONTRAST_AA,
            'aaa_large' => $contrast_ratio >= self::WCAG_CONTRAST_AA,
            'aaa_small' => $contrast_ratio >= self::WCAG_CONTRAST_AAA,
            'readable' => $contrast_ratio >= self::WCAG_CONTRAST_AA_LARGE,
            'recommendations' => $this->get_recommendations($contrast_ratio)
        ];
    }

    /**
     * Check entire palette for accessibility
     *
     * @param array $colors Array of hex colors.
     * @return array Accessibility results for all combinations.
     */
    public function check_palette(array $palette): array {
        $results = [];

        foreach ($palette as $i => $color1) {
            foreach ($palette as $j => $color2) {
                if ($i >= $j) continue;

                $results[] = [
                    'colors' => [$color1, $color2],
                    'results' => $this->check_combination($color1, $color2)
                ];
            }
        }

        return [
            'combinations' => $results,
            'overall_score' => $this->calculate_score($palette),
            'recommendations' => $this->get_palette_recommendations($results)
        ];
    }

    /**
     * Calculate accessibility score for a color palette
     *
     * @param array $palette Array of hex color values
     * @return float Score between 0 and 1
     */
    public function calculate_score(array $palette): float {
        $combinations = [];
        foreach ($palette as $i => $color1) {
            foreach ($palette as $j => $color2) {
                if ($i >= $j) continue;
                $combinations[] = $this->check_combination($color1, $color2);
            }
        }

        if (empty($combinations)) {
            return 1.0;
        }

        $total_score = array_reduce($combinations, function($score, $combo) {
            $ratio = $combo['contrast_ratio'];
            $weight = 1.0;
            
            // Weight based on WCAG criteria
            if ($ratio >= self::WCAG_CONTRAST_AAA) $weight = 1.0;     // Perfect - AAA
            elseif ($ratio >= self::WCAG_CONTRAST_AA) $weight = 0.8;  // Good - AA
            elseif ($ratio >= self::WCAG_CONTRAST_AA_LARGE) $weight = 0.5; // Fair - AA Large
            else $weight = 0.2;                                        // Poor
            
            return $score + $weight;
        }, 0.0);

        return $total_score / count($combinations);
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color hex.
     * @param string $color2 Second color hex.
     * @return float Contrast ratio.
     */
    private function calculate_contrast_ratio($color1, $color2) {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get relative luminance of a color
     *
     * @param string $hex_color Hex color code.
     * @return float Relative luminance.
     */
    private function get_relative_luminance($hex_color) {
        $rgb = $this->hex_to_rgb($hex_color);

        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);

        return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
    }

    /**
     * Convert hex color to RGB array
     *
     * @param string $hex_color Hex color code.
     * @return array RGB values.
     */
    private function hex_to_rgb($hex_color) {
        $hex = ltrim($hex_color, '#');
        return array_map('hexdec', [
            substr($hex, 0, 2),
            substr($hex, 2, 2),
            substr($hex, 4, 2)
        ]);
    }

    /**
     * Get recommendations for improving contrast
     *
     * @param float $ratio Current contrast ratio.
     * @return array Recommendations.
     */
    private function get_recommendations($ratio) {
        $recommendations = [];

        if ($ratio < self::WCAG_CONTRAST_AA_LARGE) {
            $recommendations[] = 'Colors do not meet minimum contrast requirements. Consider using darker/lighter variants.';
        } elseif ($ratio < self::WCAG_CONTRAST_AA) {
            $recommendations[] = 'Colors meet only large text requirements. Consider increasing contrast for better readability.';
        } elseif ($ratio < self::WCAG_CONTRAST_AAA) {
            $recommendations[] = 'Colors meet AA requirements but could be improved for AAA compliance.';
        } elseif ($ratio > self::CONTRAST_MAX) {
            $recommendations[] = 'Contrast may be too high for visual comfort. Consider reducing for better readability.';
        }

        return $recommendations;
    }

    /**
     * Get recommendations for improving palette accessibility
     *
     * @param array $results Palette check results.
     * @return array Recommendations.
     */
    private function get_palette_recommendations($results) {
        $recommendations = [];
        $low_contrast_pairs = [];

        foreach ($results as $result) {
            if (!$result['results']['aa_small']) {
                $low_contrast_pairs[] = sprintf(
                    '%s and %s (ratio: %.2f, target: %.1f)',
                    $result['colors'][0],
                    $result['colors'][1],
                    $result['results']['contrast_ratio'],
                    self::WCAG_CONTRAST_AA
                );
            }
        }

        if (!empty($low_contrast_pairs)) {
            $recommendations[] = sprintf(
                'The following color pairs need higher contrast: %s',
                implode(', ', $low_contrast_pairs)
            );
        }

        return $recommendations;
    }
}
