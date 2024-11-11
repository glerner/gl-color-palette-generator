<?php

class PaletteValidator {
    private $color_analyzer;
    private $accessibility_checker;
    private $settings;

    // Validation rules
    private const VALIDATION_RULES = [
        'contrast' => [
            'min_ratio' => 4.5,
            'preferred_ratio' => 7.0,
            'large_text_ratio' => 3.0
        ],
        'distinction' => [
            'min_difference' => 20,
            'preferred_difference' => 40
        ],
        'harmony' => [
            'max_hue_variance' => 120,
            'saturation_range' => 30,
            'lightness_range' => 40
        ],
        'colorblind' => [
            'min_distinction' => 15,
            'safe_types' => ['protanopia', 'deuteranopia', 'tritanopia']
        ],
        'web_safe' => [
            'validate' => true,
            'fallback_generation' => true
        ]
    ];

    public function __construct() {
        $this->color_analyzer = new ColorAnalyzer();
        $this->accessibility_checker = new AccessibilityChecker();
        $this->settings = new SettingsManager();
    }

    /**
     * Validate entire palette
     */
    public function validate_palette($palette, $context = []) {
        $validation_results = [
            'is_valid' => true,
            'contrast' => $this->validate_contrast($palette, $context),
            'distinction' => $this->validate_distinction($palette),
            'harmony' => $this->validate_harmony($palette),
            'accessibility' => $this->validate_accessibility($palette, $context),
            'colorblind_safe' => $this->validate_colorblind_safety($palette),
            'web_safe' => $this->validate_web_safety($palette),
            'brand_compliance' => $this->validate_brand_compliance($palette, $context),
            'technical' => $this->validate_technical_requirements($palette, $context),
            'warnings' => [],
            'suggestions' => []
        ];

        // Check if any major validation failed
        foreach ($validation_results as $key => $result) {
            if (is_array($result) && isset($result['is_valid']) && !$result['is_valid']) {
                $validation_results['is_valid'] = false;
                break;
            }
        }

        // Generate improvement suggestions
        if (!$validation_results['is_valid']) {
            $validation_results['suggestions'] = $this->generate_improvement_suggestions(
                $validation_results,
                $palette,
                $context
            );
        }

        return $validation_results;
    }

