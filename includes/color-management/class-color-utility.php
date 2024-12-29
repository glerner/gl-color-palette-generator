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
        // Convert colors to Lab color space
        $lab1 = $this->hex_to_lab($color1);
        $lab2 = $this->hex_to_lab($color2);

        // Calculate Delta E
        $delta_l = $lab1['l'] - $lab2['l'];
        $delta_a = $lab1['a'] - $lab2['a'];
        $delta_b = $lab1['b'] - $lab2['b'];

        return sqrt($delta_l * $delta_l + $delta_a * $delta_a + $delta_b * $delta_b);
    }

    /**
     * Check if colors are visually distinct
     *
     * @param array $colors Array of hex colors
     * @return bool True if colors are visually distinct
     */
    public function are_colors_distinct(array $colors): bool {
        $min_difference = 20; // Minimum Delta E difference for colors to be considered distinct

        // Compare each pair of colors
        $count = count($colors);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->get_color_difference($colors[$i], $colors[$j]) < $min_difference) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get contrast ratio between two colors
     * Based on WCAG 2.0 contrast ratio formula
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float Contrast ratio (1-21)
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
     *
     * @param string $color Color in hex format
     * @return float Relative luminance (0-1)
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
     *
     * @param float $value RGB value (0-1)
     * @return float Luminance value
     */
    private function get_luminance_value(float $value): float {
        return $value <= 0.03928
            ? $value / 12.92
            : pow(($value + 0.055) / 1.055, 2.4);
    }

    /**
     * Convert hex color to RGB array
     *
     * @param string $hex Hex color code
     * @return array RGB values [r, g, b]
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
     */
    public function rgb_to_hex(array $rgb): string {
        return sprintf('#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Convert RGB color to HSL
     *
     * @param array $rgb RGB color values with 'r', 'g', and 'b' keys.
     * @return array HSL values with 'h', 's', and 'l' keys.
     */
    public function rgb_to_hsl(array $rgb): array {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max === $min) {
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

            $h = $h / 6;
        }

        return [
            'h' => (int) round($h * 360),
            's' => (int) round($s * 100),
            'l' => (int) round($l * 100)
        ];
    }

    /**
     * Convert HSL color to RGB
     *
     * @param array $hsl HSL color values with 'h', 's', and 'l' keys.
     * @return array RGB values with 'r', 'g', and 'b' keys.
     */
    public function hsl_to_rgb(array $hsl): array {
        $h = $hsl['h'] / 360;
        $s = $hsl['s'] / 100;
        $l = $hsl['l'] / 100;

        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = $this->hue_to_rgb($p, $q, $h + 1/3);
            $g = $this->hue_to_rgb($p, $q, $h);
            $b = $this->hue_to_rgb($p, $q, $h - 1/3);
        }

        return [
            'r' => (int) round($r * 255),
            'g' => (int) round($g * 255),
            'b' => (int) round($b * 255)
        ];
    }

    /**
     * Helper function to convert hue to RGB
     *
     * @param float $p First value.
     * @param float $q Second value.
     * @param float $t Third value.
     * @return float Resulting value.
     */
    private function hue_to_rgb(float $p, float $q, float $t): float {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1/6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1/2) {
            return $q;
        }
        if ($t < 2/3) {
            return $p + ($q - $p) * (2/3 - $t) * 6;
        }
        return $p;
    }

    /**
     * Convert hex color to Lab color space
     *
     * @param string $hex_color Hex color code
     * @return array Lab values [l, a, b]
     */
    public function hex_to_lab(string $hex_color): array {
        $rgb = $this->hex_to_rgb($hex_color);
        $xyz = $this->rgb_to_xyz($rgb);
        return $this->xyz_to_lab($xyz);
    }

    /**
     * Convert RGB to XYZ color space
     *
     * @param array $rgb RGB values [r, g, b]
     * @return array XYZ values [x, y, z]
     */
    private function rgb_to_xyz(array $rgb): array {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        // Convert to sRGB
        $r = $r > 0.04045 ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = $g > 0.04045 ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = $b > 0.04045 ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ
        return [
            'x' => ($r * 0.4124 + $g * 0.3576 + $b * 0.1805) * 100,
            'y' => ($r * 0.2126 + $g * 0.7152 + $b * 0.0722) * 100,
            'z' => ($r * 0.0193 + $g * 0.1192 + $b * 0.9505) * 100
        ];
    }

    /**
     * Convert XYZ to Lab color space
     *
     * @param array $xyz XYZ values [x, y, z]
     * @return array Lab values [l, a, b]
     */
    private function xyz_to_lab(array $xyz): array {
        // D65 illuminant reference values
        $ref_x = 95.047;
        $ref_y = 100.000;
        $ref_z = 108.883;

        $x = $xyz['x'] / $ref_x;
        $y = $xyz['y'] / $ref_y;
        $z = $xyz['z'] / $ref_z;

        $x = $x > 0.008856 ? pow($x, 1/3) : (7.787 * $x) + 16/116;
        $y = $y > 0.008856 ? pow($y, 1/3) : (7.787 * $y) + 16/116;
        $z = $z > 0.008856 ? pow($z, 1/3) : (7.787 * $z) + 16/116;

        return [
            'l' => (116 * $y) - 16,
            'a' => 500 * ($x - $y),
            'b' => 200 * ($y - $z)
        ];
    }

    /**
     * Convert HSL color to hex
     *
     * @param array $hsl HSL color values with 'h', 's', and 'l' keys.
     * @return string Color in hex format.
     */
    public function hsl_to_hex(array $hsl): string {
        $rgb = $this->hsl_to_rgb($hsl);
        return $this->rgb_to_hex($rgb);
    }

    /**
     * Convert hex color to HSL
     *
     * @param string $hex Color in hex format.
     * @return array HSL values with 'h', 's', and 'l' keys.
     */
    public function hex_to_hsl(string $hex): array {
        $rgb = $this->hex_to_rgb($hex);
        return $this->rgb_to_hsl($rgb);
    }
}
