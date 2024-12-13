/**
 * Color Wheel Class
 *
 * Implements a comprehensive color wheel system for managing color relationships
 * and generating harmonious color combinations. Provides tools for calculating
 * complementary colors, analogous colors, and other color harmony patterns.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Wheel_Interface;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Scheme_Types;
use GL_Color_Palette_Generator\Color_Management\Color_Calculator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Settings\Settings_Manager;

/**
 * Color Wheel Class
 *
 * @since 1.0.0
 */
class Color_Wheel implements Color_Wheel_Interface {
    /**
     * Color calculator instance
     *
     * @var Color_Calculator
     * @since 1.0.0
     */
    private Color_Calculator $color_calculator;

    /**
     * Color utility instance
     *
     * @var Color_Utility
     * @since 1.0.0
     */
    private Color_Utility $color_utility;

    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     * @since 1.0.0
     */
    private Settings_Manager $settings;

    /**
     * Color wheel configuration
     *
     * @var array
     * @since 1.0.0
     */
    private const WHEEL_CONFIG = [
        'segments' => 12,
        'primary_colors' => [
            'red' => 0,
            'yellow' => 120,
            'blue' => 240
        ],
        'secondary_colors' => [
            'orange' => 60,
            'green' => 180,
            'purple' => 300
        ],
        'tertiary_colors' => [
            'red-orange' => 30,
            'yellow-orange' => 90,
            'yellow-green' => 150,
            'blue-green' => 210,
            'blue-purple' => 270,
            'red-purple' => 330
        ]
    ];

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct(
        Color_Calculator $color_calculator,
        Color_Utility $color_utility,
        Settings_Manager $settings
    ) {
        $this->color_calculator = $color_calculator;
        $this->color_utility = $color_utility;
        $this->settings = $settings;
    }

    /**
     * Calculate color harmonies
     *
     * @param string $base_color
     * @param string $harmony_type
     * @param array  $options
     *
     * @return array
     * @since 1.0.0
     */
    public function calculate_harmonies(string $base_color, string $harmony_type, array $options = []): array {
        $hsl = $this->color_utility->hex_to_hsl($base_color);
        $harmonies = [];

        switch ($harmony_type) {
            case 'complementary':
                $harmonies = $this->calculate_complementary($hsl, $options);
                break;
            case 'analogous':
                $harmonies = $this->calculate_analogous($hsl, $options);
                break;
            case 'triadic':
                $harmonies = $this->calculate_triadic($hsl, $options);
                break;
            case 'tetradic':
                $harmonies = $this->calculate_tetradic($hsl, $options);
                break;
            case 'compound':
                $harmonies = $this->calculate_compound($hsl, $options);
                break;
        }

        return $this->apply_harmony_variations($harmonies, $options);
    }

    /**
     * Calculate color relationships
     *
     * @param string $color
     *
     * @return array
     * @since 1.0.0
     */
    public function calculate_relationships(string $color): array {
        return [
            'complementary' => $this->find_complementary($color),
            'analogous' => $this->find_analogous($color),
            'triadic' => $this->find_triadic($color),
            'tetradic' => $this->find_tetradic($color),
            'split_complementary' => $this->find_split_complementary($color),
            'distance_relationships' => $this->calculate_distance_relationships($color)
        ];
    }

    /**
     * Generate dynamic color wheel
     *
     * @param int   $segments
     * @param array $options
     *
     * @return array
     * @since 1.0.0
     */
    public function generate_color_wheel(int $segments = 12, array $options = []): array {
        $wheel = [];
        $segment_angle = 360 / $segments;

        for ($i = 0; $i < $segments; $i++) {
            $hue = $i * $segment_angle;
            $wheel[] = [
                'hue' => $hue,
                'hex' => $this->hue_to_hex($hue),
                'relationships' => $this->calculate_segment_relationships($hue, $segment_angle)
            ];
        }

        return $wheel;
    }

    /**
     * Calculate advanced color variations
     *
     * @param string $base_color
     * @param string $variation_type
     *
     * @return array
     * @since 1.0.0
     */
    private function calculate_variations(string $base_color, string $variation_type): array {
        $hsl = $this->color_utility->hex_to_hsl($base_color);
        $variations = [];

        switch ($variation_type) {
            case 'monochromatic':
                $variations = $this->generate_monochromatic_scale($hsl);
                break;
            case 'brightness':
                $variations = $this->generate_brightness_scale($hsl);
                break;
            case 'saturation':
                $variations = $this->generate_saturation_scale($hsl);
                break;
            case 'temperature':
                $variations = $this->generate_temperature_scale($hsl);
                break;
        }

        return $variations;
    }

    /**
     * Generate color schemes
     *
     * @param string $base_color
     * @param string $scheme_type
     * @param array  $options
     *
     * @return array
     * @since 1.0.0
     */
    public function generate_scheme(string $base_color, string $scheme_type, array $options = []): array {
        $scheme = [
            'base' => $base_color,
            'harmonies' => $this->calculate_harmonies($base_color, $scheme_type),
            'variations' => $this->calculate_variations($base_color, 'monochromatic'),
            'relationships' => $this->calculate_relationships($base_color, 'all'),
            'combinations' => $this->generate_combinations($base_color, $scheme_type)
        ];

        if (!empty($options['include_neutrals'])) {
            $scheme['neutrals'] = $this->generate_neutral_palette($base_color);
        }

        if (!empty($options['include_accents'])) {
            $scheme['accents'] = $this->generate_accent_colors($base_color);
        }

        return $scheme;
    }

