<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Manager Interface
 *
 * Defines the contract for managing collections of color palettes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteManager {
    /**
     * Creates new palette.
     *
     * @param array $palette Palette data.
     * @param array $options {
     *     Optional. Creation options.
     *     @type array  $validation    Validation rules.
     *     @type array  $defaults      Default values.
     *     @type array  $metadata      Creation metadata.
     * }
     * @return array {
     *     Creation results.
     *     @type array  $palette       Created palette.
     *     @type array  $validation    Validation results.
     *     @type array  $metadata      Creation metadata.
     * }
     */
    public function create_palette(array $palette, array $options = []): array;

    /**
     * Updates existing palette.
     *
     * @param string $id Palette identifier.
     * @param array $updates Update data.
     * @param array $options {
     *     Optional. Update options.
     *     @type array  $validation    Validation rules.
     *     @type array  $merge         Merge strategy.
     *     @type array  $metadata      Update metadata.
     * }
     * @return array {
     *     Update results.
     *     @type array  $palette       Updated palette.
     *     @type array  $changes       Applied changes.
     *     @type array  $metadata      Update metadata.
     * }
     */
    public function update_palette(string $id, array $updates, array $options = []): array;

    /**
     * Deletes palette.
     *
     * @param string $id Palette identifier.
     * @param array $options {
     *     Optional. Deletion options.
     *     @type bool   $force         Force deletion.
     *     @type array  $backup        Backup options.
     *     @type array  $metadata      Deletion metadata.
     * }
     * @return array {
     *     Deletion results.
     *     @type bool   $success       Deletion status.
     *     @type array  $backup        Backup data.
     *     @type array  $metadata      Deletion metadata.
     * }
     */
    public function delete_palette(string $id, array $options = []): array;

    /**
     * Lists palettes.
     *
     * @param array $filters Filter criteria.
     * @param array $options {
     *     Optional. List options.
     *     @type array  $pagination    Page options.
     *     @type array  $sorting       Sort options.
     *     @type array  $metadata      List metadata.
     * }
     * @return array {
     *     List results.
     *     @type array  $palettes      Found palettes.
     *     @type array  $pagination    Page details.
     *     @type array  $metadata      List metadata.
     * }
     */
    public function list_palettes(array $filters = [], array $options = []): array;

    /**
     * Gets palette by ID.
     *
     * @param string $id Palette identifier.
     * @param array $options {
     *     Optional. Retrieval options.
     *     @type array  $fields        Field selection.
     *     @type array  $relations     Related data.
     *     @type array  $metadata      Retrieval metadata.
     * }
     * @return array {
     *     Retrieval results.
     *     @type array  $palette       Found palette.
     *     @type array  $relations     Related data.
     *     @type array  $metadata      Retrieval metadata.
     * }
     */
    public function get_palette(string $id, array $options = []): array;

    /**
     * Searches palettes.
     *
     * @param array $criteria Search criteria.
     * @param array $options {
     *     Optional. Search options.
     *     @type array  $pagination    Page options.
     *     @type array  $sorting       Sort options.
     *     @type array  $metadata      Search metadata.
     * }
     * @return array {
     *     Search results.
     *     @type array  $palettes      Found palettes.
     *     @type array  $pagination    Page details.
     *     @type array  $metadata      Search metadata.
     * }
     */
    public function search_palettes(array $criteria, array $options = []): array;
} 
