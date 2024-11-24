<?php
/**
 * Color AI Generator
 *
 * Handles AI-driven color palette generation based on business context and user preferences.
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

class Color_AI_Generator {
    /**
     * OpenAI API client
     * @var \OpenAI\Client
     */
    private $ai_client;

    /**
     * Business context for color generation
     * @var array
     */
    private $context = [];

    /**
     * Color naming preference
     * @var string 'descriptive'|'functional'|'both'
     */
    private $naming_preference;

    /**
     * Constructor
     *
     * @param string $api_key OpenAI API key
     * @param string $naming_preference Color naming preference
     */
    public function __construct(string $api_key, string $naming_preference = 'both') {
        $this->ai_client = new \OpenAI\Client($api_key);
        $this->naming_preference = $naming_preference;
    }

    /**
     * Get comprehensive questionnaire for business context
     *
     * @return array Array of questions
     */
    public function get_questionnaire(): array {
        return [
            'audience' => [
                'question' => __('Tell us about your target audience:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'age_range' => __('What is their age range?', 'gl-color-palette-generator'),
                    'profession' => __('What are their typical professions?', 'gl-color-palette-generator'),
                    'interests' => __('What are their main interests?', 'gl-color-palette-generator'),
                    'education' => __('What is their typical education level?', 'gl-color-palette-generator'),
                    'income' => __('What is their income bracket?', 'gl-color-palette-generator')
                ]
            ],
            'psychological_impact' => [
                'question' => __('What psychological impact do you want your website to have?', 'gl-color-palette-generator'),
                'subquestions' => [
                    'first_impression' => __('What immediate feeling should visitors have when they first see your site?', 'gl-color-palette-generator'),
                    'lasting_impression' => __('What feeling should they leave with?', 'gl-color-palette-generator'),
                    'trust_factors' => __('What aspects of trust are most important? (professionalism, authenticity, expertise)', 'gl-color-palette-generator'),
                    'action_triggers' => __('What emotions would motivate them to take action?', 'gl-color-palette-generator'),
                    'stress_points' => __('What concerns or stress points should the design help alleviate?', 'gl-color-palette-generator')
                ]
            ],
            'brand_personality' => [
                'question' => __('Describe your brand personality:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'core_traits' => __('What are your brand\'s top 3 personality traits?', 'gl-color-palette-generator'),
                    'voice_tone' => __('How would you describe your brand\'s voice? (formal, friendly, authoritative)', 'gl-color-palette-generator'),
                    'archetype' => __('Which brand archetype best fits? (Sage, Hero, Creator, Caregiver)', 'gl-color-palette-generator'),
                    'differentiation' => __('How should your personality stand out from competitors?', 'gl-color-palette-generator')
                ]
            ],
            'cultural_context' => [
                'question' => __('Understanding cultural context:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'target_regions' => __('Which geographical regions are you targeting?', 'gl-color-palette-generator'),
                    'cultural_values' => __('What cultural values should be respected or emphasized?', 'gl-color-palette-generator'),
                    'cultural_taboos' => __('Are there any cultural sensitivities to avoid?', 'gl-color-palette-generator')
                ]
            ],
            'business_goals' => [
                'question' => __('What are your business objectives?', 'gl-color-palette-generator'),
                'subquestions' => [
                    'primary_goal' => __('What is the main action you want visitors to take?', 'gl-color-palette-generator'),
                    'secondary_goals' => __('What other important actions should visitors take?', 'gl-color-palette-generator'),
                    'conversion_barriers' => __('What typically prevents people from taking these actions?', 'gl-color-palette-generator'),
                    'trust_building' => __('What elements build trust in your industry?', 'gl-color-palette-generator')
                ]
            ],
            'industry_context' => [
                'question' => __('Industry-specific considerations:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'industry_type' => __('What industry category best describes your business?', 'gl-color-palette-generator'),
                    'market_position' => __('How do you position yourself in the market? (premium, accessible, innovative)', 'gl-color-palette-generator'),
                    'competitor_analysis' => __('What color schemes do your competitors use?', 'gl-color-palette-generator'),
                    'industry_trends' => __('What design trends are relevant in your industry?', 'gl-color-palette-generator')
                ]
            ]
        ];
    }

    /**
     * Additional questionnaire sections for temporal and brand considerations
     *
     * @return array Additional questions
     */
    private function get_additional_questionnaire(): array {
        return [
            'temporal_context' => [
                'question' => __('Temporal and Seasonal Considerations:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'seasonality' => __('Is your business seasonal? If so, which seasons are most important?', 'gl-color-palette-generator'),
                    'peak_periods' => __('What are your peak business periods?', 'gl-color-palette-generator'),
                    'future_events' => __('Are there upcoming events or launches to consider?', 'gl-color-palette-generator')
                ]
            ],
            'brand_evolution' => [
                'question' => __('Future Brand Development:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'expansion_plans' => __('What are your expansion plans for the next 2-3 years?', 'gl-color-palette-generator'),
                    'target_markets' => __('Are you planning to enter new markets or demographics?', 'gl-color-palette-generator'),
                    'brand_maturity' => __('How do you see your brand maturing over time?', 'gl-color-palette-generator')
                ]
            ],
            'existing_brand' => [
                'question' => __('Existing Brand Elements:', 'gl-color-palette-generator'),
                'subquestions' => [
                    'current_colors' => __('Do you have any existing brand colors to consider?', 'gl-color-palette-generator'),
                    'color_preferences' => __('Are there any colors you specifically want to include or avoid?', 'gl-color-palette-generator'),
                    'brand_assets' => __('What existing brand assets need to be considered?', 'gl-color-palette-generator')
                ]
            ]
        ];
    }

    /**
     * Analyze color psychology based on context
     *
     * @param array $colors Generated colors
     * @return array Psychological analysis
     */
    private function analyze_color_psychology(array $colors): array {
        $analysis = [];

        foreach ($colors as $role => $color) {
            $hsl = $this->hex_to_hsl($color['hex']);

            $analysis[$role] = [
                'psychological_effects' => $this->get_psychological_effects($hsl['h']),
                'cultural_significance' => $this->get_cultural_significance($color['hex']),
                'business_alignment' => $this->analyze_business_alignment($color, $this->context),
                'emotional_response' => $this->predict_emotional_response($color, $this->context['psychological_impact'])
            ];
        }

        / Analyze palette harmony
        $analysis['palette_harmony'] = $this->analyze_palette_harmony($colors);

        / Check cultural appropriateness
        $analysis['cultural_fit'] = $this->check_cultural_fit(
            $colors,
            $this->context['cultural_context']['target_regions']
        );

        return $analysis;
    }

    /**
     * Get psychological effects of a color
     *
     * @param float $hue Color hue value
     * @return array Psychological effects
     */
    private function get_psychological_effects(float $hue): array {
        / Define color ranges and their effects
        $color_effects = [
            'red' => [
                'range' => [0, 15],
                'effects' => [
                    'primary' => ['energy', 'passion', 'excitement'],
                    'negative' => ['aggression', 'danger'],
                    'business_contexts' => ['food', 'entertainment', 'sports']
                ]
            ],
            'orange' => [
                'range' => [15, 45],
                'effects' => [
                    'primary' => ['creativity', 'adventure', 'confidence'],
                    'negative' => ['frivolity', 'immaturity'],
                    'business_contexts' => ['youth', 'arts', 'food']
                ]
            ],
            'yellow' => [
                'range' => [45, 75],
                'effects' => [
                    'primary' => ['optimism', 'clarity', 'warmth'],
                    'negative' => ['caution', 'cowardice'],
                    'business_contexts' => ['education', 'children', 'leisure']
                ]
            ],
            'green' => [
                'range' => [75, 165],
                'effects' => [
                    'primary' => ['growth', 'harmony', 'nature'],
                    'negative' => ['envy', 'boredom'],
                    'business_contexts' => ['environment', 'health', 'finance']
                ]
            ],
            'blue' => [
                'range' => [165, 255],
                'effects' => [
                    'primary' => ['trust', 'stability', 'professionalism'],
                    'negative' => ['coldness', 'aloofness'],
                    'business_contexts' => ['technology', 'finance', 'healthcare']
                ]
            ],
            'purple' => [
                'range' => [255, 315],
                'effects' => [
                    'primary' => ['luxury', 'creativity', 'mystery'],
                    'negative' => ['decadence', 'moodiness'],
                    'business_contexts' => ['luxury', 'beauty', 'spirituality']
                ]
            ],
            'pink' => [
                'range' => [315, 360],
                'effects' => [
                    'primary' => ['love', 'nurturing', 'sensitivity'],
                    'negative' => ['weakness', 'immaturity'],
                    'business_contexts' => ['beauty', 'fashion', 'romance']
                ]
            ]
        ];

        / Normalize hue to 0-360 range
        $hue = $hue % 360;
        if ($hue < 0) {
            $hue += 360;
        }

        / Find matching color range
        foreach ($color_effects as $color => $data) {
            if ($hue >= $data['range'][0] && $hue <= $data['range'][1]) {
                return $data['effects'];
            }
        }

        / Default to red if no match found (shouldn't happen with normalized hue)
        return $color_effects['red']['effects'];
    }

    /**
     * Analyze business alignment of a color
     *
     * @param array $color Color data
     * @param array $context Business context
     * @return array Alignment analysis
     */
    private function analyze_business_alignment(array $color, array $context): array {
        $alignment = [
            'industry_fit' => 0,
            'audience_fit' => 0,
            'goal_fit' => 0,
            'recommendations' => []
        ];

        $psychological_effects = $this->get_psychological_effects($this->hex_to_hsl($color['hex'])['h']);

        / Check industry alignment
        if (in_array($context['industry_context']['industry_type'], $psychological_effects['business_contexts'])) {
            $alignment['industry_fit'] = 1;
        }

        / Check audience alignment
        $audience_emotions = $this->map_audience_emotions($context['audience']);
        $alignment['audience_fit'] = count(
            array_intersect($psychological_effects['primary'], $audience_emotions)
        ) / count($audience_emotions);

        / Check business goals alignment
        $goal_emotions = $this->map_goal_emotions($context['business_goals']);
        $alignment['goal_fit'] = count(
            array_intersect($psychological_effects['primary'], $goal_emotions)
        ) / count($goal_emotions);

        / Generate recommendations
        if ($alignment['industry_fit'] < 0.5) {
            $alignment['recommendations'][] = sprintf(
                __('Consider adjusting %s to better align with industry expectations', 'gl-color-palette-generator'),
                $color['name']
            );
        }

        return $alignment;
    }

    /**
     * Map audience characteristics to expected emotional responses
     *
     * @param array $audience_data Audience context data
     * @return array Expected emotions
     */
    private function map_audience_emotions(array $audience_data): array {
        $emotion_maps = [
            'age_range' => [
                '18-24' => ['energy', 'creativity', 'adventure'],
                '25-34' => ['confidence', 'professionalism', 'innovation'],
                '35-44' => ['trust', 'stability', 'sophistication'],
                '45-64' => ['reliability', 'wisdom', 'quality'],
                '65+' => ['tradition', 'comfort', 'clarity']
            ],
            'income' => [
                'budget' => ['value', 'accessibility', 'trust'],
                'middle' => ['quality', 'reliability', 'balance'],
                'premium' => ['luxury', 'exclusivity', 'excellence']
            ]
        ];

        $emotions = [];
        foreach ($emotion_maps as $factor => $map) {
            if (isset($audience_data[$factor]) && isset($map[$audience_data[$factor]])) {
                $emotions = array_merge($emotions, $map[$audience_data[$factor]]);
            }
        }

        return array_unique($emotions);
    }

    /**
     * Generate color variations ensuring contrast requirements
     *
     * @param string $base_hex Base color hex code
     * @return array Color variations
     */
    private function generate_accessible_variations(string $base_hex): array {
        $analyzer = new Color_Analysis();
        $base_hsl = $analyzer->hex_to_hsl($base_hex);

        / Initialize variations
        $variations = [
            'lighter' => ['l' => min($base_hsl['l'] + 40, 95)],
            'light' => ['l' => min($base_hsl['l'] + 20, 85)],
            'dark' => ['l' => max($base_hsl['l'] - 20, 15)],
            'darker' => ['l' => max($base_hsl['l'] - 40, 5)]
        ];

        $results = [];
        foreach ($variations as $name => $adjustment) {
            $variation_hsl = $base_hsl;
            $variation_hsl['l'] = $adjustment['l'];

            $hex = $analyzer->hsl_to_hex($variation_hsl);

            / Verify contrast
            $black_contrast = $analyzer->calculate_contrast_ratio($hex, '#000000');
            $white_contrast = $analyzer->calculate_contrast_ratio($hex, '#FFFFFF');

            / Adjust if needed
            if ($name === 'lighter' || $name === 'light') {
                / Should contrast with black text
                while ($black_contrast < 4.5 && $variation_hsl['l'] < 95) {
                    $variation_hsl['l'] += 5;
                    $hex = $analyzer->hsl_to_hex($variation_hsl);
                    $black_contrast = $analyzer->calculate_contrast_ratio($hex, '#000000');
                }
            } else {
                / Should contrast with white text
                while ($white_contrast < 4.5 && $variation_hsl['l'] > 5) {
                    $variation_hsl['l'] -= 5;
                    $hex = $analyzer->hsl_to_hex($variation_hsl);
                    $white_contrast = $analyzer->calculate_contrast_ratio($hex, '#FFFFFF');
                }
            }

            $results[$name] = [
                'hex' => $hex,
                'contrast' => [
                    'black' => $black_contrast,
                    'white' => $white_contrast
                ]
            ];
        }

        return $results;
    }

    /**
     * Predict emotional response based on context
     *
     * @param array $color Color data
     * @param array $context Psychological impact context
     * @return array Predicted responses
     */
    private function predict_emotional_response(array $color, array $context): array {
        $hsl = $this->hex_to_hsl($color['hex']);
        $base_effects = $this->get_psychological_effects($hsl['h']);

        $response = [
            'alignment' => [
                'first_impression' => 0,
                'lasting_impression' => 0,
                'trust_factors' => 0
            ],
            'strength' => min(100, max(0, $hsl['s'])), / Saturation indicates emotional intensity
            'clarity' => min(100, max(0, 100 - abs(50 - $hsl['l']) * 2)), / How clear the emotion is
            'effects' => []
        ];

        / Match psychological effects with desired impacts
        foreach (['first_impression', 'lasting_impression'] as $impact_type) {
            $desired_feeling = strtolower($context[$impact_type]);
            $response['alignment'][$impact_type] = in_array($desired_feeling, $base_effects['primary']) ? 1 : 0;
        }

        / Analyze trust factors
        $trust_keywords = array_map('strtolower', explode(',', $context['trust_factors']));
        $trust_matches = array_intersect($trust_keywords, $base_effects['primary']);
        $response['alignment']['trust_factors'] = count($trust_matches) / count($trust_keywords);

        / Add predicted effects
        $response['effects'] = $base_effects['primary'];

        return $response;
    }

    /**
     * Check cultural fit of colors
     *
     * @param array $colors Color palette
     * @param string $target_regions Target geographical regions
     * @return array Cultural fit analysis
     */
    private function check_cultural_fit(array $colors, string $target_regions): array {
        $regions = array_map('trim', explode(',', $target_regions));
        $cultural_concerns = [];

        $cultural_meanings = [
            'east_asia' => [
                'red' => ['positive' => ['luck', 'prosperity'], 'negative' => []],
                'white' => ['positive' => ['purity'], 'negative' => ['death', 'mourning']],
                'gold' => ['positive' => ['wealth', 'prosperity'], 'negative' => []]
            ],
            'middle_east' => [
                'green' => ['positive' => ['paradise', 'spirituality'], 'negative' => []],
                'blue' => ['positive' => ['protection', 'heaven'], 'negative' => []]
            ],
            'western' => [
                'white' => ['positive' => ['purity', 'cleanliness'], 'negative' => []],
                'black' => ['positive' => ['luxury', 'sophistication'], 'negative' => ['death']]
            ]
        ];

        foreach ($colors as $role => $color) {
            $hsl = $this->hex_to_hsl($color['hex']);
            $color_name = $this->get_basic_color_name($hsl);

            foreach ($regions as $region) {
                $region = strtolower(trim($region));
                if (isset($cultural_meanings[$region][$color_name])) {
                    $meaning = $cultural_meanings[$region][$color_name];
                    if (!empty($meaning['negative'])) {
                        $cultural_concerns[] = [
                            'role' => $role,
                            'region' => $region,
                            'concerns' => $meaning['negative']
                        ];
                    }
                }
            }
        }

        return [
            'has_concerns' => !empty($cultural_concerns),
            'concerns' => $cultural_concerns
        ];
    }

    / ... (to be continued)
}
