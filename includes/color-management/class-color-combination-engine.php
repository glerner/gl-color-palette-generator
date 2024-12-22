<?php
/**
 * Color Combination Engine Class
 * 
 * Generates harmonious color combinations by applying color theory rules.
 * Takes a base color and creates complementary, analogous, or triadic color schemes
 * while ensuring proper contrast and accessibility standards are met.
 * Uses predefined harmony rules from Color_Constants to maintain consistency.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Combination_Engine
 * Handles advanced color combination algorithms and rules
 */
class Color_Combination_Engine {
    /**
     * Color analyzer instance
     *
     * @var Color_Analyzer
     */
    protected $color_analyzer;

    /**
     * Harmony calculator instance
     *
     * @var Harmony_Calculator
     */
    protected $harmony_calculator;

    /**
     * Context evaluator instance
     *
     * @var Context_Evaluator
     */
    protected $context_evaluator;

    /**
     * Get color combination rules from constants
     *
     * @return array Color combination rules
     */
    private function get_combination_rules(): array {
        return [
            'harmony_patterns' => Color_Constants::COLOR_HARMONY_RULES,
            'color_roles' => Color_Constants::COLOR_ROLES,
            'relationships' => Color_Constants::COLOR_ROLE_RELATIONSHIPS
        ];
    }

    /**
     * Apply color combination rules
     *
     * @param array $colors Input colors
     * @param string $pattern Harmony pattern to apply
     * @return array Modified colors
     */
    public function apply_combination_rules(array $colors, string $pattern): array {
        $rules = $this->get_combination_rules();
        $harmony_rules = $rules['harmony_patterns'][$pattern] ?? [];
        
        if (empty($harmony_rules)) {
            return $colors;
        }

        foreach ($colors as $role => &$color) {
            if (isset($harmony_rules[$role])) {
                $color = $this->harmony_calculator->apply_harmony_rule($color, $harmony_rules[$role]);
            }
        }

        return $colors;
    }

    /**
     * Generate optimized color combination
     *
     * @param string $base_color
     * @param array  $parameters
     * @param array  $context
     *
     * @return array
     */
    public function generate_combination($base_color, $parameters = [], $context = []) {
        $combination = [
            'primary_colors' => $this->calculate_primary_colors($base_color, $parameters),
            'accent_colors' => $this->determine_accent_colors($base_color, $parameters),
            'harmony_metrics' => $this->calculate_harmony_metrics($base_color, $parameters),
            'context_adaptations' => $this->apply_context_adaptations($context),
            'application_guidelines' => $this->generate_guidelines($context)
        ];

        return $this->optimize_combination($combination, $context);
    }

    /**
     * Validate and optimize color combination
     *
     * @param array $colors
     * @param array $context
     *
     * @return array
     */
    public function validate_combination($colors, $context = []) {
        return [
            'harmony_score' => $this->calculate_harmony_score($colors),
            'contrast_metrics' => $this->analyze_contrast_relationships($colors),
            'context_suitability' => $this->evaluate_context_fit($colors, $context),
            'optimization_suggestions' => $this->generate_optimization_suggestions($colors, $context)
        ];
    }
}
