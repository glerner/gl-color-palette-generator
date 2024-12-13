<?php
/**
 * Color Utility Class
 *
 * Provides essential color manipulation and conversion utilities.
 * Handles color space transformations, color calculations, and
 * various color format conversions.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Utility
 *
 * Core utility class for color operations including:
 * - Color space conversions (RGB, HSL, Lab, CMYK)
 * - Color difference calculations
 * - Color manipulation (lighten, darken, saturate)
 * - Format validation and normalization
 *
 * @since 1.0.0
 */
class Color_Utility {
    /**
     * Color space conversion matrices
     *
     * @var array
     * @since 1.0.0
     */
    private const COLOR_MATRICES = [
        'rgb_to_xyz' => [
            [0.4124564, 0.3575761, 0.1804375],
            [0.2126729, 0.7151522, 0.0721750],
            [0.0193339, 0.1191920, 0.9503041]
        ],
        'xyz_to_rgb' => [
            [3.2404542, -1.5371385, -0.4985314],
            [-0.9692660, 1.8760108, 0.0415560],
            [0.0556434, -0.2040259, 1.0572252]
        ]
    ];

    /**
     * Get perceptual color difference between two colors
     * Uses Delta E (CIE76) formula for simplicity and performance
     *
     * @param string $color1 First hex color
     * @param string $color2 Second hex color
     * @return float Delta E value (0-100)
     * @since 1.0.0
     */
    public function get_color_difference(string $color1, string $color2): float {
        // Convert hex to Lab color space
        $lab1 = $this->hex_to_lab($color1);
        $lab2 = $this->hex_to_lab($color2);

        // Calculate Euclidean distance in Lab space
        return sqrt(
            pow($lab2['l'] - $lab1['l'], 2) +
            pow($lab2['a'] - $lab1['a'], 2) +
            pow($lab2['b'] - $lab1['b'], 2)
        );
    }

    /**
     * Convert hex color to RGB array
     *
     * @param string $hex Hex color code
     * @return array RGB values [r, g, b]
     * @since 1.0.0
     */
    public function hex_to_rgb(string $hex): array {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Convert RGB to hex color
     *
     * @param array $rgb RGB values [r, g, b]
     * @return string Hex color code
     * @since 1.0.0
     */
    public function rgb_to_hex(array $rgb): string {
        return sprintf('#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Convert RGB to HSL color space
     *
     * @param array $rgb RGB values [r, g, b]
     * @return array HSL values [h, s, l]
     * @since 1.0.0
     */
    public function rgb_to_hsl(array $rgb): array {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return [
            'h' => round($h * 360),
            's' => round($s * 100),
            'l' => round($l * 100)
        ];
    }

    /**
     * Convert hex color to Lab color space
     *
     * @param string $hex_color Hex color code.
     * @return array Lab values.
     * @since 1.0.0
     */
    private function hex_to_lab(string $hex_color): array {
        // First convert hex to RGB
        $rgb = $this->hex_to_rgb($hex_color);

        // Convert RGB to XYZ
        $xyz = $this->rgb_to_xyz($rgb);

        // Convert XYZ to Lab
        return $this->xyz_to_lab($xyz);
    }

    /**
     * Convert RGB to XYZ color space
     *
     * @param array $rgb RGB values (0-1).
     * @return array XYZ values.
     * @since 1.0.0
     */
    private function rgb_to_xyz(array $rgb): array {
        // Convert RGB to linear RGB
        foreach ($rgb as &$val) {
            $val = ($val > 0.04045)
                ? pow(($val + 0.055) / 1.055, 2.4)
                : $val / 12.92;
        }

        // Convert to XYZ using sRGB/D65 matrix
        return [
            'x' => $rgb['r'] * 0.4124 + $rgb['g'] * 0.3576 + $rgb['b'] * 0.1805,
            'y' => $rgb['r'] * 0.2126 + $rgb['g'] * 0.7152 + $rgb['b'] * 0.0722,
            'z' => $rgb['r'] * 0.0193 + $rgb['g'] * 0.1192 + $rgb['b'] * 0.9505,
        ];
    }

    /**
     * Convert XYZ to Lab color space
     *
     * @param array $xyz XYZ values.
     * @return array Lab values.
     * @since 1.0.0
     */
    private function xyz_to_lab(array $xyz): array {
        // D65 reference white
        $ref_x = 0.95047;
        $ref_y = 1.00000;
        $ref_z = 1.08883;

        $xyz['x'] /= $ref_x;
        $xyz['y'] /= $ref_y;
        $xyz['z'] /= $ref_z;

        foreach ($xyz as &$val) {
            $val = ($val > 0.008856)
                ? pow($val, 1/3)
                : (7.787 * $val) + (16/116);
        }

        return [
            'l' => (116 * $xyz['y']) - 16,
            'a' => 500 * ($xyz['x'] - $xyz['y']),
            'b' => 200 * ($xyz['y'] - $xyz['z']),
        ];
    }

    /**
     * Check if colors are visually distinct
     *
     * @param array $colors Array of hex colors.
     * @return bool True if colors are visually distinct.
     * @since 1.0.0
     */
    public function are_colors_distinct(array $colors): bool {
        // Minimum perceptual difference threshold
        $min_difference = 25;

        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i >= $j) {
                    continue;
                }

                if ($this->get_color_difference($color1, $color2) < $min_difference) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get color distinctiveness score
     * Higher score means colors are more visually distinct from each other
     *
     * @param array $colors Array of hex colors.
     * @return float Distinctiveness score (0-100).
     * @since 1.0.0
     */
    public function get_distinctiveness_score(array $colors): float {
        $differences = [];

        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i >= $j) {
                    continue;
                }
                $differences[] = $this->get_color_difference($color1, $color2);
            }
        }

        // Return average difference, normalized to 0-100
        return min(100, array_sum($differences) / count($differences));
    }
}
