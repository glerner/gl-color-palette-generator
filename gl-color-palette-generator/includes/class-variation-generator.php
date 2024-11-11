<?php

class VariationGenerator {
    private $color_analyzer;
    private $settings;

    // Variation types
    private const VARIATION_TYPES = [
        'tints' => ['steps' => 5, 'intensity' => 0.1],
        'shades' => ['steps' => 5, 'intensity' => 0.1],
        'tones' => ['steps' => 5, 'intensity' => 0.1],
        'saturated' => ['steps' => 3, 'intensity' => 0.15],
        'desaturated' => ['steps' => 3, 'intensity' => 0.15],
        'temperature' => ['warm' => 2, 'cool' => 2],
        'neutrals' => ['steps' => 3, 'intensity' => 0.2],
        'accents' => ['bright' => 2, 'muted' => 2]
    ];

    public function __construct() {
        $this->color_analyzer = new ColorAnalyzer();
        $this->settings = new SettingsManager();
    }

    /**
     * Generate comprehensive color variations
     */
    public function generate_variations($base_color, $options = []) {
        $lab = $this->color_analyzer->hex_to_lab($base_color);
        $variations = [
            'base' => $base_color,
            'tints' => $this->generate_tints($lab, $options),
            'shades' => $this->generate_shades($lab, $options),
            'tones' => $this->generate_tones($lab, $options),
            'saturated' => $this->generate_saturated($lab, $options),
            'desaturated' => $this->generate_desaturated($lab, $options),
            'temperature' => $this->generate_temperature_variations($lab, $options),
            'neutrals' => $this->generate_neutrals($lab, $options),
            'accents' => $this->generate_accents($lab, $options)
        ];

        // Add semantic variations if requested
        if (!empty($options['semantic'])) {
            $variations['semantic'] = $this->generate_semantic_variations($base_color);
        }

        // Add custom variations if specified
        if (!empty($options['custom'])) {
            $variations['custom'] = $this->generate_custom_variations($base_color, $options['custom']);
        }

        return $variations;
    }

    /**
     * Generate tints (lighter variations)
     */
    private function generate_tints($lab, $options) {
        $steps = $options['tint_steps'] ?? self::VARIATION_TYPES['tints']['steps'];
        $intensity = $options['tint_intensity'] ?? self::VARIATION_TYPES['tints']['intensity'];
        $tints = [];

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                min(100, $lab[0] + ($i * $intensity * 100)),
                $lab[1] * (1 - ($i * $intensity * 0.5)),
                $lab[2] * (1 - ($i * $intensity * 0.5))
            ];

            $tints[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $tints;
    }

    /**
     * Generate shades (darker variations)
     */
    private function generate_shades($lab, $options) {
        $steps = $options['shade_steps'] ?? self::VARIATION_TYPES['shades']['steps'];
        $intensity = $options['shade_intensity'] ?? self::VARIATION_TYPES['shades']['intensity'];
        $shades = [];

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                max(0, $lab[0] - ($i * $intensity * 100)),
                $lab[1] * (1 - ($i * $intensity * 0.3)),
                $lab[2] * (1 - ($i * $intensity * 0.3))
            ];

            $shades[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $shades;
    }

    /**
     * Generate tones (saturation variations)
     */
    private function generate_tones($lab, $options) {
        $steps = $options['tone_steps'] ?? self::VARIATION_TYPES['tones']['steps'];
        $intensity = $options['tone_intensity'] ?? self::VARIATION_TYPES['tones']['intensity'];
        $tones = [];

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                $lab[0],
                $lab[1] * (1 - ($i * $intensity)),
                $lab[2] * (1 - ($i * $intensity))
            ];

            $tones[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $tones;
    }

    /**
     * Generate saturated variations
     */
    private function generate_saturated($lab, $options) {
        $steps = $options['saturated_steps'] ?? self::VARIATION_TYPES['saturated']['steps'];
        $intensity = $options['saturated_intensity'] ?? self::VARIATION_TYPES['saturated']['intensity'];
        $saturated = [];

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                $lab[0],
                $lab[1] * (1 + ($i * $intensity)),
                $lab[2] * (1 + ($i * $intensity))
            ];

            $saturated[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $saturated;
    }

