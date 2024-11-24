<?php
namespace GLColorPalette;

class PersonalityMatching {
    private $personality_analyzer;
    private $preference_calculator;
    private $context_evaluator;

    / Comprehensive personality-color mapping
    private const PERSONALITY_COLOR_MAPPING = [
        'personality_dimensions' => [
            'openness' => [
                'high' => [
                    'primary_colors' => [
                        'purple' => [
                            'traits' => ['creativity', 'imagination', 'curiosity'],
                            'variations' => [
                                'deep_purple' => ['intellectual_depth', 'artistic_appreciation'],
                                'bright_purple' => ['innovation', 'experimentation'],
                                'soft_purple' => ['intuition', 'sensitivity']
                            ],
                            'combinations' => [
                                'creative' => ['purple', 'blue', 'teal'],
                                'artistic' => ['purple', 'gold', 'deep_blue'],
                                'experimental' => ['purple', 'orange', 'chartreuse']
                            ]
                        ],
                        'teal' => [
                            'traits' => ['unconventional', 'progressive', 'explorative'],
                            'applications' => ['creative_spaces', 'learning_environments']
                        ]
                    ],
                    'color_characteristics' => [
                        'complexity' => 'high',
                        'uniqueness' => 'preferred',
                        'harmony_type' => 'experimental'
                    ]
                ],
                'low' => [
                    'primary_colors' => [
                        'brown' => [
                            'traits' => ['traditional', 'practical', 'grounded'],
                            'variations' => [
                                'warm_brown' => ['comfort', 'reliability'],
                                'dark_brown' => ['stability', 'convention']
                            ]
                        ]
                    ],
                    'color_characteristics' => [
                        'complexity' => 'low',
                        'uniqueness' => 'avoided',
                        'harmony_type' => 'traditional'
                    ]
                ]
            ],

            'conscientiousness' => [
                'high' => [
                    'primary_colors' => [
                        'navy_blue' => [
                            'traits' => ['organization', 'efficiency', 'reliability'],
                            'variations' => [
                                'deep_navy' => ['authority', 'competence'],
                                'structured_blue' => ['order', 'precision']
                            ],
                            'applications' => ['work_spaces', 'productivity_tools']
                        ],
                        'gray' => [
                            'traits' => ['methodical', 'structured', 'disciplined'],
                            'variations' => [
                                'charcoal' => ['professionalism', 'focus'],
                                'silver' => ['refinement', 'attention_to_detail']
                            ]
                        ]
                    ],
                    'color_characteristics' => [
                        'organization' => 'structured',
                        'precision' => 'high',
                        'harmony_type' => 'systematic'
                    ]
                ]
            ],

            'extraversion' => [
                'high' => [
                    'primary_colors' => [
                        'orange' => [
                            'traits' => ['sociability', 'energy', 'enthusiasm'],
                            'variations' => [
                                'bright_orange' => ['excitement', 'stimulation'],
                                'coral' => ['warmth', 'friendliness']
                            ],
                            'social_contexts' => [
                                'gathering_spaces' => ['vibrancy' => 'high', 'energy' => 'dynamic'],
                                'communication_tools' => ['engagement' => 'active', 'interaction' => 'encouraged']
                            ]
                        ],
                        'yellow' => [
                            'traits' => ['optimism', 'expressiveness', 'outgoing'],
                            'applications' => ['social_spaces', 'entertainment_venues']
                        ]
                    ],
                    'color_characteristics' => [
                        'brightness' => 'high',
                        'energy' => 'vibrant',
                        'harmony_type' => 'dynamic'
                    ]
                ]
            ],

            'agreeableness' => [
                'high' => [
                    'primary_colors' => [
                        'green' => [
                            'traits' => ['harmony', 'compassion', 'cooperation'],
                            'variations' => [
                                'sage' => ['nurturing', 'balance'],
                                'mint' => ['freshness', 'approachability']
                            ],
                            'relationship_contexts' => [
                                'collaborative_spaces' => ['harmony' => 'emphasized', 'comfort' => 'priority'],
                                'healing_environments' => ['peace' => 'central', 'support' => 'evident']
                            ]
                        ]
                    ]
                ]
            ],

            'emotional_stability' => [
                'high' => [
                    'primary_colors' => [
                        'blue' => [
                            'traits' => ['calmness', 'stability', 'resilience'],
                            'variations' => [
                                'sky_blue' => ['serenity', 'clarity'],
                                'deep_blue' => ['depth', 'confidence']
                            ],
                            'emotional_contexts' => [
                                'personal_spaces' => ['tranquility' => 'primary', 'balance' => 'key'],
                                'professional_environments' => ['stability' => 'emphasized', 'trust' => 'conveyed']
                            ]
                        ]
                    ]
                ]
            ]
        ],

        'interaction_patterns' => [
            'color_combinations' => [
                'social_confidence' => ['orange', 'blue', 'yellow'],
                'creative_focus' => ['purple', 'gray', 'teal'],
                'balanced_energy' => ['green', 'coral', 'navy'],
                'structured_innovation' => ['blue', 'purple', 'silver']
            ],
            'environmental_adaptations' => [
                'work' => ['productivity' => 'primary', 'creativity' => 'secondary'],
                'social' => ['engagement' => 'primary', 'comfort' => 'secondary'],
                'personal' => ['reflection' => 'primary', 'expression' => 'secondary']
            ]
        ]
    ];

