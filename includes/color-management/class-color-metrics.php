<?php
/**
 * Color Metrics Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Metrics_Interface;
use WP_Error;

/**
 * Class Color_Metrics
 */
class Color_Metrics implements Color_Metrics_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private Color_Utility $color_util;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_util = new Color_Utility();
    }

    /**
     * Calculate color difference using CIEDE2000
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float|WP_Error Difference value or error
     */
    public function calculate_color_difference($color1, $color2) {
        try {
            // Convert hex to Lab color space
            $lab1 = $this->color_util->hex_to_lab($color1);
            $lab2 = $this->color_util->hex_to_lab($color2);

            // Calculate CIEDE2000 difference
            return $this->calculate_ciede2000($lab1, $lab2);
        } catch (\Exception $e) {
            return new WP_Error(
                'color_difference_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate perceived brightness
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Brightness value (0-1) or error
     */
    public function calculate_brightness($color) {
        try {
            $rgb = $this->color_util->hex_to_rgb($color);
            // Using perceived brightness formula (ITU-R BT.709)
            return (0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b']) / 255;
        } catch (\Exception $e) {
            return new WP_Error(
                'brightness_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color saturation
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Saturation value (0-1) or error
     */
    public function calculate_saturation($color) {
        try {
            $hsl = $this->color_util->hex_to_hsl($color);
            return $hsl['s'];
        } catch (\Exception $e) {
            return new WP_Error(
                'saturation_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color temperature
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Temperature in Kelvin or error
     */
    public function calculate_temperature($color) {
        try {
            $rgb = $this->color_util->hex_to_rgb($color);
            
            // Using McCamy's formula
            $x = ($rgb['r'] * 0.3320 + $rgb['g'] * 0.1858) / ($rgb['r'] * 0.1735 + $rgb['g'] * 0.0180);
            $y = ($rgb['r'] * 0.3320 + $rgb['g'] * 0.1858) / ($rgb['r'] * 0.0241 + $rgb['g'] * 0.0738);
            
            return 449 * pow($x + 0.3320, 3) * pow($y + 0.1858, 2);
        } catch (\Exception $e) {
            return new WP_Error(
                'temperature_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color harmony score
     *
     * @param array $colors Array of colors in hex format
     * @return float|WP_Error Harmony score (0-1) or error
     */
    public function calculate_harmony_score($colors) {
        try {
            if (count($colors) < 2) {
                throw new \Exception(__('Need at least 2 colors to calculate harmony', 'gl-color-palette-generator'));
            }

            $total_score = 0;
            $comparisons = 0;

            // Calculate average color difference and hue spacing
            for ($i = 0; $i < count($colors); $i++) {
                for ($j = $i + 1; $j < count($colors); $j++) {
                    $diff = $this->calculate_color_difference($colors[$i], $colors[$j]);
                    if (is_wp_error($diff)) {
                        throw new \Exception($diff->get_error_message());
                    }
                    
                    $hsl1 = $this->color_util->hex_to_hsl($colors[$i]);
                    $hsl2 = $this->color_util->hex_to_hsl($colors[$j]);
                    $hue_diff = abs($hsl1['h'] - $hsl2['h']);
                    
                    // Normalize difference and hue spacing
                    $norm_diff = max(0, min(1, $diff / 100));
                    $norm_hue = max(0, min(1, $hue_diff / 180));
                    
                    $total_score += ($norm_diff + $norm_hue) / 2;
                    $comparisons++;
                }
            }

            return $total_score / $comparisons;
        } catch (\Exception $e) {
            return new WP_Error(
                'harmony_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color complexity
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Complexity score (0-1) or error
     */
    public function calculate_complexity($color) {
        try {
            $rgb = $this->color_util->hex_to_rgb($color);
            $hsl = $this->color_util->hex_to_hsl($color);
            
            // Consider saturation, brightness variations, and RGB channel differences
            $rgb_diff = max($rgb['r'], $rgb['g'], $rgb['b']) - min($rgb['r'], $rgb['g'], $rgb['b']);
            $sat_impact = $hsl['s'];
            $light_impact = abs(0.5 - $hsl['l']);
            
            return ($rgb_diff / 255 + $sat_impact + $light_impact) / 3;
        } catch (\Exception $e) {
            return new WP_Error(
                'complexity_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color dominance in a palette
     *
     * @param string $color Color in hex format
     * @param array  $palette Array of palette colors
     * @return float|WP_Error Dominance score (0-1) or error
     */
    public function calculate_dominance($color, $palette) {
        try {
            if (!in_array($color, $palette)) {
                throw new \Exception(__('Color not found in palette', 'gl-color-palette-generator'));
            }

            $total_difference = 0;
            foreach ($palette as $other_color) {
                if ($color !== $other_color) {
                    $diff = $this->calculate_color_difference($color, $other_color);
                    if (is_wp_error($diff)) {
                        throw new \Exception($diff->get_error_message());
                    }
                    $total_difference += $diff;
                }
            }

            // Normalize the dominance score
            return $total_difference / (count($palette) - 1) / 100;
        } catch (\Exception $e) {
            return new WP_Error(
                'dominance_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color balance in a palette
     *
     * @param array $colors Array of colors in hex format
     * @return array|WP_Error Balance metrics or error
     */
    public function calculate_balance($colors) {
        try {
            $total_r = 0;
            $total_g = 0;
            $total_b = 0;
            $total_h = 0;
            $total_s = 0;
            $total_l = 0;

            foreach ($colors as $color) {
                $rgb = $this->color_util->hex_to_rgb($color);
                $hsl = $this->color_util->hex_to_hsl($color);

                $total_r += $rgb['r'];
                $total_g += $rgb['g'];
                $total_b += $rgb['b'];
                $total_h += $hsl['h'];
                $total_s += $hsl['s'];
                $total_l += $hsl['l'];
            }

            $count = count($colors);
            return [
                'rgb_balance' => [
                    'r' => $total_r / ($count * 255),
                    'g' => $total_g / ($count * 255),
                    'b' => $total_b / ($count * 255)
                ],
                'hsl_balance' => [
                    'h' => $total_h / ($count * 360),
                    's' => $total_s / $count,
                    'l' => $total_l / $count
                ]
            ];
        } catch (\Exception $e) {
            return new WP_Error(
                'balance_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color weight
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Weight value (0-1) or error
     */
    public function calculate_weight($color) {
        try {
            $rgb = $this->color_util->hex_to_rgb($color);
            $hsl = $this->color_util->hex_to_hsl($color);
            
            // Consider both luminance and saturation
            $luminance = $this->calculate_brightness($color);
            if (is_wp_error($luminance)) {
                throw new \Exception($luminance->get_error_message());
            }
            
            return (1 - $luminance) * (1 + $hsl['s']) / 2;
        } catch (\Exception $e) {
            return new WP_Error(
                'weight_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color energy
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Energy value (0-1) or error
     */
    public function calculate_energy($color) {
        try {
            $hsl = $this->color_util->hex_to_hsl($color);
            $complexity = $this->calculate_complexity($color);
            if (is_wp_error($complexity)) {
                throw new \Exception($complexity->get_error_message());
            }
            
            // Consider saturation, lightness, and complexity
            return ($hsl['s'] + abs(0.5 - $hsl['l']) + $complexity) / 3;
        } catch (\Exception $e) {
            return new WP_Error(
                'energy_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate color contrast ratio
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float|WP_Error Contrast ratio (1-21) or error
     */
    public function calculate_contrast_ratio($color1, $color2) {
        try {
            $l1 = $this->color_util->get_relative_luminance($color1);
            $l2 = $this->color_util->get_relative_luminance($color2);
            
            $lighter = max($l1, $l2);
            $darker = min($l1, $l2);
            
            return ($lighter + 0.05) / ($darker + 0.05);
        } catch (\Exception $e) {
            return new WP_Error(
                'contrast_ratio_calculation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Calculate CIEDE2000 color difference
     *
     * @param array $lab1 First color in Lab format
     * @param array $lab2 Second color in Lab format
     * @return float CIEDE2000 difference value
     */
    private function calculate_ciede2000($lab1, $lab2) {
        // Implementation of CIEDE2000 formula
        // This is a simplified version, the full implementation would be quite long
        $delta_l = $lab2['l'] - $lab1['l'];
        $delta_a = $lab2['a'] - $lab1['a'];
        $delta_b = $lab2['b'] - $lab1['b'];
        
        $c1 = sqrt(pow($lab1['a'], 2) + pow($lab1['b'], 2));
        $c2 = sqrt(pow($lab2['a'], 2) + pow($lab2['b'], 2));
        
        $delta_c = $c2 - $c1;
        
        // Simplified CIEDE2000 calculation
        return sqrt(
            pow($delta_l, 2) +
            pow($delta_c, 2) +
            pow($delta_a, 2) +
            pow($delta_b, 2)
        );
    }
}
