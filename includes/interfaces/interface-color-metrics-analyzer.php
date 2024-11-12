<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Metrics Analyzer Interface
 *
 * Defines the contract for analyzing and measuring color metrics and relationships.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorMetricsAnalyzer {
    /**
     * Analyzes color relationships within a palette.
     *
     * @param array $colors Array of colors to analyze.
     * @param array $options {
     *     Optional. Analysis options.
     *     @type bool   $include_contrast    Include contrast analysis.
     *     @type bool   $include_harmony     Include harmony analysis.
     *     @type bool   $include_psychology  Include color psychology.
     *     @type string $color_space        Color space for analysis ('RGB', 'LAB', 'HSL').
     * }
     * @return array {
     *     Analysis results.
     *     @type array  $relationships   Color relationship metrics.
     *     @type array  $contrast_matrix Contrast ratio matrix.
     *     @type array  $harmony_scores  Harmony analysis scores.
     *     @type array  $psychology      Psychological impact analysis.
     *     @type array  $statistics      Statistical color metrics.
     * }
     */
    public function analyze_relationships(array $colors, array $options = []): array;

    /**
     * Calculates color distribution metrics.
     *
     * @param array $colors Colors to analyze.
     * @return array {
     *     Distribution metrics.
     *     @type array  $hue_distribution     Distribution across hue spectrum.
     *     @type array  $saturation_spread    Saturation distribution.
     *     @type array  $lightness_balance    Lightness distribution.
     *     @type array  $color_clusters       Identified color clusters.
     *     @type float  $coverage_score       Color space coverage score.
     * }
     */
    public function calculate_distribution(array $colors): array;

    /**
     * Measures perceptual color differences.
     *
     * @param string $color1 First color.
     * @param string $color2 Second color.
     * @param array $options {
     *     Optional. Measurement options.
     *     @type string $algorithm    Difference algorithm ('CIE76', 'CIE94', 'CIEDE2000').
     *     @type string $color_space  Color space for comparison.
     *     @type bool   $weighted     Use weighted calculations.
     * }
     * @return array {
     *     Difference measurements.
     *     @type float  $delta_e        Color difference value.
     *     @type array  $components     Component-wise differences.
     *     @type bool   $perceptible    Whether difference is perceptible.
     *     @type array  $analysis       Detailed difference analysis.
     * }
     */
    public function measure_difference(string $color1, string $color2, array $options = []): array;

    /**
     * Generates color metrics report.
     *
     * @param array $colors Colors to analyze.
     * @param array $criteria {
     *     Optional. Report criteria.
     *     @type array  $metrics        Specific metrics to include.
     *     @type string $format         Report format ('detailed', 'summary').
     *     @type bool   $include_charts Include visual representations.
     *     @type array  $benchmarks     Comparison benchmarks.
     * }
     * @return array {
     *     Metrics report.
     *     @type array  $summary        Key metrics summary.
     *     @type array  $detailed_data  Detailed metrics data.
     *     @type array  $visualizations Chart/graph data.
     *     @type array  $comparisons    Benchmark comparisons.
     *     @type array  $recommendations Improvement suggestions.
     * }
     */
    public function generate_metrics_report(array $colors, array $criteria = []): array;
} 
