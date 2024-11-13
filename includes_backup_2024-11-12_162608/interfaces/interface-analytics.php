<?php

namespace GLColorPalette\Interfaces;

/**
 * Analytics Interface
 *
 * Defines the contract for tracking and analyzing plugin usage and color data.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Analytics {
    /**
     * Tracks feature usage within the plugin.
     *
     * @param string $feature The feature being used.
     * @param array  $context Additional context about the usage.
     * @return void
     * @throws \RuntimeException If tracking fails.
     */
    public function track_usage(string $feature, array $context = []): void;

    /**
     * Generates an analytics report based on specified criteria.
     *
     * @param array $criteria {
     *     Optional. Array of report criteria.
     *     @type string $start_date Start date for report (Y-m-d format).
     *     @type string $end_date   End date for report (Y-m-d format).
     *     @type array  $features   Specific features to include in report.
     *     @type string $format     Report format ('json', 'csv', 'html').
     * }
     * @return array {
     *     Report data structure.
     *     @type array  $usage_stats    Usage statistics by feature.
     *     @type array  $trends         Usage trends over time.
     *     @type array  $popular_colors Most popular colors/palettes.
     *     @type array  $user_segments  User segment analysis.
     * }
     */
    public function generate_report(array $criteria = []): array;

    /**
     * Retrieves current metrics for dashboard display.
     *
     * @return array {
     *     Current metrics data.
     *     @type int    $total_palettes     Total palettes generated.
     *     @type int    $active_users       Active users in last 30 days.
     *     @type array  $popular_features   Most used features.
     *     @type array  $color_trends      Current color trends.
     * }
     */
    public function get_metrics(): array;

    /**
     * Exports analytics data in specified format.
     *
     * @param string $format Export format ('json', 'csv', 'pdf').
     * @param array  $options Export options and filters.
     * @return string Formatted export data.
     * @throws \InvalidArgumentException If format is unsupported.
     */
    public function export_analytics(string $format, array $options = []): string;
} 
