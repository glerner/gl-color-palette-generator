<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette History Interface
 *
 * Defines the contract for managing color palette version history.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteHistory {
    /**
     * Records palette version.
     *
     * @param array $palette Palette to version.
     * @param array $options {
     *     Optional. Version options.
     *     @type string $type          Version type.
     *     @type array  $changes       Change details.
     *     @type string $author        Version author.
     *     @type array  $metadata      Version metadata.
     *     @type array  $tags          Version tags.
     * }
     * @return array {
     *     Version results.
     *     @type int    $version_id    Version identifier.
     *     @type array  $version_info  Version information.
     *     @type array  $diff          Version differences.
     *     @type array  $metadata      Version metadata.
     * }
     */
    public function record_version(array $palette, array $options = []): array;

    /**
     * Retrieves version history.
     *
     * @param int $palette_id Palette identifier.
     * @param array $options {
     *     Optional. History options.
     *     @type int    $limit         Maximum versions.
     *     @type array  $filters       History filters.
     *     @type array  $sort          Sort options.
     *     @type array  $include       Additional data.
     * }
     * @return array {
     *     History results.
     *     @type array  $versions      Version list.
     *     @type array  $timeline      Version timeline.
     *     @type array  $statistics    History statistics.
     *     @type array  $metadata      History metadata.
     * }
     */
    public function get_history(int $palette_id, array $options = []): array;

    /**
     * Restores previous version.
     *
     * @param int $palette_id Palette identifier.
     * @param int $version_id Version to restore.
     * @param array $options {
     *     Optional. Restore options.
     *     @type bool   $create_backup Create backup.
     *     @type array  $validate      Validation rules.
     *     @type array  $notify        Notification options.
     *     @type array  $metadata      Restore metadata.
     * }
     * @return array {
     *     Restore results.
     *     @type bool   $success       Restore success.
     *     @type array  $restored      Restored data.
     *     @type array  $backup        Backup information.
     *     @type array  $metadata      Restore metadata.
     * }
     */
    public function restore_version(int $palette_id, int $version_id, array $options = []): array;

    /**
     * Compares palette versions.
     *
     * @param int $palette_id Palette identifier.
     * @param int $version_1 First version ID.
     * @param int $version_2 Second version ID.
     * @param array $options {
     *     Optional. Comparison options.
     *     @type array  $fields        Fields to compare.
     *     @type string $format        Diff format.
     *     @type array  $context       Comparison context.
     *     @type array  $metadata      Comparison metadata.
     * }
     * @return array {
     *     Comparison results.
     *     @type array  $differences   Version differences.
     *     @type array  $summary       Comparison summary.
     *     @type array  $visualize     Visual comparison.
     *     @type array  $metadata      Comparison metadata.
     * }
     */
    public function compare_versions(int $palette_id, int $version_1, int $version_2, array $options = []): array;
} 
