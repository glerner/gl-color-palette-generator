<?php
/**
 * Accessibility Checker Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Accessibility_Checker
 */
class Accessibility_Checker implements \GL_Color_Palette_Generator\Interfaces\Accessibility_Checker {

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
            'aa_large' => $this->meets_aa_large($foreground, $background),
            'aa_small' => $this->meets_aa_small($foreground, $background),
            'aaa_large' => $this->meets_aaa_large($foreground, $background),
            'aaa_small' => $this->meets_aaa_small($foreground, $background),
            'readable' => $this->meets_aa_large($foreground, $background),
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
     * Check if a color combination meets WCAG AAA requirements for large text
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return bool True if meets requirements, false otherwise
     */
    public function meets_aaa_large($color1, $color2) {
        $ratio = $this->calculate_contrast_ratio($color1, $color2);
        return $ratio >= Color_Constants::WCAG_CONTRAST_AA;
    }

    /**
     * Check if a color combination meets WCAG AAA requirements for small text
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return bool True if meets requirements, false otherwise
     */
    public function meets_aaa_small($color1, $color2) {
        $ratio = $this->calculate_contrast_ratio($color1, $color2);
        return $ratio >= Color_Constants::WCAG_CONTRAST_AAA;
    }

    /**
     * Check if a color combination meets WCAG AA requirements for large text
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return bool True if meets requirements, false otherwise
     */
    public function meets_aa_large($color1, $color2) {
        $ratio = $this->calculate_contrast_ratio($color1, $color2);
        return $ratio >= Color_Constants::WCAG_CONTRAST_AA_LARGE;
    }

    /**
     * Check if a color combination meets WCAG AA requirements for small text
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return bool True if meets requirements, false otherwise
     */
    public function meets_aa_small($color1, $color2) {
        $ratio = $this->calculate_contrast_ratio($color1, $color2);
        return $ratio >= Color_Constants::WCAG_CONTRAST_AA;
    }