    /**
     * Generate personality-based color recommendations
     */
    public function generate_personality_colors($personality_profile, $context = []) {
        $recommendations = [
            'primary_palette' => $this->calculate_primary_colors($personality_profile),
            'accent_colors' => $this->determine_accent_colors($personality_profile),
            'combination_patterns' => $this->generate_combinations($personality_profile),
            'application_guidelines' => $this->create_application_guidelines($personality_profile, $context),
            'contextual_adaptations' => $this->generate_adaptations($personality_profile, $context)
        ];

        return $recommendations;
    }

    /**
     * Analyze color-personality compatibility
     */
    public function analyze_compatibility($colors, $personality_profile) {
        return [
            'overall_match' => $this->calculate_match_score($colors, $personality_profile),
            'dimension_alignment' => $this->analyze_dimension_alignment($colors, $personality_profile),
            'contextual_fit' => $this->evaluate_contextual_fit($colors, $personality_profile),
            'recommendations' => $this->generate_optimization_recommendations($colors, $personality_profile)
        ];
    }

    /**
     * Match colors to personality traits
     */
    public function match_personality_traits($traits) {
        $color_mappings = [];
        foreach ($traits as $trait => $score) {
            $color_mappings[$trait] = [
                'primary_colors' => $this->get_primary_colors_for_trait($trait, $score),
                'secondary_colors' => $this->get_secondary_colors_for_trait($trait, $score),
                'combinations' => $this->get_color_combinations_for_trait($trait, $score)
            ];
        }

        return [
            'color_mappings' => $color_mappings,
            'palette_suggestions' => $this->generate_palette_suggestions($color_mappings),
            'personality_analysis' => $this->analyze_personality_color_fit($color_mappings)
        ];
    }

    /**
     * Generate brand personality palette
     */
    public function generate_brand_personality_palette($brand_traits) {
        $emotional = new EmotionalMapping();
        $psychological = new PsychologicalEffects();

        return [
            'primary_palette' => $this->generate_primary_brand_colors($brand_traits),
            'accent_colors' => $this->generate_accent_colors($brand_traits),
            'emotional_impact' => $emotional->analyze_brand_emotions($brand_traits),
            'psychological_alignment' => $psychological->analyze_brand_alignment($brand_traits)
        ];
    }

    /**
     * Optimize personality-color alignment
     */
    public function optimize_personality_alignment($current_palette, $target_traits) {
        $current_alignment = $this->analyze_current_alignment($current_palette, $target_traits);
        $optimization_suggestions = $this->generate_optimization_suggestions($current_alignment);

        return [
            'current_alignment' => $current_alignment,
            'suggested_changes' => $optimization_suggestions,
            'optimized_palette' => $this->generate_optimized_palette($current_palette, $optimization_suggestions),
            'implementation_guide' => $this->create_implementation_guide($optimization_suggestions)
        ];
    }
}
