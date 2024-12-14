<?php
/**
 * Personality Matching Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage AI_ML\Analysis
 */

namespace GL_Color_Palette_Generator\AI_ML\Analysis;

use WP_Error;

/**
 * Class Personality_Matching
 *
 * Handles matching colors to personality traits and preferences
 *
 * @since 1.0.0
 */
class Personality_Matching {
    /**
     * Personality analyzer instance
     *
     * @var object
     */
    private $personality_analyzer;

    /**
     * Preference calculator instance
     *
     * @var object
     */
    private $preference_calculator;

    /**
     * Context evaluator instance
     *
     * @var object
     */
    private $context_evaluator;

    /**
     * Personality color mappings
     *
     * @var array
     */
    private const PERSONALITY_MAPPINGS = [
        'openness' => [
            'high' => ['blue', 'purple', 'turquoise'],
            'moderate' => ['green', 'yellow', 'orange'],
            'low' => ['brown', 'gray', 'black']
        ],
        'conscientiousness' => [
            'high' => ['navy', 'gray', 'black'],
            'moderate' => ['blue', 'brown', 'green'],
            'low' => ['red', 'orange', 'yellow']
        ],
        'extraversion' => [
            'high' => ['red', 'orange', 'yellow'],
            'moderate' => ['green', 'purple', 'pink'],
            'low' => ['blue', 'gray', 'brown']
        ],
        'agreeableness' => [
            'high' => ['pink', 'peach', 'light_blue'],
            'moderate' => ['green', 'yellow', 'lavender'],
            'low' => ['red', 'black', 'dark_gray']
        ],
        'neuroticism' => [
            'high' => ['gray', 'black', 'brown'],
            'moderate' => ['blue', 'green', 'purple'],
            'low' => ['yellow', 'orange', 'pink']
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
