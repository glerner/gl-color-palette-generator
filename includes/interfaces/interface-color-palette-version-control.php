<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette Version Control Interface
 *
 * Defines the contract for managing color palette versions and change history.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteVersionControl {
    /**
     * Creates a new palette version.
     *
     * @param array $palette Current palette data.
     * @param array $metadata {
     *     Optional. Version metadata.
     *     @type string $version     Version identifier.
     *     @type string $author      Version author.
     *     @type string $message     Commit message.
     *     @type array  $tags        Version tags.
     *     @type bool   $is_major    Major version change.
     * }
     * @return array {
     *     Version creation results.
     *     @type string $version_id   Unique version identifier.
     *     @type array  $snapshot     Palette snapshot data.
     *     @type array  $metadata     Version metadata.
     *     @type array  $timestamps   Creation timestamps.
     * }
     */
    public function create_version(array $palette, array $metadata = []): array;

    /**
     * Retrieves version history.
     *
     * @param string $palette_id Palette identifier.
     * @param array $options {
     *     Optional. History options.
     *     @type int    $limit        Maximum versions to retrieve.
     *     @type string $start_date   History start date.
     *     @type string $end_date     History end date.
     *     @type array  $filters      Version filters.
     * }
     * @return array {
     *     Version history.
     *     @type array  $versions     Version list and details.
     *     @type array  $timeline     Chronological changes.
     *     @type array  $statistics   Version statistics.
     *     @type array  $metadata     History metadata.
     * }
     */
    public function get_version_history(string $palette_id, array $options = []): array;

    /**
     * Compares two palette versions.
     *
     * @param string $version1_id First version ID.
     * @param string $version2_id Second version ID.
     * @param array $options {
     *     Optional. Comparison options.
     *     @type array  $compare_fields Specific fields to compare.
     *     @type bool   $detailed       Include detailed differences.
     *     @type string $format         Comparison format.
     *     @type bool   $visual_diff    Include visual differences.
     * }
     * @return array {
     *     Version comparison results.
     *     @type array  $differences    Identified differences.
     *     @type array  $statistics     Comparison statistics.
     *     @type array  $visual_diff    Visual difference data.
     *     @type array  $metadata       Comparison metadata.
     * }
     */
    public function compare_versions(string $version1_id, string $version2_id, array $options = []): array;

    /**
     * Restores palette to specific version.
     *
     * @param string $palette_id Palette identifier.
     * @param string $version_id Target version identifier.
     * @param array $options {
     *     Optional. Restore options.
     *     @type bool   $create_backup Create backup before restore.
     *     @type array  $restore_fields Specific fields to restore.
     *     @type bool   $keep_metadata Keep current metadata.
     *     @type string $restore_mode  Restore mode ('full', 'partial').
     * }
     * @return array {
     *     Restore operation results.
     *     @type array  $restored_palette Restored palette data.
     *     @type array  $backup_data     Backup information if created.
     *     @type array  $changes         Applied changes.
     *     @type array  $metadata        Restore operation metadata.
     * }
     */
    public function restore_version(string $palette_id, string $version_id, array $options = []): array;
} 
