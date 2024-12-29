<?php
namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Metrics_Analyzer as Color_Metrics_Analyzer_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;

/**
 * Color Metrics Analyzer
 *
 * Analyzes color metrics for web color palettes, focusing on:
 * - Contrast ratios
 * - WCAG compliance
 * - Color relationships
 */
class Color_Metrics_Analyzer implements Color_Metrics_Analyzer_Interface {
    private Color_Utility $utility;

    /**
     * Constructor
     *
     * @param Color_Utility $utility Color utility instance
     */
    public function __construct(Color_Utility $utility) {
        $this->utility = $utility;
    }

    /**
     * Get color analysis
     *
     * @param string $color Hex color code
     * @return array Color analysis data
     */
    public function get_color_analysis(string $color): array {
        $rgb = $this->utility->hex_to_rgb($color);
        $hsl = $this->utility->rgb_to_hsl($rgb);

        return [
            'color_values' => [
                'hex' => $color,
                'rgb' => $rgb,
                'hsl' => $hsl,
            ],
            'metrics' => [
                'relative_luminance' => $this->utility->get_relative_luminance($color),
                'perceived_brightness' => $this->calculate_perceived_brightness($rgb),
                'saturation' => $hsl['s'],
                'lightness' => $hsl['l']
            ]
        ];
    }

    /**
     * Calculate perceived brightness using standard coefficients
     *
     * @param array $rgb RGB values
     * @return float Perceived brightness (0-255)
     */
    public function calculate_perceived_brightness(array $rgb): float {
        return sqrt(
            pow($rgb['r'] * 0.299, 2) +
            pow($rgb['g'] * 0.587, 2) +
            pow($rgb['b'] * 0.114, 2)
        );
    }

    /**
     * Analyze contrast between colors
     *
     * @param array $colors Array of hex color codes
     * @return array Contrast analysis results
     */
    public function analyze_contrast(array $colors): array {
        $combinations = [];

        for ($i = 0; $i < count($colors); $i++) {
            for ($j = $i + 1; $j < count($colors); $j++) {
                $contrast = $this->utility->get_contrast_ratio($colors[$i], $colors[$j]);
                $score = $this->get_accessibility_score($contrast);

                $combinations[] = [
                    'color1' => $colors[$i],
                    'color2' => $colors[$j],
                    'contrast_ratio' => $contrast,
                    'accessibility_score' => $score,
                    'meets_wcag' => [
                        'AA_large' => $contrast >= Color_Constants::WCAG_CONTRAST_AA_LARGE,
                        'AA' => $contrast >= Color_Constants::WCAG_CONTRAST_AA,
                        'AAA' => $contrast >= Color_Constants::WCAG_CONTRAST_AAA
                    ]
                ];
            }
        }

        return $combinations;
    }

    /**
     * Get accessibility score based on contrast ratio
     *
     * @param float $contrast Contrast ratio
     * @return float Score between 0 and 100
     */
    private function get_accessibility_score(float $contrast): float {
        if ($contrast >= Color_Constants::WCAG_CONTRAST_AAA) return 100.0;  // AAA
        if ($contrast >= Color_Constants::WCAG_CONTRAST_AA) return 80.0;   // AA
        if ($contrast >= Color_Constants::WCAG_CONTRAST_AA_LARGE) return 60.0;   // AA Large

        // Linear score for lower contrasts
        return max(0, min(50, ($contrast / 3.0) * 50));
    }

    /**
     * Check if colors are visually distinct
     *
     * @param array $colors Array of hex color codes
     * @return bool True if colors are visually distinct
     */
    public function are_colors_distinct(array $colors): bool {
        $min_contrast = 1.5; // Minimum contrast for visual distinction

        for ($i = 0; $i < count($colors); $i++) {
            for ($j = $i + 1; $j < count($colors); $j++) {
                $contrast = $this->utility->get_contrast_ratio($colors[$i], $colors[$j]);
                if ($contrast < $min_contrast) {
                    return false;
                }
            }
        }

        return true;
    }
}
