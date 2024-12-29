<?php
/**
 * Color Combination Engine Class
 *
 * Generates and analyzes color combinations based on color theory principles.
 * Takes a base color and creates complementary, analogous, or triadic color schemes
 * while ensuring proper contrast and accessibility standards are met.
 * Uses predefined harmony rules from Color_Constants to maintain consistency.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Analyzer;
use GL_Color_Palette_Generator\Color_Management\Color_Wheel;

/**
 * Class Color_Combination_Engine
 * 
 * Generates and analyzes color combinations based on color theory principles
 */
class Color_Combination_Engine {
    /**
     * Color analyzer instance
     *
     * @var Color_Palette_Analyzer
     */
    protected $color_analyzer;

    /**
     * Color wheel instance for harmony calculations
     *
     * @var Color_Wheel
     */
    protected $color_wheel;

    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    protected $color_utility;

    /**
     * Constructor
     *
     * @param Color_Palette_Analyzer $analyzer Color analyzer instance
     * @param Color_Wheel $color_wheel Color wheel instance
     * @param Color_Utility $color_utility Color utility instance
     */
    public function __construct(
        Color_Palette_Analyzer $analyzer,
        Color_Wheel $color_wheel,
        Color_Utility $color_utility
    ) {
        $this->color_analyzer = $analyzer;
        $this->color_wheel = $color_wheel;
        $this->color_utility = $color_utility;
    }

    /**
     * Generate color combinations based on input parameters
     *
     * @param array $params Generation parameters
     * @return array Generated color combinations
     */
    public function generate_combinations(array $params): array {
        if (!isset($params['base_color'])) {
            return [];
        }

        $base_color = $params['base_color'];
        $harmony_type = $params['harmony_type'] ?? 'complementary';
        $options = $params['options'] ?? [];

        // Generate harmonious colors using Color_Wheel
        $harmonies = $this->color_wheel->calculate_harmonies($base_color, $harmony_type, $options);

        // Process the harmonies into our combination format
        $combinations = [];
        foreach ($harmonies as $harmony) {
            $combinations[] = [
                'colors' => $harmony,
                'metrics' => $this->analyze_combination($harmony)
            ];
        }

        return $combinations;
    }

    /**
     * Analyze color combination
     *
     * @param array $colors Array of colors to analyze
     * @return array Analysis results
     */
    public function analyze_combination(array $colors): array {
        $analysis = [];

        // Calculate harmony score
        $analysis['harmony_score'] = $this->color_wheel->calculate_harmony_score($colors);

        // Calculate contrast ratios between all color pairs
        $analysis['contrast_ratios'] = [];
        for ($i = 0; $i < count($colors); $i++) {
            for ($j = $i + 1; $j < count($colors); $j++) {
                $ratio = $this->color_wheel->calculate_contrast_ratio($colors[$i], $colors[$j]);
                $analysis['contrast_ratios'][] = [
                    'colors' => [$colors[$i], $colors[$j]],
                    'ratio' => $ratio,
                    'passes_wcag' => $ratio >= 4.5 // WCAG AA standard for normal text
                ];
            }
        }

        // Calculate overall color balance
        $analysis['color_balance'] = $this->color_wheel->calculate_color_balance($colors);

        // Calculate vibrance score
        $analysis['vibrance'] = $this->color_wheel->calculate_vibrance_score($colors);

        return $analysis;
    }
}
