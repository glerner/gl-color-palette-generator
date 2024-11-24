<?php
/**
 * Color Palette Converter Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Converter
 * Converts color palettes between different formats and color spaces
 */
class Color_Palette_Converter implements \GL_Color_Palette_Generator\Interfaces\Color_Palette_Converter {
    /**
     * Convert palette to different color space
     *
     * @param array  $palette Array of hex colors.
     * @param string $target_space Target color space (rgb, hsl, lab, cmyk).
     * @return array Converted colors.
     */
    public function convert_palette($palette, $target_space) {
        return array_map(
            function($color) use ($target_space) {
                return $this->convert_color($color, $target_space);
            },
            $palette
        );
    }

    /**
     * Convert single color to different color space
     *
     * @param string $color Hex color code.
     * @param string $target_space Target color space.
     * @return array|string Converted color.
     * @throws \Exception If target space is invalid.
     */
    public function convert_color($color, $target_space) {
        $rgb = $this->hex_to_rgb($color);

        switch (strtolower($target_space)) {
            case 'rgb':
                return $rgb;
            case 'hsl':
                return $this->rgb_to_hsl($rgb);
            case 'lab':
                return $this->rgb_to_lab($rgb);
            case 'cmyk':
                return $this->rgb_to_cmyk($rgb);
            default:
                throw new \Exception(
                    sprintf(
                        __('Invalid color space: %s', 'gl-color-palette-generator'),
                        $target_space
                    )
                );
        }
    }

    /**
     * Convert hex to RGB
     *
     * @param string $hex Hex color code.
     * @return array RGB values.
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Convert RGB to HSL
     *
     * @param array $rgb RGB values.
     * @return array HSL values.
     */
    private function rgb_to_hsl($rgb) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

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
     * Convert RGB to Lab
     *
     * @param array $rgb RGB values.
     * @return array Lab values.
     */
    private function rgb_to_lab($rgb) {
        // First convert to XYZ
        $xyz = $this->rgb_to_xyz($rgb);

        // Then convert XYZ to Lab
        $white_ref = ['x' => 95.047, 'y' => 100.000, 'z' => 108.883];

        $x = $xyz['x'] / $white_ref['x'];
        $y = $xyz['y'] / $white_ref['y'];
        $z = $xyz['z'] / $white_ref['z'];

        $x = $x > 0.008856 ? pow($x, 1/3) : (7.787 * $x) + 16/116;
        $y = $y > 0.008856 ? pow($y, 1/3) : (7.787 * $y) + 16/116;
        $z = $z > 0.008856 ? pow($z, 1/3) : (7.787 * $z) + 16/116;

        return [
            'l' => round((116 * $y) - 16),
            'a' => round(500 * ($x - $y)),
            'b' => round(200 * ($y - $z))
        ];
    }

    /**
     * Convert RGB to CMYK
     *
     * @param array $rgb RGB values.
     * @return array CMYK values.
     */
    private function rgb_to_cmyk($rgb) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $k = 1 - max($r, $g, $b);

        if ($k == 1) {
            return ['c' => 0, 'm' => 0, 'y' => 0, 'k' => 100];
        }

        return [
            'c' => round(((1 - $r - $k) / (1 - $k)) * 100),
            'm' => round(((1 - $g - $k) / (1 - $k)) * 100),
            'y' => round(((1 - $b - $k) / (1 - $k)) * 100),
            'k' => round($k * 100)
        ];
    }

    /**
     * Convert RGB to XYZ
     *
     * @param array $rgb RGB values.
     * @return array XYZ values.
     */
    private function rgb_to_xyz($rgb) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        // Convert to sRGB
        $r = $r > 0.04045 ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = $g > 0.04045 ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = $b > 0.04045 ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Scale
        $r *= 100;
        $g *= 100;
        $b *= 100;

        return [
            'x' => $r * 0.4124 + $g * 0.3576 + $b * 0.1805,
            'y' => $r * 0.2126 + $g * 0.7152 + $b * 0.0722,
            'z' => $r * 0.0193 + $g * 0.1192 + $b * 0.9505
        ];
    }
}
