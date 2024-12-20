<?php
/**
 * Color Recommendations Analysis Class
 *
 * Analyzes color combinations and provides intelligent recommendations
 * for improving contrast, accessibility, and visual harmony.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage AI_ML\Analysis
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\AI_ML\Analysis;

use GL_Color_Palette_Generator\Analysis\Color_Analyzer;
use GL_Color_Palette_Generator\Accessibility\Accessibility_Checker;
use GL_Color_Palette_Generator\Color_Management\Color_Harmonizer;
use GL_Color_Palette_Generator\Settings\Settings_Manager;

/**
 * Class Color_Recommendations
 *
 * Provides intelligent color recommendations based on analysis of
 * contrast ratios, accessibility requirements, and color harmony.
 *
 * @since 1.0.0
 */
class Color_Recommendations {
    /**
     * Color analyzer instance
     *
     * @var Color_Analyzer
     * @since 1.0.0
     */
    private $color_analyzer;

    /**
     * Accessibility checker instance
     *
     * @var Accessibility_Checker
     * @since 1.0.0
     */
    private $accessibility_checker;

    /**
     * Color harmonizer instance
     *
     * @var Color_Harmonizer
     * @since 1.0.0
     */
    private $color_harmonizer;

    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     * @since 1.0.0
     */
    private $settings;

    /**
     * Adjustment threshold constants
     */
    private const MINOR_ADJUSTMENT = 0.1;    // 10% change
    private const MODERATE_ADJUSTMENT = 0.2;  // 20% change
    private const MAJOR_ADJUSTMENT = 0.3;    // 30% change

    /**
     * Minimum improvement thresholds
     */
    private const MIN_CONTRAST_IMPROVEMENT = 1.0;
    private const MIN_BRIGHTNESS_IMPROVEMENT = 20;
    private const MIN_DISTINGUISHABILITY_IMPROVEMENT = 0.15;

    /**
     * Constructor
     *
     * Initializes the color analyzer, accessibility checker, color harmonizer, and settings manager.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->color_analyzer = new Color_Analyzer();
        $this->accessibility_checker = new Accessibility_Checker();
        $this->color_harmonizer = new Color_Harmonizer();
        $this->settings = new Settings_Manager();
    }

    /**
     * Generate comprehensive recommendations
     *
     * Analyzes the provided color combination and generates recommendations for improving contrast, accessibility, and visual harmony.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $results Analysis results from the color analyzer.
     * @param array $context Optional context data for generating context-specific recommendations.
     *
     * @return array Comprehensive recommendations for improving the color combination.
     *
     * @since 1.0.0
     */
    public function generate_recommendations($foreground, $background, $results, $context = []) {
        $recommendations = [
            'summary' => $this->generate_summary($results),
            'contrast_improvements' => $this->get_contrast_improvements($foreground, $background, $results),
            'color_blind_alternatives' => $this->get_colorblind_alternatives($foreground, $background, $results),
            'readability_adjustments' => $this->get_readability_adjustments($foreground, $background, $results),
            'harmony_suggestions' => $this->get_harmony_suggestions($foreground, $background, $context),
            'alternative_combinations' => $this->get_alternative_combinations($foreground, $background, $results),
            'priority' => $this->determine_priority($results)
        ];

        // Add specific context-based recommendations
        if (!empty($context)) {
            $recommendations['context_specific'] = $this->get_context_specific_recommendations(
                $foreground,
                $background,
                $results,
                $context
            );
        }

        return $recommendations;
    }

    /**
     * Generate contrast improvement suggestions
     *
     * Analyzes the provided color combination and generates suggestions for improving contrast.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $results Analysis results from the color analyzer.
     *
     * @return array Contrast improvement suggestions.
     *
     * @since 1.0.0
     */
    private function get_contrast_improvements($foreground, $background, $results) {
        $current_contrast = $results['contrast_ratio'];
        $improvements = [];

        // Calculate adjustments needed
        $target_contrast = $this->determine_target_contrast($results);

        if ($current_contrast < $target_contrast) {
            // Try different adjustment strategies
            $improvements['foreground'] = [
                'lighter' => $this->adjust_for_better_contrast($foreground, $background, 'lighter'),
                'darker' => $this->adjust_for_better_contrast($foreground, $background, 'darker'),
                'saturated' => $this->adjust_for_better_contrast($foreground, $background, 'saturated')
            ];

            $improvements['background'] = [
                'lighter' => $this->adjust_for_better_contrast($background, $foreground, 'lighter', true),
                'darker' => $this->adjust_for_better_contrast($background, $foreground, 'darker', true),
                'desaturated' => $this->adjust_for_better_contrast($background, $foreground, 'desaturated', true)
            ];

            // Sort improvements by effectiveness
            $improvements = $this->sort_improvements_by_effectiveness($improvements, $target_contrast);
        }

        return [
            'current_contrast' => $current_contrast,
            'target_contrast' => $target_contrast,
            'improvements' => $improvements,
            'explanation' => $this->explain_contrast_improvements($improvements)
        ];
    }

