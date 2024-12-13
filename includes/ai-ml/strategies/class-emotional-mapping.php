<?php
/**
 * Emotional Color Mapping Strategy Class
 *
 * Implements color selection strategies based on emotional psychology
 * and color-emotion associations across different contexts.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage AI_ML\Strategies
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\AI_ML\Strategies;

use GL_Color_Palette_Generator\Analysis\Color_Analyzer;
use GL_Color_Palette_Generator\Analysis\Cultural_Context;
use GL_Color_Palette_Generator\Analysis\Psychological_Data;

/**
 * Class Emotional_Mapping
 *
 * Maps colors to emotional responses and psychological effects,
 * considering cultural context and psychological research.
 *
 * @since 1.0.0
 */
class Emotional_Mapping {
    /**
     * Color analysis instance
     *
     * @var Color_Analyzer
     * @since 1.0.0
     */
    private $color_analyzer;

    /**
     * Cultural context instance
     *
     * @var Cultural_Context
     * @since 1.0.0
     */
    private $cultural_context;

    /**
     * Psychological data instance
     *
     * @var Psychological_Data
     * @since 1.0.0
     */
    private $psychological_data;

    /**
     * Comprehensive emotional color mappings
     *
     * @var array
     */
    private const EMOTIONAL_MAPPINGS = [
        'primary_emotions' => [
            'joy' => [
                'core_colors' => [
                    'yellow' => [
                        'hex' => '#FFD700',
                        'variations' => [
                            'bright' => ['#FFEB3B', 'energetic_happiness'],
                            'soft' => ['#F0E68C', 'gentle_contentment'],
                            'warm' => ['#FFA000', 'radiant_delight']
                        ],
                        'psychological_effects' => [
                            'primary' => ['optimism', 'enthusiasm', 'spontaneity'],
                            'secondary' => ['creativity', 'confidence', 'playfulness'],
                            'physiological' => ['increased_serotonin', 'mental_stimulation']
                        ],
                        'applications' => [
                            'marketing' => ['children_products', 'summer_campaigns'],
                            'spaces' => ['creative_areas', 'social_zones'],
                            'branding' => ['entertainment', 'food_industry']
                        ]
                    ],
                    'orange' => [
                        'hex' => '#FFA500',
                        'variations' => [
                            'vibrant' => ['#FF9800', 'social_excitement'],
                            'coral' => ['#FF7F50', 'warm_happiness'],
                            'peach' => ['#FFDAB9', 'gentle_joy']
                        ]
                    ]
                ],
                'complementary_emotions' => ['excitement', 'satisfaction', 'pleasure'],
                'contextual_variations' => [
                    'cultural' => [
                        'western' => ['celebration', 'positivity'],
                        'eastern' => ['prosperity', 'spiritual_joy']
                    ],
                    'seasonal' => [
                        'summer' => ['vibrant_joy', 'playful_energy'],
                        'spring' => ['fresh_happiness', 'renewal_joy']
                    ]
                ]
            ],

            'trust' => [
                'core_colors' => [
                    'blue' => [
                        'hex' => '#0066CC',
                        'variations' => [
                            'deep' => ['#004C99', 'profound_trust'],
                            'professional' => ['#0077B5', 'business_trust'],
                            'gentle' => ['#B0C4DE', 'approachable_trust']
                        ],
                        'psychological_effects' => [
                            'primary' => ['reliability', 'competence', 'stability'],
                            'secondary' => ['security', 'calmness', 'order'],
                            'physiological' => ['lowered_heart_rate', 'mental_clarity']
                        ],
                        'applications' => [
                            'corporate' => ['banking', 'healthcare', 'technology'],
                            'digital' => ['security_features', 'professional_platforms'],
                            'communication' => ['official_documents', 'formal_presentations']
                        ]
                    ]
                ],
                'complementary_emotions' => ['security', 'reliability', 'confidence'],
                'contextual_applications' => [
                    'business' => ['corporate_identity', 'professional_services'],
                    'healthcare' => ['medical_facilities', 'wellness_programs'],
                    'education' => ['learning_platforms', 'academic_institutions']
                ]
            ],

            'passion' => [
                'core_colors' => [
                    'red' => [
                        'hex' => '#FF0000',
                        'variations' => [
                            'deep' => ['#8B0000', 'intense_passion'],
                            'bright' => ['#FF4444', 'energetic_passion'],
                            'burgundy' => ['#800020', 'sophisticated_passion']
                        ],
                        'psychological_effects' => [
                            'primary' => ['excitement', 'desire', 'power'],
                            'secondary' => ['courage', 'strength', 'determination'],
                            'physiological' => ['increased_heart_rate', 'adrenaline_boost']
                        ],
                        'applications' => [
                            'branding' => ['sports', 'entertainment', 'food'],
                            'marketing' => ['sales', 'promotions', 'urgency'],
                            'design' => ['call_to_action', 'emphasis_points']
                        ]
                    ]
                ],
                'intensity_mapping' => [
                    'subtle' => ['pink_tones', 'soft_energy'],
                    'moderate' => ['red_orange', 'balanced_passion'],
                    'intense' => ['pure_red', 'maximum_impact']
                ]
            ],

            'serenity' => [
                'core_colors' => [
                    'green' => [
                        'hex' => '#4CAF50',
                        'variations' => [
                            'sage' => ['#9CAF88', 'gentle_calm'],
                            'forest' => ['#228B22', 'natural_peace'],
                            'mint' => ['#98FF98', 'fresh_tranquility']
                        ],
                        'psychological_effects' => [
                            'primary' => ['balance', 'harmony', 'growth'],
                            'secondary' => ['renewal', 'restoration', 'peace'],
                            'physiological' => ['stress_reduction', 'visual_comfort']
                        ],
                        'applications' => [
                            'wellness' => ['spa_facilities', 'meditation_spaces'],
                            'healthcare' => ['waiting_rooms', 'recovery_areas'],
                            'workspaces' => ['break_rooms', 'focus_areas']
                        ]
                    ]
                ],
                'environmental_context' => [
                    'natural' => ['outdoor_spaces', 'eco_friendly_brands'],
                    'urban' => ['city_sanctuaries', 'wellness_centers']
                ]
            ],

            'tranquility' => [
                'core_colors' => [
                    'soft_blue' => [
                        'hex' => '#B0C4DE',
                        'variations' => [
                            'morning_mist' => ['#E6EFF6', 'gentle_awakening'],
                            'ocean_calm' => ['#7BA4DB', 'deep_peace'],
                            'twilight' => ['#4A708B', 'evening_serenity']
                        ],
                        'psychological_effects' => [
                            'primary' => ['calmness', 'clarity', 'inner_peace'],
                            'secondary' => ['mental_rest', 'emotional_balance'],
                            'physiological' => ['lowered_blood_pressure', 'reduced_anxiety']
                        ]
                    ],
                    'sage_green' => [
                        'hex' => '#9CAF88',
                        'variations' => [
                            'morning_dew' => ['#D1E2C4', 'fresh_calm'],
                            'forest_peace' => ['#738B69', 'natural_tranquility']
                        ]
                    ]
                ],
                'applications' => [
                    'spaces' => ['meditation_rooms', 'spa_facilities', 'reading_nooks'],
                    'products' => ['wellness_apps', 'sleep_aids', 'relaxation_tools'],
                    'content' => ['mindfulness_materials', 'therapeutic_resources']
                ]
            ],

            'contemplation' => [
                'core_colors' => [
                    'deep_purple' => [
                        'hex' => '#483D8B',
                        'variations' => [
                            'twilight' => ['#2E1A47', 'deep_thought'],
                            'lavender' => ['#E6E6FA', 'gentle_reflection'],
                            'midnight' => ['#191970', 'profound_insight']
                        ],
                        'psychological_effects' => [
                            'primary' => ['introspection', 'wisdom', 'spiritual_awareness'],
                            'secondary' => ['mental_depth', 'emotional_understanding'],
                            'physiological' => ['enhanced_focus', 'mental_clarity']
                        ]
                    ]
                ],
                'applications' => [
                    'spaces' => ['study_areas', 'spiritual_centers', 'libraries'],
                    'digital' => ['philosophy_platforms', 'educational_apps'],
                    'branding' => ['academic_institutions', 'spiritual_organizations']
                ]
            ],

            'vitality' => [
                'core_colors' => [
                    'vibrant_green' => [
                        'hex' => '#32CD32',
                        'variations' => [
                            'spring_burst' => ['#90EE90', 'fresh_energy'],
                            'forest_life' => ['#228B22', 'natural_vigor'],
                            'lime_zest' => ['#9ACD32', 'active_energy']
                        ],
                        'psychological_effects' => [
                            'primary' => ['renewal', 'growth', 'health'],
                            'secondary' => ['motivation', 'natural_energy'],
                            'physiological' => ['increased_vitality', 'enhanced_mood']
                        ]
                    ]
                ],
                'applications' => [
                    'health' => ['fitness_centers', 'nutrition_brands'],
                    'products' => ['sports_equipment', 'health_foods'],
                    'spaces' => ['gyms', 'outdoor_recreation']
                ]
            ],

            'mystery' => [
                'core_colors' => [
                    'deep_indigo' => [
                        'hex' => '#4B0082',
                        'variations' => [
                            'midnight' => ['#2E0854', 'deep_enigma'],
                            'twilight' => ['#6A5ACD', 'subtle_intrigue'],
                            'cosmic' => ['#483D8B', 'celestial_mystery']
                        ],
                        'psychological_effects' => [
                            'primary' => ['intrigue', 'curiosity', 'wonder'],
                            'secondary' => ['imagination', 'discovery'],
                            'physiological' => ['heightened_awareness', 'mental_engagement']
                        ]
                    ]
                ],
                'applications' => [
                    'entertainment' => ['mystery_genres', 'puzzle_games'],
                    'branding' => ['luxury_mystery', 'exclusive_experiences'],
                    'spaces' => ['escape_rooms', 'immersive_experiences']
                ]
            ],

            'empowerment' => [
                'core_colors' => [
                    'royal_purple' => [
                        'hex' => '#7851A9',
                        'variations' => [
                            'regal' => ['#9370DB', 'confident_power'],
                            'deep_strength' => ['#663399', 'inner_power'],
                            'wisdom' => ['#8B008B', 'enlightened_strength']
                        ],
                        'psychological_effects' => [
                            'primary' => ['confidence', 'leadership', 'dignity'],
                            'secondary' => ['self_assurance', 'authority'],
                            'physiological' => ['increased_confidence', 'enhanced_presence']
                        ]
                    ]
                ],
                'applications' => [
                    'business' => ['leadership_programs', 'executive_coaching'],
                    'education' => ['personal_development', 'skill_building'],
                    'branding' => ['empowerment_brands', 'coaching_services']
                ]
            ],

            'wonder' => [
                'core_colors' => [
                    'celestial_blue' => [
                        'hex' => '#4169E1',
                        'variations' => [
                            'starlight' => ['#87CEEB', 'dreamy_wonder'],
                            'cosmic' => ['#191970', 'deep_amazement'],
                            'aurora' => ['#40E0D0', 'magical_discovery']
                        ],
                        'psychological_effects' => [
                            'primary' => ['awe', 'inspiration', 'discovery'],
                            'secondary' => ['imagination', 'possibility'],
                            'physiological' => ['expanded_perception', 'heightened_senses']
                        ]
                    ]
                ],
                'applications' => [
                    'education' => ['science_centers', 'museums', 'planetariums'],
                    'entertainment' => ['theme_parks', 'immersive_experiences'],
                    'digital' => ['educational_apps', 'discovery_platforms']
                ]
            ]
        ],

        'emotional_combinations' => [
            'inspiration' => [
                'color_blend' => [
                    'primary' => ['purple', 'blue'],
                    'accent' => ['gold', 'white']
                ],
                'psychological_impact' => ['creativity', 'vision', 'wisdom'],
                'applications' => ['creative_spaces', 'educational_materials']
            ],
            'tranquility' => [
                'color_blend' => [
                    'primary' => ['blue', 'green'],
                    'accent' => ['white', 'silver']
                ],
                'psychological_impact' => ['peace', 'balance', 'harmony'],
                'applications' => ['wellness_centers', 'meditation_apps']
            ]
        ],

        'emotional_transitions' => [
            'energy_levels' => [
                'high' => ['red', 'orange', 'yellow'],
                'medium' => ['green', 'blue', 'purple'],
                'low' => ['blue', 'gray', 'brown']
            ],
            'mood_progression' => [
                'morning' => ['energetic_yellow', 'fresh_orange'],
                'afternoon' => ['balanced_blue', 'productive_green'],
                'evening' => ['calming_purple', 'restful_blue']
            ]
        ]
    ];

