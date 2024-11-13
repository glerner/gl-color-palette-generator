<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Analytics Interface
 *
 * Defines the contract for analyzing color palette usage and performance metrics.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteAnalytics {
    /**
     * Analyzes palette usage patterns.
     *
     * @param string $palette_id Palette identifier.
     * @param array $options {
     *     Optional. Analysis options.
     *     @type string $start_date    Analysis start date.
     *     @type string $end_date      Analysis end date.
     *     @type array  $metrics       Specific metrics to analyze.
     *     @type string $granularity   Data granularity ('daily', 'weekly', 'monthly').
     *     @type array  $segments      Data segmentation criteria.
     * }
     * @return array {
     *     Usage analysis results.
     *     @type array  $usage_stats    Usage statistics over time.
     *     @type array  $color_usage    Individual color usage data.
     *     @type array  $trends         Usage trend analysis.
     *     @type array  $segments       Segmented analysis results.
     * }
     */
    public function analyze_usage(string $palette_id, array $options = []): array;

    /**
     * Generates performance metrics.
     *
     * @param string $palette_id Palette identifier.
     * @param array $metrics {
     *     Optional. Performance metrics to calculate.
     *     @type bool   $accessibility Calculate accessibility scores.
     *     @type bool   $load_time     Calculate load time impact.
     *     @type bool   $consistency   Measure consistency metrics.
     *     @type array  $custom        Custom metric definitions.
     * }
     * @return array {
     *     Performance metrics results.
     *     @type array  $scores        Performance scores.
     *     @type array  $benchmarks    Performance benchmarks.
     *     @type array  $impact        Performance impact analysis.
     *     @type array  $recommendations Improvement recommendations.
     * }
     */
    public function generate_metrics(string $palette_id, array $metrics = []): array;

    /**
     * Tracks color usage events.
     *
     * @param array $event {
     *     Event data.
     *     @type string $palette_id    Associated palette ID.
     *     @type string $color         Used color.
     *     @type string $context       Usage context.
     *     @type string $platform      Platform information.
     *     @type array  $metadata      Additional event metadata.
     * }
     * @return array {
     *     Event tracking results.
     *     @type string $event_id      Unique event identifier.
     *     @type bool   $tracked       Whether event was tracked.
     *     @type array  $metadata      Tracking metadata.
     *     @type array  $analytics     Real-time analytics update.
     * }
     */
    public function track_usage_event(array $event): array;

    /**
     * Generates analytics report.
     *
     * @param string $palette_id Palette identifier.
     * @param array $options {
     *     Optional. Report options.
     *     @type string $format        Report format ('detailed', 'summary').
     *     @type array  $sections      Report sections to include.
     *     @type array  $comparisons   Comparison parameters.
     *     @type bool   $include_viz   Include visualizations.
     * }
     * @return array {
     *     Analytics report data.
     *     @type array  $summary       Executive summary.
     *     @type array  $metrics       Detailed metrics.
     *     @type array  $trends        Trend analysis.
     *     @type array  $visualizations Data visualizations.
     * }
     */
    public function generate_report(string $palette_id, array $options = []): array;
} 