    /**
     * Validate contrast ratios
     */
    private function validate_contrast($palette, $context) {
        $results = [
            'is_valid' => true,
            'issues' => [],
            'measurements' => []
        ];

        $background = $context['background'] ?? '#FFFFFF';

        foreach ($palette as $key => $color) {
            if (is_string($color)) {  // Skip if not a color hex
                $contrast_ratio = $this->accessibility_checker->calculate_contrast_ratio(
                    $color,
                    $background
                );

                $results['measurements'][$key] = [
                    'ratio' => $contrast_ratio,
                    'meets_aa' => $contrast_ratio >= self::VALIDATION_RULES['contrast']['min_ratio'],
                    'meets_aaa' => $contrast_ratio >= self::VALIDATION_RULES['contrast']['preferred_ratio']
                ];

                if ($contrast_ratio < self::VALIDATION_RULES['contrast']['min_ratio']) {
                    $results['is_valid'] = false;
                    $results['issues'][] = [
                        'color' => $key,
                        'ratio' => $contrast_ratio,
                        'required' => self::VALIDATION_RULES['contrast']['min_ratio']
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Validate color distinction
     */
    private function validate_distinction($palette) {
        $results = [
            'is_valid' => true,
            'issues' => [],
            'measurements' => []
        ];

        $colors = array_filter($palette, 'is_string');  // Get only color values

        foreach ($colors as $key1 => $color1) {
            foreach ($colors as $key2 => $color2) {
                if ($key1 !== $key2) {
                    $difference = $this->color_analyzer->calculate_color_difference(
                        $color1,
                        $color2
                    );

                    $results['measurements']["{$key1}_vs_{$key2}"] = $difference;

                    if ($difference < self::VALIDATION_RULES['distinction']['min_difference']) {
                        $results['is_valid'] = false;
                        $results['issues'][] = [
                            'color1' => $key1,
                            'color2' => $key2,
                            'difference' => $difference,
                            'required' => self::VALIDATION_RULES['distinction']['min_difference']
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Validate color harmony
     */
    private function validate_harmony($palette) {
        $results = [
            'is_valid' => true,
            'issues' => [],
            'measurements' => []
        ];

        $colors = array_filter($palette, 'is_string');
        $base_hsl = $this->color_analyzer->hex_to_hsl($colors['primary']);

        foreach ($colors as $key => $color) {
            $hsl = $this->color_analyzer->hex_to_hsl($color);

            // Check hue variance
            $hue_diff = abs($hsl[0] - $base_hsl[0]);
            $hue_diff = min($hue_diff, 360 - $hue_diff);

            // Check saturation range
            $sat_diff = abs($hsl[1] - $base_hsl[1]);

            // Check lightness range
            $light_diff = abs($hsl[2] - $base_hsl[2]);

            $results['measurements'][$key] = [
                'hue_variance' => $hue_diff,
                'saturation_diff' => $sat_diff,
                'lightness_diff' => $light_diff
            ];

            if ($hue_diff > self::VALIDATION_RULES['harmony']['max_hue_variance'] ||
                $sat_diff > self::VALIDATION_RULES['harmony']['saturation_range'] ||
                $light_diff > self::VALIDATION_RULES['harmony']['lightness_range']) {

                $results['is_valid'] = false;
                $results['issues'][] = [
                    'color' => $key,
                    'measurements' => $results['measurements'][$key]
                ];
            }
        }

        return $results;
    }

    /**
     * Validate colorblind safety
     */
    private function validate_colorblind_safety($palette) {
        $results = [
            'is_valid' => true,
            'issues' => [],
            'simulations' => []
        ];

        foreach (self::VALIDATION_RULES['colorblind']['safe_types'] as $type) {
            $simulated_colors = [];

            foreach ($palette as $key => $color) {
                if (is_string($color)) {
                    $simulated_colors[$key] = $this->color_analyzer->simulate_color_blindness(
                        $color,
                        $type
                    );
                }
            }

            $results['simulations'][$type] = $simulated_colors;

            // Check distinctions between simulated colors
            foreach ($simulated_colors as $key1 => $color1) {
                foreach ($simulated_colors as $key2 => $color2) {
                    if ($key1 !== $key2) {
                        $difference = $this->color_analyzer->calculate_color_difference(
                            $color1,
                            $color2
                        );

                        if ($difference < self::VALIDATION_RULES['colorblind']['min_distinction']) {
                            $results['is_valid'] = false;
                            $results['issues'][] = [
                                'type' => $type,
                                'color1' => $key1,
                                'color2' => $key2,
                                'difference' => $difference
                            ];
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Generate improvement suggestions
     */
    private function generate_improvement_suggestions($validation_results, $palette, $context) {
        $suggestions = [];

        // Contrast improvements
        if (!$validation_results['contrast']['is_valid']) {
            $suggestions['contrast'] = $this->suggest_contrast_improvements(
                $validation_results['contrast']['issues'],
                $palette,
                $context
            );
        }

        // Distinction improvements
        if (!$validation_results['distinction']['is_valid']) {
            $suggestions['distinction'] = $this->suggest_distinction_improvements(
                $validation_results['distinction']['issues'],
                $palette
            );
        }

        // Harmony improvements
        if (!$validation_results['harmony']['is_valid']) {
            $suggestions['harmony'] = $this->suggest_harmony_improvements(
                $validation_results['harmony']['issues'],
                $palette
            );
        }

        // Colorblind safety improvements
        if (!$validation_results['colorblind_safe']['is_valid']) {
            $suggestions['colorblind'] = $this->suggest_colorblind_improvements(
                $validation_results['colorblind_safe']['issues'],
                $palette
            );
        }

        return $suggestions;
    }

    /**
     * Suggest contrast improvements
     */
    private function suggest_contrast_improvements($issues, $palette, $context) {
        $suggestions = [];

        foreach ($issues as $issue) {
            $color = $palette[$issue['color']];
            $background = $context['background'] ?? '#FFFFFF';

            $suggestions[] = [
                'color' => $issue['color'],
                'current_ratio' => $issue['ratio'],
                'target_ratio' => $issue['required'],
                'suggested_adjustments' => [
                    'lighter' => $this->adjust_for_better_contrast($color, $background, 'lighter'),
                    'darker' => $this->adjust_for_better_contrast($color, $background, 'darker'),
                    'saturated' => $this->adjust_for_better_contrast($color, $background, 'saturated')
                ]
            ];
        }

        return $suggestions;
    }

    /**
     * Utility method to adjust color for better contrast
     */
    private function adjust_for_better_contrast($color, $background, $direction) {
        $lab = $this->color_analyzer->hex_to_lab($color);

        switch ($direction) {
            case 'lighter':
                $lab[0] = min(100, $lab[0] + 10);
                break;
            case 'darker':
                $lab[0] = max(0, $lab[0] - 10);
                break;
            case 'saturated':
                $lab[1] *= 1.2;
                $lab[2] *= 1.2;
                break;
        }

        return $this->color_analyzer->lab_to_hex($lab);
    }
} 