    /**
     * Generate colorblind-friendly alternatives
     *
     * Analyzes the provided color combination and generates colorblind-friendly alternatives.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $results Analysis results from the color analyzer.
     *
     * @return array Colorblind-friendly alternatives.
     *
     * @since 1.0.0
     */
    private function get_colorblind_alternatives($foreground, $background, $results) {
        $alternatives = [];

        foreach ($results['color_blindness']['issues'] as $type => $issue) {
            $alternatives[$type] = [
                'original' => [
                    'foreground' => $foreground,
                    'background' => $background,
                    'simulated' => [
                        'foreground' => $this->simulate_color_blindness($foreground, $type),
                        'background' => $this->simulate_color_blindness($background, $type)
                    ]
                ],
                'suggestions' => $this->generate_colorblind_safe_pairs($foreground, $background, $type),
                'explanation' => $this->explain_colorblind_adjustments($type, $issue)
            ];
        }

        return $alternatives;
    }

    /**
     * Generate readability adjustments
     *
     * Analyzes the provided color combination and generates readability adjustments.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $results Analysis results from the color analyzer.
     *
     * @return array Readability adjustments.
     *
     * @since 1.0.0
     */
    private function get_readability_adjustments($foreground, $background, $results) {
        $adjustments = [];

        // Check brightness
        if ($results['readability']['brightness_difference'] < 125) {
            $adjustments['brightness'] = $this->suggest_brightness_adjustments(
                $foreground,
                $background,
                $results['readability']['brightness_difference']
            );
        }

        // Check color difference
        if ($results['readability']['color_difference'] < 500) {
            $adjustments['color'] = $this->suggest_color_adjustments(
                $foreground,
                $background,
                $results['readability']['color_difference']
            );
        }

        // Check vibration effects
        if ($results['readability']['vibration_effects']['has_vibration']) {
            $adjustments['vibration'] = $this->suggest_vibration_reduction(
                $foreground,
                $background,
                $results['readability']['vibration_effects']
            );
        }

        return [
            'current_metrics' => $results['readability'],
            'adjustments' => $adjustments,
            'priority_fixes' => $this->prioritize_readability_fixes($adjustments)
        ];
    }

    /**
     * Generate harmony suggestions
     *
     * Analyzes the provided color combination and generates harmony suggestions.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $context Optional context data for generating context-specific harmony suggestions.
     *
     * @return array Harmony suggestions.
     *
     * @since 1.0.0
     */
    private function get_harmony_suggestions($foreground, $background, $context) {
        $harmony_analysis = $this->color_harmonizer->analyze_harmony($foreground, $background);

        return [
            'current_harmony' => $harmony_analysis['harmony_score'],
            'suggestions' => [
                'complementary' => $this->suggest_complementary_adjustments($foreground, $background),
                'analogous' => $this->suggest_analogous_adjustments($foreground, $background),
                'triadic' => $this->suggest_triadic_adjustments($foreground, $background)
            ],
            'context_optimal' => $this->get_context_optimal_harmony($context),
            'improvements' => $this->calculate_harmony_improvements($harmony_analysis, $context)
        ];
    }

    /**
     * Generate alternative color combinations
     *
     * Analyzes the provided color combination and generates alternative color combinations.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $results Analysis results from the color analyzer.
     *
     * @return array Alternative color combinations.
     *
     * @since 1.0.0
     */
    private function get_alternative_combinations($foreground, $background, $results) {
        $alternatives = [];

        // Generate alternatives based on current colors
        $alternatives['similar'] = $this->generate_similar_combinations($foreground, $background);
        $alternatives['contrasting'] = $this->generate_contrasting_combinations($foreground, $background);
        $alternatives['safe'] = $this->generate_safe_combinations($foreground, $background);

        // Sort alternatives by accessibility score
        foreach ($alternatives as &$category) {
            $category = $this->sort_combinations_by_accessibility($category);
        }

        return [
            'combinations' => $alternatives,
            'best_overall' => $this->select_best_alternative($alternatives, $results),
            'explanation' => $this->explain_alternatives($alternatives)
        ];
    }

