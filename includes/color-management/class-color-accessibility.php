<?php
/**
 * Color Accessibility Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Accessibility_Interface;
use WP_Error;

/**
 * Class Color_Accessibility
 */
class Color_Accessibility implements Color_Accessibility_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private Color_Utility $color_util;

    /**
     * Color metrics instance
     *
     * @var Color_Metrics
     */
    private Color_Metrics $color_metrics;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_util = new Color_Utility();
        $this->color_metrics = new Color_Metrics();
    }

    /**
     * Check contrast ratio between two colors
     *
     * @param string $color1 First color (hex format)
     * @param string $color2 Second color (hex format)
     * @return float|WP_Error Contrast ratio or error
     */
    public function get_contrast_ratio($color1, $color2) {
        return $this->color_metrics->calculate_contrast_ratio($color1, $color2);
    }

    /**
     * Check if color combination meets WCAG contrast requirements
     *
     * @param string $foreground Foreground color (hex format)
     * @param string $background Background color (hex format)
     * @param string $level WCAG level ('AA' or 'AAA')
     * @param string $size Text size ('normal' or 'large')
     * @return bool|WP_Error True if meets requirements, error if not
     */
    public function meets_wcag_contrast($foreground, $background, $level = 'AA', $size = 'normal') {
        try {
            $ratio = $this->get_contrast_ratio($foreground, $background);
            if (is_wp_error($ratio)) {
                throw new \Exception($ratio->get_error_message());
            }

            $min_ratio = $this->get_minimum_contrast_ratio($level, $size);
            return $ratio >= $min_ratio;
        } catch (\Exception $e) {
            return new WP_Error(
                'wcag_check_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Check if color is readable on background
     *
     * @param string $color Text color (hex format)
     * @param string $background Background color (hex format)
     * @param array  $options Readability options
     * @return bool|WP_Error True if readable, error if not
     */
    public function is_readable($color, $background, $options = []) {
        $defaults = [
            'level' => 'AA',
            'size' => 'normal',
            'min_brightness_diff' => 125,
            'min_color_diff' => 500
        ];
        $options = wp_parse_args($options, $defaults);

        try {
            // Check WCAG contrast
            $meets_wcag = $this->meets_wcag_contrast($color, $background, $options['level'], $options['size']);
            if (is_wp_error($meets_wcag)) {
                throw new \Exception($meets_wcag->get_error_message());
            }

            // Check brightness difference
            $brightness1 = $this->color_metrics->calculate_brightness($color);
            $brightness2 = $this->color_metrics->calculate_brightness($background);
            if (is_wp_error($brightness1) || is_wp_error($brightness2)) {
                throw new \Exception('Failed to calculate brightness');
            }
            $brightness_diff = abs($brightness1 - $brightness2) * 255;

            // Check color difference
            $color_diff = $this->color_metrics->calculate_color_difference($color, $background);
            if (is_wp_error($color_diff)) {
                throw new \Exception($color_diff->get_error_message());
            }

            return $meets_wcag &&
                   $brightness_diff >= $options['min_brightness_diff'] &&
                   $color_diff >= $options['min_color_diff'];
        } catch (\Exception $e) {
            return new WP_Error(
                'readability_check_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Get color combinations that meet contrast requirements
     *
     * @param string $base_color Base color to find combinations for (hex format)
     * @param array  $options Search options
     * @return array|WP_Error Array of valid color combinations or error
     */
    public function get_accessible_combinations($base_color, $options = []) {
        $defaults = [
            'level' => 'AA',
            'size' => 'normal',
            'step' => 5,
            'max_results' => 10
        ];
        $options = wp_parse_args($options, $defaults);

        try {
            $combinations = [];
            $min_ratio = $this->get_minimum_contrast_ratio($options['level'], $options['size']);

            // Generate variations
            for ($h = 0; $h < 360; $h += $options['step']) {
                for ($s = 0; $s <= 100; $s += $options['step']) {
                    for ($l = 0; $l <= 100; $l += $options['step']) {
                        $test_color = $this->color_util->hsl_to_hex([
                            'h' => $h,
                            's' => $s / 100,
                            'l' => $l / 100
                        ]);

                        $ratio = $this->get_contrast_ratio($base_color, $test_color);
                        if (is_wp_error($ratio)) {
                            continue;
                        }

                        if ($ratio >= $min_ratio) {
                            $combinations[] = [
                                'color' => $test_color,
                                'contrast_ratio' => $ratio
                            ];

                            if (count($combinations) >= $options['max_results']) {
                                break 3;
                            }
                        }
                    }
                }
            }

            // Sort by contrast ratio
            usort($combinations, function($a, $b) {
                return $b['contrast_ratio'] <=> $a['contrast_ratio'];
            });

            return $combinations;
        } catch (\Exception $e) {
            return new WP_Error(
                'accessible_combinations_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Check if palette is colorblind friendly
     *
     * @param array $colors Array of colors (hex format)
     * @param array $types Types of colorblindness to check
     * @return array|WP_Error Results for each type or error
     */
    public function check_colorblind_friendly($colors, $types = ['protanopia', 'deuteranopia', 'tritanopia']) {
        try {
            $results = [];

            foreach ($types as $type) {
                $simulated_colors = $this->simulate_colorblind_vision($colors, $type);
                if (is_wp_error($simulated_colors)) {
                    throw new \Exception($simulated_colors->get_error_message());
                }

                $distinguishable = true;
                for ($i = 0; $i < count($simulated_colors); $i++) {
                    for ($j = $i + 1; $j < count($simulated_colors); $j++) {
                        $diff = $this->color_metrics->calculate_color_difference(
                            $simulated_colors[$i],
                            $simulated_colors[$j]
                        );
                        if (is_wp_error($diff) || $diff < 20) { // Threshold for distinguishability
                            $distinguishable = false;
                            break 2;
                        }
                    }
                }

                $results[$type] = $distinguishable;
            }

            return $results;
        } catch (\Exception $e) {
            return new WP_Error(
                'colorblind_check_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Simulate how colors appear with different types of colorblindness
     *
     * @param array  $colors Array of colors (hex format)
     * @param string $type Type of colorblindness
     * @return array|WP_Error Simulated colors or error
     */
    public function simulate_colorblind_vision($colors, $type) {
        try {
            $matrices = $this->get_colorblind_matrices();
            if (!isset($matrices[$type])) {
                throw new \Exception(__('Invalid colorblindness type', 'gl-color-palette-generator'));
            }

            $matrix = $matrices[$type];
            $simulated = [];

            foreach ($colors as $color) {
                $rgb = $this->color_util->hex_to_rgb($color);
                
                // Apply transformation matrix
                $new_r = $rgb['r'] * $matrix[0][0] + $rgb['g'] * $matrix[0][1] + $rgb['b'] * $matrix[0][2];
                $new_g = $rgb['r'] * $matrix[1][0] + $rgb['g'] * $matrix[1][1] + $rgb['b'] * $matrix[1][2];
                $new_b = $rgb['r'] * $matrix[2][0] + $rgb['g'] * $matrix[2][1] + $rgb['b'] * $matrix[2][2];

                // Clamp values
                $new_r = max(0, min(255, $new_r));
                $new_g = max(0, min(255, $new_g));
                $new_b = max(0, min(255, $new_b));

                $simulated[] = $this->color_util->rgb_to_hex([
                    'r' => round($new_r),
                    'g' => round($new_g),
                    'b' => round($new_b)
                ]);
            }

            return $simulated;
        } catch (\Exception $e) {
            return new WP_Error(
                'simulation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Get color luminance
     *
     * @param string $color Color in hex format
     * @return float|WP_Error Relative luminance value or error
     */
    public function get_luminance($color) {
        return $this->color_util->get_relative_luminance($color);
    }

    /**
     * Suggest accessible alternatives for a color
     *
     * @param string $color Color to find alternatives for (hex format)
     * @param string $background Background color (hex format)
     * @param array  $options Search options
     * @return array|WP_Error Array of alternative colors or error
     */
    public function suggest_accessible_alternatives($color, $background, $options = []) {
        $defaults = [
            'level' => 'AA',
            'size' => 'normal',
            'preserve_hue' => true,
            'max_results' => 5
        ];
        $options = wp_parse_args($options, $defaults);

        try {
            $hsl = $this->color_util->hex_to_hsl($color);
            $alternatives = [];
            $min_ratio = $this->get_minimum_contrast_ratio($options['level'], $options['size']);

            // Try adjusting lightness first
            for ($l = 0; $l <= 100; $l += 5) {
                $test_color = $this->color_util->hsl_to_hex([
                    'h' => $options['preserve_hue'] ? $hsl['h'] : ($hsl['h'] + $l) % 360,
                    's' => $hsl['s'],
                    'l' => $l / 100
                ]);

                $ratio = $this->get_contrast_ratio($test_color, $background);
                if (is_wp_error($ratio)) {
                    continue;
                }

                if ($ratio >= $min_ratio) {
                    $alternatives[] = [
                        'color' => $test_color,
                        'contrast_ratio' => $ratio
                    ];

                    if (count($alternatives) >= $options['max_results']) {
                        break;
                    }
                }
            }

            // If we need more results, try adjusting saturation too
            if (count($alternatives) < $options['max_results']) {
                for ($s = 0; $s <= 100; $s += 10) {
                    for ($l = 0; $l <= 100; $l += 10) {
                        $test_color = $this->color_util->hsl_to_hex([
                            'h' => $options['preserve_hue'] ? $hsl['h'] : ($hsl['h'] + $l) % 360,
                            's' => $s / 100,
                            'l' => $l / 100
                        ]);

                        $ratio = $this->get_contrast_ratio($test_color, $background);
                        if (is_wp_error($ratio)) {
                            continue;
                        }

                        if ($ratio >= $min_ratio) {
                            $alternatives[] = [
                                'color' => $test_color,
                                'contrast_ratio' => $ratio
                            ];

                            if (count($alternatives) >= $options['max_results']) {
                                break 2;
                            }
                        }
                    }
                }
            }

            // Sort by contrast ratio
            usort($alternatives, function($a, $b) {
                return $b['contrast_ratio'] <=> $a['contrast_ratio'];
            });

            return $alternatives;
        } catch (\Exception $e) {
            return new WP_Error(
                'alternatives_suggestion_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Check if colors are distinguishable
     *
     * @param array $colors Array of colors to check (hex format)
     * @param array $options Check options
     * @return bool|WP_Error True if distinguishable, error if not
     */
    public function are_colors_distinguishable($colors, $options = []) {
        $defaults = [
            'min_difference' => 20,
            'check_colorblind' => true
        ];
        $options = wp_parse_args($options, $defaults);

        try {
            // Check regular color difference
            for ($i = 0; $i < count($colors); $i++) {
                for ($j = $i + 1; $j < count($colors); $j++) {
                    $diff = $this->color_metrics->calculate_color_difference($colors[$i], $colors[$j]);
                    if (is_wp_error($diff) || $diff < $options['min_difference']) {
                        return false;
                    }
                }
            }

            // Check colorblind simulation if required
            if ($options['check_colorblind']) {
                $colorblind_check = $this->check_colorblind_friendly($colors);
                if (is_wp_error($colorblind_check)) {
                    throw new \Exception($colorblind_check->get_error_message());
                }

                return !in_array(false, $colorblind_check, true);
            }

            return true;
        } catch (\Exception $e) {
            return new WP_Error(
                'distinguishable_check_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Get minimum contrast ratio for WCAG level and text size
     *
     * @param string $level WCAG level ('AA' or 'AAA')
     * @param string $size Text size ('normal' or 'large')
     * @return float Minimum contrast ratio
     */
    private function get_minimum_contrast_ratio($level, $size) {
        $ratios = [
            'AA' => [
                'normal' => 4.5,
                'large' => 3
            ],
            'AAA' => [
                'normal' => 7,
                'large' => 4.5
            ]
        ];

        return $ratios[$level][$size] ?? 4.5;
    }

    /**
     * Get colorblind simulation matrices
     *
     * @return array Transformation matrices for different types
     */
    private function get_colorblind_matrices() {
        return [
            'protanopia' => [
                [0.567, 0.433, 0],
                [0.558, 0.442, 0],
                [0, 0.242, 0.758]
            ],
            'deuteranopia' => [
                [0.625, 0.375, 0],
                [0.7, 0.3, 0],
                [0, 0.3, 0.7]
            ],
            'tritanopia' => [
                [0.95, 0.05, 0],
                [0, 0.433, 0.567],
                [0, 0.475, 0.525]
            ]
        ];
    }
}
