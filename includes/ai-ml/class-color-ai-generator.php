<?php
/**
 * Color AI Generator Class
 *
 * Handles AI-driven color palette generation based on business context and user preferences.
 * Integrates with various AI providers to generate contextually appropriate color schemes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage AI_ML
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\AI_ML;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Interfaces\Color_Generator_Interface;
use GL_Color_Palette_Generator\Settings\Settings_Manager;

/**
 * Class Color_AI_Generator
 *
 * Main class for handling AI-based color palette generation.
 * Implements various strategies for generating color palettes using AI models.
 *
 * @since 1.0.0
 */
class Color_AI_Generator implements Color_Generator_Interface {
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
     * AI provider instance
     *
     * @var AI_Provider_Base The AI service provider instance
     * @since 1.0.0
     */
    private $ai_provider;

    /**
     * Settings manager instance
     *
     * @var Settings_Manager The settings manager instance
     * @since 1.0.0
     */
    private $settings;

    /**
     * Base color for generation
     * @var string|null
     */
    private $base_color = null;

    /**
     * Generation constraints
     * @var array
     */
    private $constraints = [];

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

        // Analyze palette harmony
        $analysis['palette_harmony'] = $this->analyze_palette_harmony($colors);

        // Check cultural appropriateness
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
        foreach (Color_Constants::COLOR_RANGES as $color => $range) {
            if ($hue >= $range[0] && $hue < $range[1]) {
                return Color_Constants::COLOR_PSYCHOLOGICAL_EFFECTS[$color]['effects'];
            }
        }
        return [];
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

        // Check industry alignment
        if (in_array($context['industry_context']['industry_type'], $psychological_effects['business_contexts'])) {
            $alignment['industry_fit'] = 1;
        }

        // Check audience alignment
        $audience_emotions = $this->map_audience_emotions($context['audience']);
        $alignment['audience_fit'] = count(
            array_intersect($psychological_effects['primary'], $audience_emotions)
        ) / count($audience_emotions);

        // Check business goals alignment
        $goal_emotions = $this->map_goal_emotions($context['business_goals']);
        $alignment['goal_fit'] = count(
            array_intersect($psychological_effects['primary'], $goal_emotions)
        ) / count($goal_emotions);

        // Generate recommendations
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

        // Initialize variations using constants
        $variations = [
            Color_Constants::COLOR_VARIATIONS['lighter'] => [
                'l' => min($base_hsl['l'] + 40, Color_Constants::MAX_LIGHTNESS)
            ],
            Color_Constants::COLOR_VARIATIONS['light'] => [
                'l' => min($base_hsl['l'] + 20, Color_Constants::HIGH_LIGHTNESS)
            ],
            Color_Constants::COLOR_VARIATIONS['dark'] => [
                'l' => max($base_hsl['l'] - 20, Color_Constants::LOW_LIGHTNESS)
            ],
            Color_Constants::COLOR_VARIATIONS['darker'] => [
                'l' => max($base_hsl['l'] - 40, Color_Constants::MIN_LIGHTNESS)
            ]
        ];

