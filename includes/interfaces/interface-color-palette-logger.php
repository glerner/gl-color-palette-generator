<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette Logger Interface
 *
 * Defines the contract for logging color palette operations and events.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Logger {
    /**
     * Logs palette operation event.
     *
     * @param string $level Log level ('debug', 'info', 'warning', 'error').
     * @param string $message Event message.
     * @param array $context {
     *     Optional. Additional context.
     *     @type string $palette_id    Associated palette ID.
     *     @type string $operation     Operation type.
     *     @type array  $data          Operation data.
     *     @type array  $metadata      Event metadata.
     *     @type string $source        Event source.
     * }
     * @return array {
     *     Logging results.
     *     @type string $log_id        Unique log entry ID.
     *     @type bool   $logged        Whether event was logged.
     *     @type array  $entry         Complete log entry.
     *     @type array  $metadata      Log metadata.
     * }
     */
    public function log(string $level, string $message, array $context = []): array;

    /**
     * Retrieves log entries.
     *
     * @param array $criteria {
     *     Optional. Search criteria.
     *     @type array  $levels        Log levels to include.
     *     @type string $start_date    Start date for logs.
     *     @type string $end_date      End date for logs.
     *     @type array  $operations    Operation types to include.
     *     @type string $palette_id    Filter by palette ID.
     *     @type int    $limit         Maximum entries to return.
     *     @type int    $offset        Results offset.
     *     @type array  $sort          Sort criteria.
     * }
     * @return array {
     *     Log retrieval results.
     *     @type array  $entries       Retrieved log entries.
     *     @type int    $total         Total matching entries.
     *     @type array  $summary       Log summary statistics.
     *     @type array  $metadata      Query metadata.
     * }
     */
    public function get_logs(array $criteria = []): array;

    /**
     * Analyzes log patterns.
     *
     * @param array $options {
     *     Optional. Analysis options.
     *     @type string $time_frame    Analysis time frame.
     *     @type array  $metrics       Metrics to analyze.
     *     @type array  $group_by      Grouping criteria.
     *     @type bool   $include_trends Include trend analysis.
     * }
     * @return array {
     *     Analysis results.
     *     @type array  $patterns      Identified patterns.
     *     @type array  $statistics    Log statistics.
     *     @type array  $trends        Trend analysis.
     *     @type array  $insights      Generated insights.
     * }
     */
    public function analyze_logs(array $options = []): array;

    /**
     * Manages log maintenance.
     *
     * @param string $action Maintenance action ('rotate', 'archive', 'cleanup').
     * @param array $params {
     *     Optional. Action parameters.
     *     @type int    $retention     Retention period in days.
     *     @type string $archive_path  Archive location.
     *     @type array  $filters       Entry filters for action.
     *     @type bool   $dry_run       Simulate action only.
     * }
     * @return array {
     *     Maintenance results.
     *     @type bool   $success       Operation success status.
     *     @type array  $actions       Performed actions.
     *     @type array  $statistics    Operation statistics.
     *     @type array  $errors        Any errors encountered.
     * }
     */
    public function maintain_logs(string $action, array $params = []): array;
} 