    /**
     * Get emotional associations for color
     *
     * @param string $color
     * @param array $context
     * @return array|null
     */
    public function get_emotional_mapping($color, $context = []) {
        foreach (self::EMOTIONAL_MAPPINGS['primary_emotions'] as $emotion => $data) {
            foreach ($data['core_colors'] as $color_name => $color_data) {
                if ($this->color_matches($color, $color_data['hex'])) {
                    return $this->adapt_to_context($color_data, $context);
                }
            }
        }
        return null;
    }

    /**
     * Generate emotional color strategy
     *
     * @param string $target_emotion
     * @param array $context
     * @return array|null
     */
    public function generate_emotional_strategy($target_emotion, $context = []) {
        if (isset(self::EMOTIONAL_MAPPINGS['primary_emotions'][$target_emotion])) {
            $emotion_data = self::EMOTIONAL_MAPPINGS['primary_emotions'][$target_emotion];

            return [
                'color_palette' => $this->generate_emotional_palette($emotion_data),
                'psychological_effects' => $this->compile_psychological_effects($emotion_data),
                'applications' => $this->compile_applications($emotion_data, $context),
                'variations' => $this->generate_contextual_variations($emotion_data, $context)
            ];
        }
        return null;
    }

    /**
     * Generate emotional transitions
     *
     * @param string $start_emotion
     * @param string $end_emotion
     * @return array
     */
    public function generate_emotional_transition($start_emotion, $end_emotion) {
        return [
            'color_progression' => $this->calculate_color_progression($start_emotion, $end_emotion),
            'psychological_journey' => $this->map_psychological_journey($start_emotion, $end_emotion),
            'application_guidelines' => $this->compile_transition_guidelines($start_emotion, $end_emotion)
        ];
    }
}
