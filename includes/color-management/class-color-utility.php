<?php declare(strict_types=1);
/**
 * Color Utility Class
 *
 * Provides essential color manipulation and conversion utilities.
 * Handles color space transformations, color calculations, and
 * various color format conversions.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @bootstrap wp-mock
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Traits\Error_Handler;
use GL_Color_Palette_Generator\Traits\Logger;

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
class Color_Utility implements \GL_Color_Palette_Generator\Interfaces\Color_Utility {
    use Error_Handler, Logger;

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
     * Convert HSL to RGB color space
     *
     * @param array $hsl HSL values [h, s, l]
     * @return array RGB values [r, g, b]
     * @since 1.0.0
     */
    public function hsl_to_rgb(array $hsl): array {
        $h = $hsl['h'] / 360;
        $s = $hsl['s'] / 100;
        $l = $hsl['l'] / 100;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = $this->hue_to_rgb($p, $q, $h + 1/3);
            $g = $this->hue_to_rgb($p, $q, $h);
            $b = $this->hue_to_rgb($p, $q, $h - 1/3);
        }

        return [
            'r' => round($r * 255),
            'g' => round($g * 255),
            'b' => round($b * 255)
        ];
    }

    /**
     * Helper function to convert hue to RGB
     * Used by hsl_to_rgb()
     *
     * @param float $p
     * @param float $q
     * @param float $t
     * @return float
     */
    public function hue_to_rgb(float $p, float $q, float $t): float {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    /**
     * Calculate relative luminance of a color
     * According to WCAG 2.0 definition
     *
     * @param string $color Color in hex format
     * @return float Relative luminance value
     * @since 1.0.0
     */
    public function calculate_relative_luminance(string $color): float {
        $rgb = $this->hex_to_rgb($color);

        // Convert to sRGB
        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);

        // Calculate luminance using WCAG coefficients
        return 0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b'];
    }

    /**
     * Convert hex color to Lab color space
     *
     * @param string $hex_color Hex color code.
     * @return array Lab values.
     * @since 1.0.0
     */
    public function hex_to_lab(string $hex_color): array {
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
     * @param array $rgb RGB color values
     * @return array XYZ color values
     */
    public function rgb_to_xyz(array $rgb): array {
        $rgb = array_map(function($value) {
            $value = $value / 255;
            return $value <= 0.04045
                ? $value / 12.92
                : pow(($value + 0.055) / 1.055, 2.4);
        }, $rgb);

        $matrix = Color_Constants::COLOR_SPACE_CONVERSION['rgb_to_xyz'];

        return [
            'x' => $matrix[0][0] * $rgb['r'] + $matrix[0][1] * $rgb['g'] + $matrix[0][2] * $rgb['b'],
            'y' => $matrix[1][0] * $rgb['r'] + $matrix[1][1] * $rgb['g'] + $matrix[1][2] * $rgb['b'],
            'z' => $matrix[2][0] * $rgb['r'] + $matrix[2][1] * $rgb['g'] + $matrix[2][2] * $rgb['b']
        ];
    }

    /**
     * Convert XYZ to RGB color space
     *
     * @param array $xyz XYZ color values
     * @return array RGB color values
     */
    public function xyz_to_rgb(array $xyz): array {
        $matrix = Color_Constants::COLOR_SPACE_CONVERSION['xyz_to_rgb'];

        $rgb = [
            'r' => $matrix[0][0] * $xyz['x'] + $matrix[0][1] * $xyz['y'] + $matrix[0][2] * $xyz['z'],
            'g' => $matrix[1][0] * $xyz['x'] + $matrix[1][1] * $xyz['y'] + $matrix[1][2] * $xyz['z'],
            'b' => $matrix[2][0] * $xyz['x'] + $matrix[2][1] * $xyz['y'] + $matrix[2][2] * $xyz['z']
        ];

        return array_map(function($value) {
            $value = $value <= 0.0031308
                ? 12.92 * $value
                : 1.055 * pow($value, 1/2.4) - 0.055;
            return round(max(0, min(255, $value * 255)));
        }, $rgb);
    }

    /**
     * Convert XYZ to Lab color space
     *
     * @param array $xyz XYZ values.
     * @return array Lab values.
     * @since 1.0.0
     */
    public function xyz_to_lab(array $xyz): array {
        // D65 reference white
        $ref_x = 0.95047;
        $ref_y = 1.00000;
        $ref_z = 1.08883;

        // Scale XYZ values
        $xyz['x'] /= $ref_x;
        $xyz['y'] /= $ref_y;
        $xyz['z'] /= $ref_z;

        // Convert scaled values
        $xyz = array_map(function($value) {
            return $value > 0.008856
                ? pow($value, 1/3)
                : (7.787 * $value) + (16/116);
        }, $xyz);

        return [
            'l' => (116 * $xyz['y']) - 16,
            'a' => 500 * ($xyz['x'] - $xyz['y']),
            'b' => 200 * ($xyz['y'] - $xyz['z'])
        ];
    }

    /**
     * Convert hex color to HSL array
     *
     * @param string $hex Hex color code
     * @return array HSL values [h, s, l]
     * @since 1.0.0
     */
    public function hex_to_hsl(string $hex): array {
        $rgb = $this->hex_to_rgb($hex);
        return $this->rgb_to_hsl($rgb);
    }

    /**
     * Convert HSL color to hex
     *
     * @param array $hsl HSL color array with h, s, l keys
     * @return string Hex color code
     * @since 1.0.0
     */
    public function hsl_to_hex(array $hsl): string {
        $rgb = $this->hsl_to_rgb($hsl);
        return $this->rgb_to_hex($rgb);
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

    /**
     * Get contrast ratio between two colors
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float Contrast ratio
     */
    public function get_contrast_ratio(string $color1, string $color2): float {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get relative luminance of a color
     * Based on WCAG 2.0 relative luminance calculation
     * See: https://www.w3.org/TR/WCAG20/#relativeluminancedef
     *
     * @param string $color Color in hex format
     * @return float Relative luminance
     */
    public function get_relative_luminance(string $color): float {
        $rgb = $this->hex_to_rgb($color);
        $r = $this->get_luminance_value($rgb['r'] / 255);
        $g = $this->get_luminance_value($rgb['g'] / 255);
        $b = $this->get_luminance_value($rgb['b'] / 255);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Get luminance value for a single RGB channel
     * Based on WCAG 2.0 relative luminance calculation
     *
     * @param float $value RGB value (0-1)
     * @return float Luminance value
     */
    public function get_luminance_value(float $value): float {
        return $value <= 0.03928
            ? $value / 12.92
            : pow(($value + 0.055) / 1.055, 2.4);
    }

    /**
     * Darken a color by a percentage
     *
     * @param string $color Hex color code
     * @param int $amount Percentage to darken (0-100)
     * @return string Darkened color hex code
     */
    public function darken_color(string $color, int $amount): string {
        $rgb = $this->hex_to_rgb($color);
        $hsl = $this->rgb_to_hsl($rgb);

        // Decrease lightness
        $hsl['l'] = max(0, $hsl['l'] - ($amount / 100));

        $rgb = $this->hsl_to_rgb($hsl);
        return $this->rgb_to_hex($rgb);
    }

    /**
     * Lighten a color by a percentage
     *
     * @param string $color Hex color code
     * @param int $amount Percentage to lighten (0-100)
     * @return string Lightened color hex code
     */
    public function lighten_color(string $color, int $amount): string {
        $rgb = $this->hex_to_rgb($color);
        $hsl = $this->rgb_to_hsl($rgb);

        // Increase lightness
        $hsl['l'] = min(1, $hsl['l'] + ($amount / 100));

        $rgb = $this->hsl_to_rgb($hsl);
        return $this->rgb_to_hex($rgb);
    }
}