    /**
     * Generate temperature variations
     */
    private function generate_temperature_variations($lab, $options) {
        $warm_steps = $options['warm_steps'] ?? self::VARIATION_TYPES['temperature']['warm'];
        $cool_steps = $options['cool_steps'] ?? self::VARIATION_TYPES['temperature']['cool'];

        return [
            'warm' => $this->generate_warm_variations($lab, $warm_steps),
            'cool' => $this->generate_cool_variations($lab, $cool_steps)
        ];
    }

    /**
     * Generate warm variations
     */
    private function generate_warm_variations($lab, $steps) {
        $warm = [];
        $warm_adjustment = [2, 5, -2]; // Adjust Lab values for warmer appearance

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                $lab[0] + ($warm_adjustment[0] * $i),
                $lab[1] + ($warm_adjustment[1] * $i),
                $lab[2] + ($warm_adjustment[2] * $i)
            ];

            $warm[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $warm;
    }

    /**
     * Generate cool variations
     */
    private function generate_cool_variations($lab, $steps) {
        $cool = [];
        $cool_adjustment = [2, -5, 2]; // Adjust Lab values for cooler appearance

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                $lab[0] + ($cool_adjustment[0] * $i),
                $lab[1] + ($cool_adjustment[1] * $i),
                $lab[2] + ($cool_adjustment[2] * $i)
            ];

            $cool[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $cool;
    }

    /**
     * Generate neutral variations
     */
    private function generate_neutrals($lab, $options) {
        $steps = $options['neutral_steps'] ?? self::VARIATION_TYPES['neutrals']['steps'];
        $intensity = $options['neutral_intensity'] ?? self::VARIATION_TYPES['neutrals']['intensity'];
        $neutrals = [];

        for ($i = 1; $i <= $steps; $i++) {
            $new_lab = [
                $lab[0],
                $lab[1] * (1 - ($i * $intensity * 1.5)),
                $lab[2] * (1 - ($i * $intensity * 1.5))
            ];

            $neutrals[$i * 100] = $this->color_analyzer->lab_to_hex($new_lab);
        }

        return $neutrals;
    }

    /**
     * Generate accent variations
     */
    private function generate_accents($lab, $options) {
        return [
            'bright' => $this->generate_bright_accents($lab, $options),
            'muted' => $this->generate_muted_accents($lab, $options)
        ];
    }

    /**
     * Generate semantic variations
     */
    private function generate_semantic_variations($base_color) {
        return [
            'success' => $this->adjust_for_semantic($base_color, 'success'),
            'warning' => $this->adjust_for_semantic($base_color, 'warning'),
            'error' => $this->adjust_for_semantic($base_color, 'error'),
            'info' => $this->adjust_for_semantic($base_color, 'info'),
            'disabled' => $this->adjust_for_semantic($base_color, 'disabled')
        ];
    }

    /**
     * Generate custom variations
     */
    private function generate_custom_variations($base_color, $custom_options) {
        $custom = [];

        foreach ($custom_options as $name => $adjustments) {
            $custom[$name] = $this->apply_custom_adjustments($base_color, $adjustments);
        }

        return $custom;
    }

    /**
     * Adjust color for semantic meaning
     */
    private function adjust_for_semantic($color, $type) {
        $lab = $this->color_analyzer->hex_to_lab($color);

        switch ($type) {
            case 'success':
                return $this->shift_towards_hue($lab, 120, 0.7); // Shift towards green
            case 'warning':
                return $this->shift_towards_hue($lab, 45, 0.7);  // Shift towards orange
            case 'error':
                return $this->shift_towards_hue($lab, 0, 0.7);   // Shift towards red
            case 'info':
                return $this->shift_towards_hue($lab, 200, 0.7); // Shift towards blue
            case 'disabled':
                return $this->desaturate_and_lighten($lab, 0.5);
            default:
                return $color;
        }
    }

    /**
     * Utility methods for color adjustments
     */
    private function shift_towards_hue($lab, $target_hue, $strength) {
        $current_hue = $this->color_analyzer->lab_to_hue($lab);
        $new_hue = $current_hue + ($target_hue - $current_hue) * $strength;
        $new_lab = [
            $lab[0],
            $lab[1],
            $lab[2]
        ];
        $new_lab[0] = $new_hue;
        return $this->color_analyzer->lab_to_hex($new_lab);
    }

    private function desaturate_and_lighten($lab, $amount) {
        $new_lab = [
            $lab[0],
            $lab[1] * (1 - $amount),
            $lab[2] * (1 - $amount)
        ];
        return $this->color_analyzer->lab_to_hex($new_lab);
    }
} 