        $results = [];
        foreach ($variations as $name => $adjustment) {
            $variation_hsl = $base_hsl;
            $variation_hsl['l'] = $adjustment['l'];
            $hex = $analyzer->hsl_to_hex($variation_hsl);

            // Verify contrast
            $black_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_BLACK);
            $white_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_WHITE);

            // Adjust if needed
            if (in_array($name, [Color_Constants::COLOR_VARIATIONS['lighter'], Color_Constants::COLOR_VARIATIONS['light']])) {
                // Should contrast with black text
                while ($black_contrast < Color_Constants::WCAG_CONTRAST_MIN &&
                       $variation_hsl['l'] < Color_Constants::MAX_LIGHTNESS) {
                    $variation_hsl['l'] += Color_Constants::LIGHTNESS_STEP;
                    $hex = $analyzer->hsl_to_hex($variation_hsl);
                    $black_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_BLACK);
                }
            } else {
                // Should contrast with white text
                while ($white_contrast < Color_Constants::WCAG_CONTRAST_MIN &&
                       $variation_hsl['l'] > Color_Constants::MIN_LIGHTNESS) {
                    $variation_hsl['l'] -= Color_Constants::LIGHTNESS_STEP;
                    $hex = $analyzer->hsl_to_hex($variation_hsl);
                    $white_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_WHITE);
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
            // Saturation indicates emotional intensity
            // Clarity how clear the emotion is
            'strength' => min(100, max(0, $hsl['s'])),
            'clarity' => min(100, max(0, 100 - abs(50 - $hsl['l']) * 2)),
            'effects' => []
        ];

        // Match psychological effects with desired impacts
        foreach (['first_impression', 'lasting_impression'] as $impact_type) {
            $desired_feeling = strtolower($context[$impact_type]);
            $response['alignment'][$impact_type] = in_array($desired_feeling, $base_effects['primary']) ? 1 : 0;
        }

        // Analyze trust factors
        $trust_keywords = array_map('strtolower', explode(',', $context['trust_factors']));
        $trust_matches = array_intersect($trust_keywords, $base_effects['primary']);
        $response['alignment']['trust_factors'] = count($trust_matches) / count($trust_keywords);

        // Add predicted effects
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

    /**
     * Adjust color for accessibility
     *
     * @param string $hex Original hex color
     * @return string Adjusted hex color
     */
    private function adjust_for_accessibility(string $hex): string {
        $variation_hsl = $this->color_converter->hex_to_hsl($hex);
        $analyzer = new Color_Metrics();

        // Check contrast with black and white
        $black_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_BLACK);
        $white_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_WHITE);

        // Adjust lightness until we achieve minimum contrast with either black or white
        $min_contrast = Color_Constants::WCAG_CONTRAST_MIN;

        if ($black_contrast < $min_contrast && $white_contrast < $min_contrast) {
            if ($variation_hsl['l'] < 50) {
                // Dark color - adjust lighter for black text
                while ($black_contrast < $min_contrast && $variation_hsl['l'] < Color_Constants::MAX_LIGHTNESS) {
                    $variation_hsl['l'] += 5;
                    $hex = $this->color_converter->hsl_to_hex($variation_hsl);
                    $black_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_BLACK);
                }
            } else {
                // Light color - adjust darker for white text
                while ($white_contrast < $min_contrast && $variation_hsl['l'] > Color_Constants::MIN_LIGHTNESS) {
                    $variation_hsl['l'] -= 5;
                    $hex = $this->color_converter->hsl_to_hex($variation_hsl);
                    $white_contrast = $analyzer->calculate_contrast_ratio($hex, Color_Constants::COLOR_WHITE);
                }
            }
        }

        return $hex;
    }

    /**
     * Generate system colors that harmonize with the theme
     *
     * @param array $base_colors Array of base theme colors
     * @return array Generated system colors
     */
    public function generate_system_colors(array $base_colors): array {
        // Implementation coming soon
        return [];
    }

    /**
     * Generate a single color based on given parameters.
     *
     * @param array $params Optional parameters to influence color generation.
     * @return string Generated color in hexadecimal format.
     */
    public function generate_color(array $params = []): string {
        // Merge with existing context and constraints
        $generation_params = array_merge($this->context, $params);
        
        try {
            // Use AI to generate a contextually appropriate color
            $color = $this->ai_client->generateColor($generation_params);
            return $this->validate_and_format_color($color);
        } catch (\Exception $e) {
            error_log('Color generation failed: ' . $e->getMessage());
            // Return a fallback color if AI generation fails
            return '#000000';
        }
    }

    /**
     * Generate multiple colors based on given parameters.
     *
     * @param int   $count  Number of colors to generate.
     * @param array $params Optional parameters to influence color generation.
     * @return array Array of generated colors in hexadecimal format.
     */
    public function generate_colors(int $count, array $params = []): array {
        $colors = [];
        $generation_params = array_merge($this->context, $params);
        
        try {
            if ($this->base_color) {
                $generation_params['base_color'] = $this->base_color;
            }
            
            // Generate palette using AI
            $colors = $this->ai_client->generatePalette($count, $generation_params);
            
            // Validate and format each color
            return array_map([$this, 'validate_and_format_color'], $colors);
        } catch (\Exception $e) {
            error_log('Palette generation failed: ' . $e->getMessage());
            // Return fallback colors if AI generation fails
            return array_fill(0, $count, '#000000');
        }
    }

    /**
     * Set the base color for generation.
     *
     * @param string $color Base color in hexadecimal format.
     * @return void
     */
    public function set_base_color(string $color): void {
        $this->base_color = $this->validate_and_format_color($color);
    }

    /**
     * Get the current base color.
     *
     * @return string|null Current base color in hexadecimal format or null if not set.
     */
    public function get_base_color(): ?string {
        return $this->base_color;
    }

    /**
     * Set generation constraints.
     *
     * @param array $constraints Array of constraints for color generation.
     * @return void
     */
    public function set_constraints(array $constraints): void {
        $this->constraints = $constraints;
    }

    /**
     * Get current generation constraints.
     *
     * @return array Current constraints.
     */
    public function get_constraints(): array {
        return $this->constraints;
    }

    /**
     * Validate and format a color to ensure proper hexadecimal format.
     *
     * @param string $color Color to validate and format.
     * @return string Validated and formatted color in hexadecimal format.
     */
    private function validate_and_format_color(string $color): string {
        // Remove any spaces and '#' if present
        $color = str_replace(['#', ' '], '', $color);
        
        // Validate hex format
        if (!preg_match('/^[0-9A-F]{6}$/i', $color)) {
            error_log('Invalid color format: ' . $color);
            return '#000000';
        }
        
        return '#' . strtoupper($color);
    }
}
