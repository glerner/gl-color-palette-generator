<?php
/**
 * Psychological Effects Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage AI_ML\Strategies
 */

namespace GL_Color_Palette_Generator\AI_ML\Strategies;

use WP_Error;

/**
 * Class Psychological_Effects
 *
 * Analyzes and applies psychological effects of colors
 *
 * @since 1.0.0
 */
class Psychological_Effects {
    /**
     * Psychological effects mapping
     *
     * @var array
     */
    private const PSYCHOLOGICAL_EFFECTS = [
        'cognitive_impacts' => [
            'attention' => [
                'red' => ['high_arousal', 'increased_focus'],
                'blue' => ['improved_concentration', 'mental_clarity'],
                'yellow' => ['enhanced_memory', 'mental_stimulation']
            ],
            'decision_making' => [
                'green' => ['balanced_judgment', 'reduced_anxiety'],
                'purple' => ['creative_thinking', 'intuitive_decisions'],
                'brown' => ['practical_reasoning', 'grounded_choices']
            ],
            'problem_solving' => [
                'orange' => ['creative_solutions', 'optimistic_approach'],
                'white' => ['clear_thinking', 'organized_approach'],
                'black' => ['analytical_thinking', 'focused_solutions']
            ]
        ],
        'emotional_responses' => [
            'primary_emotions' => [
                'red' => ['excitement', 'passion', 'anger'],
                'blue' => ['calmness', 'trust', 'sadness'],
                'yellow' => ['happiness', 'optimism', 'anxiety'],
                'green' => ['peace', 'harmony', 'envy']
            ],
            'complex_emotions' => [
                'purple' => ['creativity', 'mystery', 'spirituality'],
                'orange' => ['enthusiasm', 'adventure', 'confidence'],
                'pink' => ['love', 'nurturing', 'compassion'],
                'brown' => ['stability', 'reliability', 'stubbornness']
            ]
        ],
        'behavioral_triggers' => [
            'motivation' => [
                'red' => ['action', 'urgency', 'competition'],
                'orange' => ['socialization', 'confidence', 'adventure'],
                'yellow' => ['learning', 'communication', 'optimism']
            ],
            'relaxation' => [
                'blue' => ['rest', 'reflection', 'peace'],
                'green' => ['balance', 'restoration', 'growth'],
                'purple' => ['meditation', 'introspection', 'creativity']
            ],
            'productivity' => [
                'white' => ['focus', 'efficiency', 'organization'],
                'gray' => ['neutrality', 'professionalism', 'balance'],
                'brown' => ['reliability', 'practicality', 'groundedness']
            ]
        ]
    ];

    /**
     * Analyze psychological effects of a color
     *
     * @param string $color Color to analyze
     * @param array  $context Context parameters
     * @return array Analysis results
     */
    public function analyze_psychological_effects($color, $context = []) {
        $effects = [];
        foreach (self::PSYCHOLOGICAL_EFFECTS as $category => $subcategories) {
            foreach ($subcategories as $subcategory => $colors) {
                if (isset($colors[$color])) {
                    $effects[$category][$subcategory] = $colors[$color];
                }
            }
        }
        return $effects;
    }

    /**
     * Generate psychologically optimized color scheme
     *
     * @param array $target_effects Desired psychological effects
     * @param array $context Context parameters
     * @return array Color scheme
     */
    public function generate_psychological_scheme($target_effects, $context = []) {
        $scheme = [];
        foreach ($target_effects as $effect) {
            foreach (self::PSYCHOLOGICAL_EFFECTS as $category => $subcategories) {
                foreach ($subcategories as $subcategory => $colors) {
                    foreach ($colors as $color => $effects) {
                        if (in_array($effect, $effects)) {
                            $scheme[$effect] = $color;
                            break 3;
                        }
                    }
                }
            }
        }
        return $scheme;
    }

    /**
     * Get emotional impact of a color combination
     *
     * @param array $colors Array of colors to analyze
     * @return array Emotional impact analysis
     */
    public function get_emotional_impact($colors) {
        $impact = [];
        foreach ($colors as $color) {
            if (isset(self::PSYCHOLOGICAL_EFFECTS['emotional_responses']['primary_emotions'][$color])) {
                $impact['primary'][] = self::PSYCHOLOGICAL_EFFECTS['emotional_responses']['primary_emotions'][$color];
            }
            if (isset(self::PSYCHOLOGICAL_EFFECTS['emotional_responses']['complex_emotions'][$color])) {
                $impact['complex'][] = self::PSYCHOLOGICAL_EFFECTS['emotional_responses']['complex_emotions'][$color];
            }
        }
        return $impact;
    }

    /**
     * Get cognitive effects of a color combination
     *
     * @param array $colors Array of colors to analyze
     * @return array Cognitive effects analysis
     */
    public function get_cognitive_effects($colors) {
        $effects = [];
        foreach ($colors as $color) {
            foreach (self::PSYCHOLOGICAL_EFFECTS['cognitive_impacts'] as $aspect => $color_effects) {
                if (isset($color_effects[$color])) {
                    $effects[$aspect][] = $color_effects[$color];
                }
            }
        }
        return $effects;
    }

    /**
     * Get behavioral triggers of a color combination
     *
     * @param array $colors Array of colors to analyze
     * @return array Behavioral triggers analysis
     */
    public function get_behavioral_triggers($colors) {
        $triggers = [];
        foreach ($colors as $color) {
            foreach (self::PSYCHOLOGICAL_EFFECTS['behavioral_triggers'] as $category => $color_triggers) {
                if (isset($color_triggers[$color])) {
                    $triggers[$category][] = $color_triggers[$color];
                }
            }
        }
        return $triggers;
    }
}
