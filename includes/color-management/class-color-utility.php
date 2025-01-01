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
 * - Color space conversions (RGB, HSL)
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
     * Convert multiple colors to a different color space
     *
     * @param array  $colors Array of colors to convert
     * @param string $from   Source color space (hex, rgb, hsl)
     * @param string $to     Target color space (rgb, hsl)
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
                'rgb' => $color,
                default => throw new \InvalidArgumentException("Unsupported source color space: $from")
            };

            // Convert from RGB to target format
            $converted[$key] = match($to) {
                'rgb' => $rgb,
                'hsl' => $this->rgb_to_hsl($rgb),
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
        }
        throw new \InvalidArgumentException('Unsupported color format');
    }
}
