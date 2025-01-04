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
 * - Color space conversions (RGB, HSL, HSV)
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
     * Uses RGB Euclidean distance for simplicity
     *
     * @param string $color1 First hex color
     * @param string $color2 Second hex color
     * @return float Color difference value (0-442)
     * @since 1.0.0
     */
    public function get_color_difference(string $color1, string $color2): float {
        // Convert colors to RGB
        $rgb1 = $this->hex_to_rgb($color1);
        $rgb2 = $this->hex_to_rgb($color2);

        // Calculate Euclidean distance in RGB space
        $r_diff = $rgb1['r'] - $rgb2['r'];
        $g_diff = $rgb1['g'] - $rgb2['g'];
        $b_diff = $rgb1['b'] - $rgb2['b'];

        return sqrt($r_diff * $r_diff + $g_diff * $g_diff + $b_diff * $b_diff);
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
     * Convert hex color to HSL
     *
     * @param string $hex Color in hex format.
     * @return array HSL values with 'h', 's', and 'l' keys.
     */
    public function hex_to_hsl(string $hex): array {
        $rgb = $this->hex_to_rgb($hex);
        return $this->rgb_to_hsl($rgb);
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
     * Convert RGB to HSV
     *
     * @param array $rgb RGB values [r, g, b]
     * @return array HSV values [h, s, v]
     */
    public function rgb_to_hsv(array $rgb): array {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $diff = $max - $min;

        $h = 0;
        $s = ($max === 0) ? 0 : ($diff / $max);
        $v = $max;

        if ($diff !== 0) {
            switch ($max) {
                case $r:
                    $h = 60 * fmod(($g - $b) / $diff, 6);
                    break;
                case $g:
                    $h = 60 * (($b - $r) / $diff + 2);
                    break;
                case $b:
                    $h = 60 * (($r - $g) / $diff + 4);
                    break;
            }
        }

        if ($h < 0) {
            $h += 360;
        }

        return [
            'h' => $h,
            's' => $s * 100,
            'v' => $v * 100
        ];
    }

    /**
     * Convert HSV to RGB
     *
     * @param array $hsv HSV values [h, s, v]
     * @return array RGB values [r, g, b]
     */
    public function hsv_to_rgb(array $hsv): array {
        $h = $hsv['h'];
        $s = $hsv['s'] / 100;
        $v = $hsv['v'] / 100;

        $c = $v * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $v - $c;

        if ($h >= 0 && $h < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($h >= 60 && $h < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($h >= 120 && $h < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($h >= 180 && $h < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($h >= 240 && $h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }

        return [
            'r' => round(($r + $m) * 255),
            'g' => round(($g + $m) * 255),
            'b' => round(($b + $m) * 255)
        ];
    }

    /**
     * Convert hex color to HSV
     *
     * @param string $hex Color in hex format
     * @return array HSV values [h, s, v]
     */
    public function hex_to_hsv(string $hex): array {
        return $this->rgb_to_hsv($this->hex_to_rgb($hex));
    }

    /**
     * Convert HSV to hex color
     *
     * @param array $hsv HSV values [h, s, v]
     * @return string Color in hex format
     */
    public function hsv_to_hex(array $hsv): string {
        return $this->rgb_to_hex($this->hsv_to_rgb($hsv));
    }

    public function hex_to_lab(string $hex): array {
        $rgb = $this->hex_to_rgb($hex);

        // Convert to sRGB
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ
        $x = ($r * 0.4124 + $g * 0.3576 + $b * 0.1805) * 100;
        $y = ($r * 0.2126 + $g * 0.7152 + $b * 0.0722) * 100;
        $z = ($r * 0.0193 + $g * 0.1192 + $b * 0.9505) * 100;

        // Convert XYZ to LAB
        $x = $x / 95.047;
        $y = $y / 100.000;
        $z = $z / 108.883;

        $x = ($x > 0.008856) ? pow($x, 1/3) : (7.787 * $x) + 16/116;
        $y = ($y > 0.008856) ? pow($y, 1/3) : (7.787 * $y) + 16/116;
        $z = ($z > 0.008856) ? pow($z, 1/3) : (7.787 * $z) + 16/116;

        return [
            'l' => (116 * $y) - 16,
            'a' => 500 * ($x - $y),
            'b' => 200 * ($y - $z)
        ];
    }

    /**
     * Darken a color by a percentage
     *
     * @param string $color Hex color code
     * @param int    $percentage Percentage to darken (0-100)
     * @return string Darkened color in hex format
     */
    public function darken_color(string $color, int $percentage): string {
        $hsl = $this->hex_to_hsl($color);
        $hsl['l'] = max(0, $hsl['l'] - ($percentage / 100));
        return $this->hsl_to_hex($hsl);
    }

    /**
     * Lighten a color by a percentage
     *
     * @param string $color Hex color code
     * @param int    $percentage Percentage to lighten (0-100)
     * @return string Lightened color in hex format
     */
    public function lighten_color(string $color, int $percentage): string {
        $hsl = $this->hex_to_hsl($color);
        $hsl['l'] = min(100, $hsl['l'] + ($percentage / 100));
        return $this->hsl_to_hex($hsl);
    }

    /**
     * Validate hex color format
     *
     * @param string $hex Color in hex format
     * @return bool True if valid hex color
     */
    public function is_valid_hex_color(string $hex): bool {
        return preg_match('/^#[a-fA-F0-9]{6}$/', $hex) === 1;
    }

    /**
     * Convert multiple colors to a different color space
     *
     * @param array  $colors Array of colors to convert
     * @param string $from   Source color space (hex, rgb, hsl, hsv)
     * @param string $to     Target color space (rgb, hsl, hsv)
     * @param array  $options {
     *     Optional. Conversion options.
     *     @type int    $precision     Number of decimal places
     *     @type string $gamut         Gamut mapping strategy
     * }
     * @return array {
     *     Conversion results
     *     @type array $converted Converted colors
     *     @type array $original  Original colors
     *     @type array $mapping   Color space mapping details
     * }
     */
    public function convert_colors(array $colors, string $from, string $to, array $options = []): array {
        $converted = [];
        $precision = $options['precision'] ?? 2;

        foreach ($colors as $key => $color) {
            // Convert to RGB as intermediate format if needed
            $rgb = match($from) {
                'hex' => $this->hex_to_rgb($color),
                'hsl' => $this->hsl_to_rgb($color),
                'hsv' => $this->hsv_to_rgb($color),
                'rgb' => $color,
                default => throw new \InvalidArgumentException("Unsupported source color space: $from")
            };

            // Convert from RGB to target format
            $converted[$key] = match($to) {
                'rgb' => $rgb,
                'hsl' => $this->rgb_to_hsl($rgb),
                'hsv' => $this->rgb_to_hsv($rgb),
                default => throw new \InvalidArgumentException("Unsupported target color space: $to")
            };

            // Round values if precision is specified
            if (is_array($converted[$key])) {
                array_walk_recursive($converted[$key], function(&$n) use ($precision) {
                    $n = round($n, $precision);
                });
            }
        }

        return [
            'converted' => $converted,
            'original' => $colors,
            'mapping' => [
                'from' => $from,
                'to' => $to,
                'options' => $options
            ]
        ];
    }

    /**
     * Convert colors to different format (css, scss, json)
     *
     * @param array  $colors  Colors to convert
     * @param string $format  Target format
     * @param array  $options Format options
     * @return array Formatted colors with metadata
     */
    public function format_colors(array $colors, string $format, array $options = []): array {
        $formatted = [];

        foreach ($colors as $name => $color) {
            switch ($format) {
                case 'css':
                    $formatted[$name] = is_array($color) ?
                        $this->array_to_css_color($color) :
                        $color;
                    break;

                case 'scss':
                    $formatted[$name] = sprintf(
                        '$%s: %s;',
                        str_replace(' ', '-', strtolower($name)),
                        is_array($color) ? $this->array_to_css_color($color) : $color
                    );
                    break;

                case 'json':
                    $formatted[$name] = is_array($color) ?
                        $color :
                        $this->hex_to_rgb($color);
                    break;

                default:
                    throw new \InvalidArgumentException("Unsupported format: $format");
            }
        }

        return [
            'converted' => $formatted,
            'original' => $colors,
            'format' => [
                'name' => $format,
                'options' => $options
            ]
        ];
    }

    /**
     * Convert color array to CSS color string
     *
     * @param array $color Color values
     * @return string CSS color string
     */
    private function array_to_css_color(array $color): string {
        if (isset($color['r'])) {
            return sprintf('rgb(%d, %d, %d)', $color['r'], $color['g'], $color['b']);
        } elseif (isset($color['h'])) {
            return sprintf('hsl(%d, %d%%, %d%%)', $color['h'], $color['s'] * 100, $color['l'] * 100);
        } elseif (isset($color['hsv'])) {
            return sprintf('hsv(%d, %d%%, %d%%)', $color['h'], $color['s'] * 100, $color['v'] * 100);
        }
        throw new \InvalidArgumentException('Unsupported color format');
    }

        /**
     * Get the hue component of a color in HSL space (0-360 degrees)
     *
     * @param string $hex_color The hex color code
     * @return int The hue value (0-360)
     */
    public function get_hue($hex_color): int {
        list($r, $g, $b) = $this->hex_to_rgb($hex_color);

        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        if ($max === $min) {
            return 0;
        }

        $d = $max - $min;

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

        return (int) round($h * 60);
    }

    /**
     * Get the saturation component of a color in HSL space (0-100 percent)
     *
     * @param string $hex_color The hex color code
     * @return int The saturation value (0-100)
     */
    public function get_saturation($hex_color): int {
        list($r, $g, $b) = $this->hex_to_rgb($hex_color);

        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $l = ($max + $min) / 2;

        if ($max === $min) {
            return 0;
        }

        $d = $max - $min;

        return (int) round(($d / (1 - abs(2 * $l - 1))) * 100);
    }

    /**
     * Calculate WCAG 2.0 contrast ratio between two colors
     *
     * @param string $color1 First hex color
     * @param string $color2 Second hex color
     * @return float Contrast ratio (1-21)
     */
    public function check_contrast_ratio($color1, $color2): float {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

}
