<?php
/**
 * Color Utility Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Utility
 */
class Color_Utility {
    /**
     * Get perceptual color difference between two colors
     * Uses Delta E (CIE76) formula for simplicity and performance
     *
     * @param string $color1 First hex color.
     * @param string $color2 Second hex color.
     * @return float Color difference value (0-100).
     */
    public function get_color_difference($color1, $color2) {
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
     * Convert hex color to Lab color space
     *
     * @param string $hex_color Hex color code.
     * @return array Lab values.
     */
    private function hex_to_lab($hex_color) {
        // First convert hex to RGB
        $rgb = $this->hex_to_rgb($hex_color);

        // Convert RGB to XYZ
        $xyz = $this->rgb_to_xyz($rgb);

        // Convert XYZ to Lab
        return $this->xyz_to_lab($xyz);
    }

    /**
     * Convert hex color to RGB array
     *
     * @param string $hex_color Hex color code.
     * @return array RGB values (0-1).
     */
    private function hex_to_rgb($hex_color) {
        $hex = ltrim($hex_color, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)) / 255,
            'g' => hexdec(substr($hex, 2, 2)) / 255,
            'b' => hexdec(substr($hex, 4, 2)) / 255,
        ];
    }

    /**
     * Convert RGB to XYZ color space
     *
     * @param array $rgb RGB values (0-1).
     * @return array XYZ values.
     */
    private function rgb_to_xyz($rgb) {
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
     */
    private function xyz_to_lab($xyz) {
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
     */
    public function are_colors_distinct($colors) {
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
     */
    public function get_distinctiveness_score($colors) {
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
