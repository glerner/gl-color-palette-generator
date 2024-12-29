<?php
/**
 * Accessibility Checker Class
 *
 * Handles color accessibility checks and WCAG compliance validation.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Accessibility
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Accessibility;

use WP_Error;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Interfaces\Accessibility_Checker as Accessibility_Checker_Interface;

/**
 * Class Accessibility_Checker
 *
 * Provides methods for checking color accessibility and WCAG compliance.
 *
 * @since 1.0.0
 */
class Accessibility_Checker implements Accessibility_Checker_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
    }

    /**
     * Check if a color combination meets WCAG 2.1 AA requirements
     *
     * @param string $foreground Foreground color hex code
     * @param string $background Background color hex code
     * @return array Results with contrast ratio and pass/fail status
     */
    public function check_contrast(string $foreground, string $background): array {
        $ratio = $this->color_utility->get_contrast_ratio($foreground, $background);

        return [
            'contrast_ratio' => round($ratio, 2),
            'passes_aa' => $ratio >= Color_Constants::WCAG_CONTRAST_AA,
            'required_ratio' => Color_Constants::WCAG_CONTRAST_AA,
            'target_ratio' => Color_Constants::WCAG_CONTRAST_TARGET
        ];
    }

    /**
     * Get suggested color adjustments to meet WCAG AA contrast requirements
     *
     * @param string $foreground Foreground color hex code
     * @param string $background Background color hex code
     * @return array Suggested adjustments
     */
    public function get_contrast_suggestions(string $foreground, string $background): array {
        $check = $this->check_contrast($foreground, $background);

        if ($check['passes_aa']) {
            return [
                'status' => 'pass',
                'message' => 'Colors meet WCAG AA contrast requirements'
            ];
        }

        $darker = $this->color_utility->darken_color($foreground, 10);
        $lighter = $this->color_utility->lighten_color($foreground, 10);

        $darker_ratio = $this->color_utility->get_contrast_ratio($darker, $background);
        $lighter_ratio = $this->color_utility->get_contrast_ratio($lighter, $background);

        return [
            'status' => 'fail',
            'current_ratio' => $check['contrast_ratio'],
            'required_ratio' => $check['required_ratio'],
            'target_ratio' => Color_Constants::WCAG_CONTRAST_TARGET,
            'suggestions' => [
                'darker' => [
                    'color' => $darker,
                    'ratio' => round($darker_ratio, 2)
                ],
                'lighter' => [
                    'color' => $lighter,
                    'ratio' => round($lighter_ratio, 2)
                ]
            ]
        ];
    }

    /**
     * Check color combination for accessibility
     *
     * @param string $foreground Foreground color hex code
     * @param string $background Background color hex code
     * @return array Results with contrast ratio and pass/fail status
     */
    public function check_combination(string $foreground, string $background): array {
        $ratio = $this->color_utility->get_contrast_ratio($foreground, $background);

        return [
            'contrast_ratio' => round($ratio, 2),
            'aa_small' => $ratio >= Color_Constants::WCAG_CONTRAST_AA,
            'aa_large' => $ratio >= Color_Constants::WCAG_CONTRAST_AA_LARGE,
            'aaa_small' => $ratio >= Color_Constants::WCAG_CONTRAST_AAA,
            'aaa_large' => $ratio >= Color_Constants::WCAG_CONTRAST_AAA
        ];
    }

    /**
     * Check color palette for accessibility
     *
     * @param array $palette Color palette
     * @return array Results with combinations and pass/fail status
     */
    public function check_palette(array $palette): array {
        $combinations = [];
        $all_aa = true;
        $all_aaa = true;
        $recommendations = [];

        // Check all color combinations
        foreach ($palette as $i => $color1) {
            foreach ($palette as $j => $color2) {
                if ($i !== $j) {
                    $check = $this->check_combination($color1, $color2);
                    $combinations[] = [
                        'color1' => $color1,
                        'color2' => $color2,
                        'results' => $check
                    ];

                    if (!$check['aa_small']) {
                        $all_aa = false;
                        $recommendations[] = "Insufficient contrast ({$check['contrast_ratio']}) between $color1 and $color2";
                    }
                    if (!$check['aaa_small']) {
                        $all_aaa = false;
                    }
                }
            }
        }

        return [
            'combinations' => $combinations,
            'wcag_aa' => $all_aa,
            'wcag_aaa' => $all_aaa,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Calculate accessibility score for color palette
     *
     * @param array $palette Color palette
     * @return float Accessibility score
     */
    public function calculate_score(array $palette): float {
        $combinations = $this->check_palette($palette);
        $total_score = 0;
        $count = 0;

        foreach ($combinations['combinations'] as $combo) {
            $ratio = $combo['results']['contrast_ratio'];
            // Score based on how well it meets or exceeds WCAG AAA (7:1)
            $total_score += min(10, ($ratio / 7) * 10);
            $count++;
        }

        return $count > 0 ? round($total_score / $count, 2) : 0;
    }
}
