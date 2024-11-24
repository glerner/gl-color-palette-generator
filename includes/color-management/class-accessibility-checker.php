<?php
/**
 * Accessibility Checker Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Accessibility_Checker
 */
class Accessibility_Checker implements \GL_Color_Palette_Generator\Interfaces\Accessibility_Checker {
    /**
     * WCAG 2.1 contrast ratios
     */
    const WCAG_AAA_LARGE = 4.5;
    const WCAG_AAA_SMALL = 7.0;
    const WCAG_AA_LARGE = 3.0;
    const WCAG_AA_SMALL = 4.5;

    /**
     * Check color combination accessibility
     *
     * @param string $foreground Foreground color hex.
     * @param string $background Background color hex.
     * @return array Accessibility results.
     */
    public function check_combination($foreground, $background) {
        $contrast_ratio = $this->calculate_contrast_ratio($foreground, $background);

        return [
            'contrast_ratio' => round($contrast_ratio, 2),
            'aa_large' => $contrast_ratio >= self::WCAG_AA_LARGE,
            'aa_small' => $contrast_ratio >= self::WCAG_AA_SMALL,
            'aaa_large' => $contrast_ratio >= self::WCAG_AAA_LARGE,
            'aaa_small' => $contrast_ratio >= self::WCAG_AAA_SMALL,
            'readable' => $contrast_ratio >= self::WCAG_AA_LARGE,
            'recommendations' => $this->get_recommendations($contrast_ratio)
        ];
    }

    /**
     * Check entire palette for accessibility
     *
     * @param array $colors Array of hex colors.
     * @return array Accessibility results for all combinations.
     */
    public function check_palette($colors) {
        $results = [];

        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i >= $j) continue;

                $results[] = [
                    'colors' => [$color1, $color2],
                    'results' => $this->check_combination($color1, $color2)
                ];
            }
        }

        return [
            'combinations' => $results,
            'overall_score' => $this->calculate_overall_score($results),
            'recommendations' => $this->get_palette_recommendations($results)
        ];
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
     * Convert hex to RGB
     *
     * @param string $hex_color Hex color code.
     * @return array RGB values.
     */
    private function hex_to_rgb($hex_color) {
        $hex = ltrim($hex_color, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Get recommendations based on contrast ratio
     *
     * @param float $ratio Contrast ratio.
     * @return array Recommendations.
     */
    private function get_recommendations($ratio) {
        $recommendations = [];

        if ($ratio < self::WCAG_AA_LARGE) {
            $recommendations[] = __('Increase contrast for better readability', 'gl-color-palette-generator');
        }

        if ($ratio < self::WCAG_AA_SMALL) {
            $recommendations[] = __('Not suitable for body text', 'gl-color-palette-generator');
        }

        if ($ratio < self::WCAG_AAA_LARGE) {
            $recommendations[] = __('Consider using larger text sizes', 'gl-color-palette-generator');
        }

        return $recommendations;
    }

    /**
     * Calculate overall accessibility score for palette
     *
     * @param array $results Accessibility results.
     * @return int Score from 0-100.
     */
    private function calculate_overall_score($results) {
        if (empty($results)) return 0;

        $total_score = 0;
        foreach ($results as $result) {
            $ratio = $result['results']['contrast_ratio'];
            $score = 0;

            if ($ratio >= self::WCAG_AAA_SMALL) $score = 100;
            elseif ($ratio >= self::WCAG_AAA_LARGE) $score = 90;
            elseif ($ratio >= self::WCAG_AA_SMALL) $score = 80;
            elseif ($ratio >= self::WCAG_AA_LARGE) $score = 70;
            else $score = min(60, max(0, round($ratio * 10)));

            $total_score += $score;
        }

        return round($total_score / count($results));
    }

    /**
     * Get recommendations for entire palette
     *
     * @param array $results Accessibility results.
     * @return array Recommendations.
     */
    private function get_palette_recommendations($results) {
        $recommendations = [];
        $failing_pairs = [];

        foreach ($results as $result) {
            if ($result['results']['contrast_ratio'] < self::WCAG_AA_LARGE) {
                $failing_pairs[] = sprintf(
                    __('Colors %s and %s have insufficient contrast', 'gl-color-palette-generator'),
                    $result['colors'][0],
                    $result['colors'][1]
                );
            }
        }

        if (!empty($failing_pairs)) {
            $recommendations[] = __('Consider adjusting these color pairs:', 'gl-color-palette-generator');
            $recommendations = array_merge($recommendations, $failing_pairs);
        }

        return $recommendations;
    }
} 