    /**
     * Generate context-specific recommendations
     *
     * Analyzes the provided color combination and generates context-specific recommendations.
     *
     * @param string $foreground Foreground color in hexadecimal format.
     * @param string $background Background color in hexadecimal format.
     * @param array $results Analysis results from the color analyzer.
     * @param array $context Optional context data for generating context-specific recommendations.
     *
     * @return array Context-specific recommendations.
     *
     * @since 1.0.0
     */
    private function get_context_specific_recommendations($foreground, $background, $results, $context) {
        $recommendations = [];

        // Brand guidelines compliance
        if (isset($context['brand_colors'])) {
            $recommendations['brand'] = $this->check_brand_compliance(
                $foreground,
                $background,
                $context['brand_colors']
            );
        }

        // Usage-specific recommendations
        if (isset($context['usage'])) {
            $recommendations['usage'] = $this->get_usage_specific_recommendations(
                $foreground,
                $background,
                $context['usage']
            );
        }

        // Device/platform specific recommendations
        if (isset($context['platforms'])) {
            $recommendations['platform'] = $this->get_platform_specific_recommendations(
                $foreground,
                $background,
                $context['platforms']
            );
        }

        return $recommendations;
    }

    /**
     * Utility methods for color adjustments
     */
    private function adjust_for_better_contrast($color, $reference, $direction, $is_background = false) {
        $lab = $this->color_analyzer->hex_to_lab($color);
        $adjustment = 0;

        switch ($direction) {
            case 'lighter':
                $adjustment = $this->calculate_lightness_adjustment($lab[0], true);
                $lab[0] = min(100, $lab[0] + $adjustment);
                break;
            case 'darker':
                $adjustment = $this->calculate_lightness_adjustment($lab[0], false);
                $lab[0] = max(0, $lab[0] - $adjustment);
                break;
            case 'saturated':
                $adjustment = $this->calculate_saturation_adjustment($lab[1], $lab[2], !$is_background);
                $lab[1] *= (1 + $adjustment);
                $lab[2] *= (1 + $adjustment);
                break;
            case 'desaturated':
                $adjustment = $this->calculate_saturation_adjustment($lab[1], $lab[2], $is_background);
                $lab[1] *= (1 - $adjustment);
                $lab[2] *= (1 - $adjustment);
                break;
        }

        return [
            'color' => $this->color_analyzer->lab_to_hex($lab),
            'adjustment' => $adjustment,
            'contrast_improvement' => $this->calculate_contrast_improvement($lab, $reference)
        ];
    }

    /**
     * Calculate appropriate adjustments
     */
    private function calculate_lightness_adjustment($current_lightness, $increase) {
        if ($increase) {
            return $current_lightness < 50 ? self::MAJOR_ADJUSTMENT : self::MINOR_ADJUSTMENT;
        } else {
            return $current_lightness > 50 ? self::MAJOR_ADJUSTMENT : self::MINOR_ADJUSTMENT;
        }
    }

    private function calculate_saturation_adjustment($a, $b, $increase) {
        $current_saturation = sqrt($a * $a + $b * $b);

        if ($increase) {
            return $current_saturation < 50 ? self::MODERATE_ADJUSTMENT : self::MINOR_ADJUSTMENT;
        } else {
            return $current_saturation > 50 ? self::MODERATE_ADJUSTMENT : self::MINOR_ADJUSTMENT;
        }
    }

    /**
     * Generate explanations
     */
    private function explain_contrast_improvements($improvements) {
        $explanation = [];

        foreach ($improvements as $target => $adjustments) {
            foreach ($adjustments as $type => $result) {
                if ($result['contrast_improvement'] >= self::MIN_CONTRAST_IMPROVEMENT) {
                    $explanation[] = sprintf(
                        __('Adjusting the %s color to be %s improves contrast by %.1f', 'color-palette-generator'),
                        $target,
                        $type,
                        $result['contrast_improvement']
                    );
                }
            }
        }

        return $explanation;
    }
}
