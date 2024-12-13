<?php
/**
 * Contrast Checker Class
 *
 * Handles contrast ratio calculations and WCAG compliance checks for
 * color combinations. Implements WCAG 2.1 guidelines for text contrast.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Accessibility
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Accessibility;

/**
 * Class ContrastChecker
 *
 * Calculates and validates contrast ratios according to WCAG 2.1 standards.
 * Provides methods for checking text readability and color contrast compliance.
 *
 * @since 1.0.0
 */
class ContrastChecker {
    /**
     * WCAG 2.1 minimum contrast requirements
     */
    const WCAG_AA_NORMAL_MIN = 4.5;  // Normal text (Level AA)
    const WCAG_AA_LARGE_MIN = 3.0;   // Large text (Level AA)
    const WCAG_AAA_NORMAL_MIN = 7.0; // Normal text (Level AAA)
    const WCAG_AAA_LARGE_MIN = 4.5;  // Large text (Level AAA)

    /**
     * Calculate relative luminance
     *
     * @param float $r Red component (0-255)
     * @param float $g Green component (0-255)
     * @param float $b Blue component (0-255)
     * @return float Relative luminance value
     */
    private function get_relative_luminance($r, $g, $b) {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Convert hex to RGB
     *
     * @param string $hex Hex color code
     * @return array RGB color components
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)), // R
            hexdec(substr($hex, 2, 2)), // G
            hexdec(substr($hex, 4, 2))  // B
        ];
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 Hex color code
     * @param string $color2 Hex color code
     * @return float Contrast ratio value
     */
    public function get_contrast_ratio($color1, $color2) {
        $rgb1 = $this->hex_to_rgb($color1);
        $rgb2 = $this->hex_to_rgb($color2);

        $l1 = $this->get_relative_luminance($rgb1[0], $rgb1[1], $rgb1[2]);
        $l2 = $this->get_relative_luminance($rgb2[0], $rgb2[1], $rgb2[2]);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }
}