    /**
     * Get recommendations based on contrast ratio
     *
     * @param float $ratio Contrast ratio.
     * @return array Recommendations.
     */
    private function get_recommendations($ratio) {
        $recommendations = [];

        if ($ratio < Color_Constants::WCAG_CONTRAST_AA) {
            $recommendations[] = __('Increase contrast for better readability', 'gl-color-palette-generator');
        }

        if ($ratio < Color_Constants::WCAG_CONTRAST_AA) {
            $recommendations[] = __('Not suitable for body text', 'gl-color-palette-generator');
        }

        if ($ratio < Color_Constants::WCAG_CONTRAST_AAA) {
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

            if ($ratio >= Color_Constants::WCAG_CONTRAST_AAA) $score = 100;
            elseif ($ratio >= Color_Constants::WCAG_CONTRAST_AA) $score = 90;
            elseif ($ratio >= Color_Constants::WCAG_CONTRAST_AA) $score = 80;
            elseif ($ratio >= Color_Constants::WCAG_CONTRAST_AA_LARGE) $score = 70;
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
            if ($result['results']['contrast_ratio'] < Color_Constants::WCAG_CONTRAST_AA) {
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

    /**
     * Check if a color meets optimal contrast requirements
     *
     * @param string $color1 First color hex.
     * @param string $color2 Second color hex.
     * @param bool   $is_decorative Whether the color is for decorative purposes.
     * @return array Analysis results.
     */
    public function analyze_contrast($color1, $color2, $is_decorative = false) {
        $contrast_ratio = $this->calculate_contrast_ratio($color1, $color2);
        $min_required = $is_decorative ? Color_Constants::DECORATIVE_CONTRAST_MIN : Color_Constants::WCAG_CONTRAST_AA;

        return [
            'contrast_ratio' => round($contrast_ratio, 2),
            'meets_minimum' => $contrast_ratio >= $min_required,
            'meets_optimal' => $contrast_ratio >= Color_Constants::WCAG_CONTRAST_TARGET,
            'is_too_harsh' => $contrast_ratio > Color_Constants::CONTRAST_MAX,
            'needs_adjustment' => $contrast_ratio < $min_required || $contrast_ratio > Color_Constants::CONTRAST_MAX,
            'target_contrast' => $this->calculate_target_contrast($contrast_ratio, $is_decorative)
        ];
    }

    /**
     * Calculate ideal target contrast based on current ratio
     *
     * @param float $current_ratio Current contrast ratio.
     * @param bool  $is_decorative Whether the color is decorative.
     * @return float Target contrast ratio.
     */
    private function calculate_target_contrast($current_ratio, $is_decorative) {
        if ($is_decorative) {
            return max(Color_Constants::DECORATIVE_CONTRAST_MIN, min($current_ratio, Color_Constants::WCAG_CONTRAST_AA_LARGE));
        }

        if ($current_ratio < Color_Constants::WCAG_CONTRAST_AA) {
            return Color_Constants::WCAG_CONTRAST_AA;
        } elseif ($current_ratio > Color_Constants::CONTRAST_MAX) {
            return Color_Constants::CONTRAST_MAX;
        } elseif ($current_ratio < Color_Constants::WCAG_CONTRAST_TARGET) {
            return Color_Constants::WCAG_CONTRAST_TARGET;
        }

        return $current_ratio;
    }

    /**
     * Analyze color luminance characteristics
     *
     * @param string $color Hex color code.
     * @return array Color analysis results.
     */
    public function analyze_color($color) {
        $luminance = $this->get_relative_luminance($color);
        $rgb = $this->hex_to_rgb($color);

        return [
            'luminance' => round($luminance, 3),
            'is_light' => $luminance > 0.5,
            'is_very_light' => $luminance >= Color_Constants::LIGHT_LUMINANCE_THRESHOLD,
            'is_very_dark' => $luminance <= Color_Constants::DARK_LUMINANCE_THRESHOLD,
            'rgb' => $rgb,
            'perceived_brightness' => $this->calculate_perceived_brightness($rgb)
        ];
    }

    /**
     * Calculate perceived brightness using HSP color model
     *
     * @param array $rgb RGB color values.
     * @return float Perceived brightness value (0-1).
     */
    private function calculate_perceived_brightness($rgb) {
        // HSP (Highly Sensitive Perceived brightness) color model coefficients
        $sqrt_r = 0.299;
        $sqrt_g = 0.587;
        $sqrt_b = 0.114;

        return sqrt(
            $sqrt_r * ($rgb[0] / 255) ** 2 +
            $sqrt_g * ($rgb[1] / 255) ** 2 +
            $sqrt_b * ($rgb[2] / 255) ** 2
        );
    }

    /**
     * Get recommendations for improving contrast
     *
     * @param float $contrast_ratio Current contrast ratio.
     * @return array Recommendations for improvement.
     */
    private function get_recommendations_for_improvement($contrast_ratio) {
        $recommendations = [];

        if ($contrast_ratio < Color_Constants::WCAG_CONTRAST_AA) {
            $recommendations[] = [
                'priority' => 'high',
                'message' => 'Increase contrast to meet WCAG AA minimum (4.5:1)',
                'target' => Color_Constants::WCAG_CONTRAST_AA
            ];
        } elseif ($contrast_ratio < Color_Constants::WCAG_CONTRAST_TARGET) {
            $recommendations[] = [
                'priority' => 'medium',
                'message' => 'Consider increasing contrast to meet WCAG AAA (7:1)',
                'target' => Color_Constants::WCAG_CONTRAST_TARGET
            ];
        } elseif ($contrast_ratio > Color_Constants::CONTRAST_MAX) {
            $recommendations[] = [
                'priority' => 'low',
                'message' => 'Consider reducing contrast to prevent visual strain',
                'target' => Color_Constants::CONTRAST_MAX
            ];
        }

        return $recommendations;
    }
}
