<?php
namespace GLColorPalette;

class ColorWheel {
    private $color_calculator;
    private $harmony_analyzer;
    private $conversion_utils;

    / Advanced color wheel configurations
    private const COLOR_WHEEL = [
        'primary_colors' => [
            'red' => ['hue' => 0, 'hex' => '#FF0000'],
            'blue' => ['hue' => 240, 'hex' => '#0000FF'],
            'yellow' => ['hue' => 60, 'hex' => '#FFFF00']
        ],
        'secondary_colors' => [
            'green' => ['hue' => 120, 'hex' => '#00FF00'],
            'orange' => ['hue' => 30, 'hex' => '#FF8000'],
            'purple' => ['hue' => 300, 'hex' => '#800080']
        ],
        'tertiary_colors' => [
            'yellow_green' => ['hue' => 90, 'hex' => '#80FF00'],
            'blue_green' => ['hue' => 180, 'hex' => '#00FF80'],
            'blue_purple' => ['hue' => 270, 'hex' => '#8000FF'],
            'red_purple' => ['hue' => 330, 'hex' => '#FF0080'],
            'red_orange' => ['hue' => 15, 'hex' => '#FF4000'],
            'yellow_orange' => ['hue' => 45, 'hex' => '#FFB000']
        ]
    ];

    / Color harmony patterns
    private const HARMONY_PATTERNS = [
        'complementary' => [
            'angle' => 180,
            'variations' => [
                'split' => ['angles' => [150, 210]],
                'double' => ['pairs' => [[0, 180], [90, 270]]]
            ]
        ],
        'analogous' => [
            'angles' => [30, -30],
            'variations' => [
                'wide' => ['angles' => [45, -45]],
                'narrow' => ['angles' => [15, -15]]
            ]
        ],
        'triadic' => [
            'angles' => [120, 240],
            'variations' => [
                'split' => ['angles' => [120, 150, 210, 240]],
                'compressed' => ['angles' => [90, 180]]
            ]
        ],
        'tetradic' => [
            'rectangle' => ['angles' => [90, 180, 270]],
            'square' => ['angles' => [90, 180, 270]]
        ],
        'compound' => [
            'split_complementary' => ['angles' => [150, 210]],
            'double_split_complementary' => ['angles' => [30, 150, 210, 330]]
        ]
    ];

    /**
     * Calculate color harmonies
     */
    public function calculate_harmonies($base_color, $harmony_type, $options = []) {
        $hsl = $this->conversion_utils->hex_to_hsl($base_color);
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
     */
    public function calculate_relationships($color) {
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
     */
    public function generate_color_wheel($segments = 12, $options = []) {
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
     */
    private function calculate_variations($base_color, $variation_type) {
        $hsl = $this->conversion_utils->hex_to_hsl($base_color);
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
     */
    public function generate_scheme($base_color, $scheme_type, $options = []) {
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
     */
    private function calculate_wheel_position($hue, $options = []) {
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
     */
    private function generate_interpolated_colors($start_color, $end_color, $steps) {
        $colors = [];
        $start_hsl = $this->conversion_utils->hex_to_hsl($start_color);
        $end_hsl = $this->conversion_utils->hex_to_hsl($end_color);

        for ($i = 0; $i <= $steps; $i++) {
            $ratio = $i / $steps;
            $hsl = [
                'h' => $this->interpolate_hue($start_hsl['h'], $end_hsl['h'], $ratio),
                's' => $start_hsl['s'] + ($end_hsl['s'] - $start_hsl['s']) * $ratio,
                'l' => $start_hsl['l'] + ($end_hsl['l'] - $start_hsl['l']) * $ratio
            ];
            $colors[] = $this->conversion_utils->hsl_to_hex($hsl);
        }

        return $colors;
    }

    /**
     * Calculate color harmony strength
     */
    private function calculate_harmony_strength($colors) {
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
     */
    public function generate_wheel_data($resolution = 360) {
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
     */
    public function generate_interactive_data($selected_color = null) {
        $wheel_data = $this->generate_wheel_data();

        return [
            'base_wheel' => $wheel_data,
            'interactive_points' => $this->generate_interactive_points(),
            'selection_guides' => $this->generate_selection_guides($selected_color),
            'harmony_indicators' => $this->generate_harmony_indicators($selected_color),
            'interaction_handlers' => $this->get_interaction_handlers()
        ];
    }
}
