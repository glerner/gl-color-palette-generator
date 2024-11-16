<?php
namespace GLColorPalette;

class ColorHarmonization {
    private $color_analyzer;
    private $harmony_calculator;
    private $context_evaluator;

    // Advanced harmony configurations
    private const HARMONY_RULES = [
        'classical_harmonies' => [
            'complementary' => [
                'primary_rules' => [
                    'hue_difference' => 180,
                    'variations' => [
                        'split' => [
                            'angles' => [150, 210],
                            'balance_ratio' => ['primary' => 0.6, 'splits' => 0.2],
                            'saturation_rules' => [
                                'primary' => ['range' => '60-100%', 'optimal' => '80%'],
                                'secondary' => ['range' => '40-80%', 'optimal' => '60%']
                            ]
                        ],
                        'double' => [
                            'angle_pairs' => [[0, 180], [90, 270]],
                            'balance_ratio' => ['primary_pair' => 0.5, 'secondary_pair' => 0.5],
                            'intensity_distribution' => ['dominant' => 0.4, 'supporting' => 0.3]
                        ]
                    ],
                    'contrast_requirements' => [
                        'minimum' => 4.5,
                        'optimal' => 7.0,
                        'maximum' => 12.0
                    ]
                ],
                'contextual_adjustments' => [
                    'digital' => [
                        'screen_type' => [
                            'OLED' => ['saturation' => '-10%', 'brightness' => '+5%'],
                            'LCD' => ['saturation' => 'standard', 'brightness' => '-5%']
                        ],
                        'viewing_distance' => [
                            'mobile' => ['contrast' => '+10%', 'size_ratio' => 1.2],
                            'desktop' => ['contrast' => 'standard', 'size_ratio' => 1.0]
                        ]
                    ],
                    'print' => [
                        'paper_type' => [
                            'coated' => ['saturation' => '+5%', 'brightness' => 'standard'],
                            'uncoated' => ['saturation' => '+15%', 'brightness' => '+10%']
                        ]
                    ]
                ]
            ],

            'analogous' => [
                'primary_rules' => [
                    'hue_range' => [30, 45],
                    'variations' => [
                        'tight' => [
                            'angle_range' => [15, 25],
                            'saturation_steps' => ['primary' => 100, 'secondary' => 85, 'tertiary' => 70],
                            'brightness_progression' => ['start' => 100, 'step' => -10]
                        ],
                        'wide' => [
                            'angle_range' => [40, 60],
                            'saturation_pattern' => ['high' => 90, 'medium' => 75, 'low' => 60],
                            'brightness_variation' => ['range' => 30, 'steps' => 3]
                        ]
                    ]
                ]
            ]
        ],

        'advanced_harmonies' => [
            'golden_ratio' => [
                'hue_progression' => [
                    'angle' => 137.5,
                    'iterations' => 5,
                    'saturation_decay' => 0.618,
                    'brightness_progression' => ['start' => 95, 'ratio' => 0.618]
                ]
            ],
            'tetratic' => [
                'rectangle' => [
                    'angle_pairs' => [[0, 180], [60, 240]],
                    'weight_distribution' => ['primary' => 0.4, 'secondary' => 0.3, 'accents' => 0.15]
                ],
                'square' => [
                    'angles' => [0, 90, 180, 270],
                    'balance_rules' => ['equal_weight' => true, 'rotation_allowed' => true]
                ]
            ]
        ],

        'dynamic_harmonies' => [
            'mood_based' => [
                'energetic' => [
                    'primary_hue_range' => [0, 60],
                    'contrast_level' => 'high',
                    'saturation_rules' => ['minimum' => 70, 'optimal' => 85],
                    'rhythm_pattern' => ['dynamic', 'progressive']
                ],
                'calming' => [
                    'primary_hue_range' => [180, 270],
                    'contrast_level' => 'low',
                    'saturation_rules' => ['maximum' => 60, 'optimal' => 45],
                    'rhythm_pattern' => ['steady', 'gradual']
                ]
            ],
            'seasonal' => [
                'spring' => [
                    'hue_range' => [60, 150],
                    'saturation_profile' => ['fresh', 'bright'],
                    'contrast_pattern' => ['medium', 'balanced']
                ],
                'autumn' => [
                    'hue_range' => [20, 60],
                    'saturation_profile' => ['rich', 'warm'],
                    'contrast_pattern' => ['deep', 'muted']
                ]
            ]
        ]
    ];

    /**
     * Generate harmonious color combination
     */
    public function generate_harmony($base_color, $harmony_type, $context = []) {
        $harmony_rules = $this->get_harmony_rules($harmony_type);
        if (!$harmony_rules) return null;

        $colors = $this->calculate_harmony_colors($base_color, $harmony_rules);
        $adjusted_colors = $this->apply_contextual_adjustments($colors, $context);

        return [
            'colors' => $adjusted_colors,
            'relationships' => $this->analyze_color_relationships($adjusted_colors),
            'balance_metrics' => $this->calculate_balance_metrics($adjusted_colors),
            'application_guidelines' => $this->generate_application_guidelines($adjusted_colors, $context)
        ];
    }

    /**
     * Validate color harmony
     */
    public function validate_harmony($colors, $harmony_type, $context = []) {
        $validation = [
            'is_valid' => true,
            'contrast_scores' => $this->calculate_contrast_scores($colors),
            'balance_metrics' => $this->calculate_balance_metrics($colors),
            'harmony_strength' => $this->calculate_harmony_strength($colors),
            'contextual_fit' => $this->evaluate_contextual_fit($colors, $context),
            'recommendations' => []
        ];

        // Add specific recommendations if needed
        if ($validation['contrast_scores']['minimum'] < 4.5) {
            $validation['is_valid'] = false;
            $validation['recommendations'][] = [
                'type' => 'contrast_adjustment',
                'details' => 'Increase contrast for better accessibility'
            ];
        }

        return $validation;
    }

    /**
     * Generate harmonic combinations
     */
    public function generate_harmonic_combinations($base_color) {
        return [
            'complementary' => $this->get_complementary_colors($base_color),
            'analogous' => $this->get_analogous_colors($base_color),
            'triadic' => $this->get_triadic_colors($base_color),
            'split_complementary' => $this->get_split_complementary($base_color),
            'tetradic' => $this->get_tetradic_colors($base_color),
            'square' => $this->get_square_colors($base_color)
        ];
    }

    /**
     * Optimize color harmony
     */
    public function optimize_harmony($palette) {
        $current_harmony = $this->analyze_harmony($palette);
        $optimization_needed = $this->check_optimization_needed($current_harmony);

        if ($optimization_needed) {
            return [
                'optimized_palette' => $this->perform_harmony_optimization($palette),
                'harmony_score' => $this->calculate_harmony_score($palette),
                'adjustments_made' => $this->get_optimization_adjustments(),
                'harmony_analysis' => $this->analyze_optimized_harmony()
            ];
        }

        return [
            'status' => 'harmony_optimal',
            'current_score' => $current_harmony['score'],
            'analysis' => $current_harmony
        ];
    }

    /**
     * Generate harmony variations
     */
    public function generate_harmony_variations($palette) {
        return [
            'monochromatic' => $this->generate_monochromatic_variation($palette),
            'warm_harmony' => $this->generate_warm_variation($palette),
            'cool_harmony' => $this->generate_cool_variation($palette),
            'neutral_harmony' => $this->generate_neutral_variation($palette),
            'vibrant_harmony' => $this->generate_vibrant_variation($palette)
        ];
    }
}
