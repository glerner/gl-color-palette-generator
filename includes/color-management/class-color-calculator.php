<?php declare(strict_types=1);
/**
 * Color Calculator Class
 *
 * Provides mathematical calculations and conversions for color values,
 * including color space transformations, distance metrics, and color
 * relationship calculations.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Calculator_Types;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Traits\Error_Handler;
use GL_Color_Palette_Generator\Traits\Logger;

/**
 * Class Color_Calculator
 *
 * Handles complex color calculations including:
 * - Color space transformations (RGB, HSL, LAB, XYZ)
 * - Color distance metrics (Euclidean, CIEDE2000)
 * - Color mixing and blending operations
 * - Color relationship calculations
 *
 * @since 1.0.0
 */
class Color_Calculator implements Interfaces\Color_Calculator {
    use Error_Handler, Logger;

    /**
     * Color utility instance
     *
     * @var Color_Utility
     * @since 1.0.0
     */
    private $color_utility;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
    }

    /**
     * Calculate color distance using CIEDE2000 formula
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float Color difference value
     * @since 1.0.0
     */
    public function calculate_color_difference(string $color1, string $color2): float {
        $lab1 = $this->color_utility->hex_to_lab($color1);
        $lab2 = $this->color_utility->hex_to_lab($color2);

        return $this->calculate_ciede2000($lab1, $lab2);
    }

    /**
     * Mix two colors with optional weight
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @param float  $weight Mixing weight (0.0 to 1.0)
     * @return string Resulting color in hex format
     * @since 1.0.0
     */
    public function mix_colors(string $color1, string $color2, float $weight = 0.5): string {
        $rgb1 = $this->color_utility->hex_to_rgb($color1);
        $rgb2 = $this->color_utility->hex_to_rgb($color2);

        $mixed = [
            'r' => round($rgb1['r'] * (1 - $weight) + $rgb2['r'] * $weight),
            'g' => round($rgb1['g'] * (1 - $weight) + $rgb2['g'] * $weight),
            'b' => round($rgb1['b'] * (1 - $weight) + $rgb2['b'] * $weight)
        ];

        return $this->color_utility->rgb_to_hex($mixed);
    }

    /**
     * Calculate color harmony relationships
     *
     * @param string $color Base color in hex format
     * @param string $type Harmony type (complementary, analogous, triadic, etc.)
     * @return array Array of related colors in hex format
     * @since 1.0.0
     */
    public function calculate_harmony(string $color, string $type): array {
        $hsl = $this->color_utility->hex_to_hsl($color);
        $harmony_colors = [];

        switch ($type) {
            case 'complementary':
                $harmony_colors[] = $this->rotate_hue($hsl, 180);
                break;
            case 'analogous':
                $harmony_colors[] = $this->rotate_hue($hsl, -30);
                $harmony_colors[] = $this->rotate_hue($hsl, 30);
                break;
            case 'triadic':
                $harmony_colors[] = $this->rotate_hue($hsl, 120);
                $harmony_colors[] = $this->rotate_hue($hsl, 240);
                break;
            case 'split-complementary':
                $harmony_colors[] = $this->rotate_hue($hsl, 150);
                $harmony_colors[] = $this->rotate_hue($hsl, 210);
                break;
        }

        return $harmony_colors;
    }

    /**
     * Calculate weighted color blend
     *
     * @param array $colors Array of colors in hex format
     * @param array $weights Array of corresponding weights
     * @return string Resulting color in hex format
     * @since 1.0.0
     */
    public function calculate_weighted_blend(array $colors, array $weights): string {
        if (count($colors) !== count($weights)) {
            throw new \InvalidArgumentException('Colors and weights arrays must have the same length');
        }

        $total_weight = array_sum($weights);
        if ($total_weight === 0) {
            throw new \InvalidArgumentException('Sum of weights must be greater than 0');
        }

        $rgb_sum = ['r' => 0, 'g' => 0, 'b' => 0];
        foreach ($colors as $i => $color) {
            $rgb = $this->color_utility->hex_to_rgb($color);
            $weight = $weights[$i] / $total_weight;

            $rgb_sum['r'] += $rgb['r'] * $weight;
            $rgb_sum['g'] += $rgb['g'] * $weight;
            $rgb_sum['b'] += $rgb['b'] * $weight;
        }

        $blended = [
            'r' => round($rgb_sum['r']),
            'g' => round($rgb_sum['g']),
            'b' => round($rgb_sum['b'])
        ];

        return $this->color_utility->rgb_to_hex($blended);
    }

    /**
     * Rotate hue value while maintaining saturation and lightness
     *
     * @param array $hsl HSL color values
     * @param int   $degrees Rotation amount in degrees
     * @return string Resulting color in hex format
     * @since 1.0.0
     */
    private function rotate_hue(array $hsl, int $degrees): string {
        $hsl['h'] = ($hsl['h'] + $degrees) % 360;
        if ($hsl['h'] < 0) {
            $hsl['h'] += 360;
        }
        return $this->color_utility->hsl_to_hex($hsl);
    }

    /**
     * Calculate CIEDE2000 color difference
     *
     * @param array $lab1 First color in LAB format
     * @param array $lab2 Second color in LAB format
     * @return float Color difference value
     * @since 1.0.0
     */
    private function calculate_ciede2000(array $lab1, array $lab2): float {
        // Implementation of CIEDE2000 formula
        // This is a complex calculation that considers human perception
        // Reference: http://www.ece.rochester.edu/~gsharma/ciede2000/

        // Placeholder for actual implementation
        return 0.0;
    }
}