    /**
     * Calculate color wheel position
     *
     * @param float $hue
     * @param array $options
     *
     * @return array
     * @since 1.0.0
     */
    private function calculate_wheel_position(float $hue, array $options = []): array {
        $position = [
            'angle' => $hue,
            'radius' => $options['radius'] ?? 1.0,
            'x' => cos(deg2rad($hue)) * ($options['radius'] ?? 1.0),
            'y' => sin(deg2rad($hue)) * ($options['radius'] ?? 1.0)
        ];

        if (!empty($options['include_coordinates'])) {
            $position['coordinates'] = $this->calculate_coordinates($position);
        }

        return $position;
    }

    /**
     * Generate interpolated colors
     *
     * @param string $start_color
     * @param string $end_color
     * @param int    $steps
     *
     * @return array
     * @since 1.0.0
     */
    private function generate_interpolated_colors(string $start_color, string $end_color, int $steps): array {
        $colors = [];
        $start_hsl = $this->color_utility->hex_to_hsl($start_color);
        $end_hsl = $this->color_utility->hex_to_hsl($end_color);

        for ($i = 0; $i <= $steps; $i++) {
            $ratio = $i / $steps;
            $hsl = [
                'h' => $this->interpolate_hue($start_hsl['h'], $end_hsl['h'], $ratio),
                's' => $start_hsl['s'] + ($end_hsl['s'] - $start_hsl['s']) * $ratio,
                'l' => $start_hsl['l'] + ($end_hsl['l'] - $start_hsl['l']) * $ratio
            ];
            $colors[] = $this->color_utility->hsl_to_hex($hsl);
        }

        return $colors;
    }

    /**
     * Calculate color harmony strength
     *
     * @param array $colors
     *
     * @return array
     * @since 1.0.0
     */
    private function calculate_harmony_strength(array $colors): array {
        $strength = [
            'contrast' => $this->calculate_contrast_ratio($colors),
            'balance' => $this->calculate_color_balance($colors),
            'harmony' => $this->calculate_harmony_score($colors),
            'vibrance' => $this->calculate_vibrance_score($colors)
        ];

        return $strength;
    }

    /**
     * Generate color wheel data
     *
     * @param int $resolution
     *
     * @return array
     * @since 1.0.0
     */
    public function generate_wheel_data(int $resolution = 360): array {
        $wheel_data = [
            'primary_colors' => $this->get_primary_colors(),
            'secondary_colors' => $this->get_secondary_colors(),
            'tertiary_colors' => $this->get_tertiary_colors(),
            'color_points' => $this->generate_color_points($resolution)
        ];

        return [
            'wheel_data' => $wheel_data,
            'segments' => $this->generate_wheel_segments($wheel_data),
            'relationships' => $this->analyze_color_relationships($wheel_data),
            'metadata' => $this->generate_wheel_metadata($wheel_data)
        ];
    }

    /**
     * Generate interactive wheel data
     *
     * @param string $selected_color
     *
     * @return array
     * @since 1.0.0
     */
    public function generate_interactive_data(string $selected_color = null): array {
        $wheel_data = $this->generate_wheel_data();

        return [
            'base_wheel' => $wheel_data,
            'interactive_points' => $this->generate_interactive_points(),
            'selection_guides' => $this->generate_selection_guides($selected_color),
            'harmony_indicators' => $this->generate_harmony_indicators($selected_color),
            'interaction_handlers' => $this->get_interaction_handlers()
        ];
    }

    /**
     * Generate color scheme
     *
     * @param string $base_color
     * @param string $scheme_type
     * @param array  $options
     *
     * @return array
     */
    public function generate_scheme(
        string $base_color,
        string $scheme_type,
        array $options = []
    ): array {
        $this->validate_color($base_color);
        $scheme_type = strtolower($scheme_type);

        return match($scheme_type) {
            Scheme_Types::COMPLEMENTARY => $this->generate_complementary_scheme($base_color, $options),
            Scheme_Types::ANALOGOUS => $this->generate_analogous_scheme($base_color, $options),
            Scheme_Types::TRIADIC => $this->generate_triadic_scheme($base_color, $options),
            Scheme_Types::SPLIT_COMPLEMENTARY => $this->generate_split_complementary_scheme($base_color, $options),
            Scheme_Types::TETRADIC => $this->generate_tetradic_scheme($base_color, $options),
            Scheme_Types::SQUARE => $this->generate_square_scheme($base_color, $options),
            default => throw new \InvalidArgumentException("Invalid scheme type: {$scheme_type}")
        };
    }

    /**
     * Validate color value
     *
     * @param string $color Color to validate
     * @throws \InvalidArgumentException If color is invalid
     */
    private function validate_color(string $color): void {
        if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            throw new \InvalidArgumentException(
                'Invalid color format. Must be a 6-digit hex color (e.g., #FF0000)'
            );
        }
    }
}
