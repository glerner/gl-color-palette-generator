<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Storage Interface
 *
 * Defines the contract for storing and retrieving color palettes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteStorage {
    /**
     * Stores palette in database.
     *
     * @param array $palette Palette to store.
     * @param array $options {
     *     Optional. Storage options.
     *     @type string $table         Target table.
     *     @type array  $indexes       Index fields.
     *     @type bool   $versioning    Enable versioning.
     *     @type array  $compression   Data compression.
     *     @type array  $validation    Pre-storage validation.
     * }
     * @return array {
     *     Storage results.
     *     @type int    $id            Stored palette ID.
     *     @type bool   $success       Storage success status.
     *     @type array  $version       Version information.
     *     @type array  $metadata      Storage metadata.
     * }
     */
    public function store_in_db(array $palette, array $options = []): array;

    /**
     * Retrieves palette from database.
     *
     * @param int $id Palette ID.
     * @param array $options {
     *     Optional. Retrieval options.
     *     @type array  $fields        Fields to retrieve.
     *     @type string $version       Specific version.
     *     @type array  $with          Related data to include.
     *     @type array  $transform     Data transformations.
     * }
     * @return array {
     *     Retrieval results.
     *     @type array  $palette       Retrieved palette.
     *     @type array  $version       Version information.
     *     @type array  $related       Related data.
     *     @type array  $metadata      Retrieval metadata.
     * }
     */
    public function retrieve_from_db(int $id, array $options = []): array;

    /**
     * Stores palette in cache.
     *
     * @param string $key Cache key.
     * @param array $palette Palette to cache.
     * @param array $options {
     *     Optional. Cache options.
     *     @type int    $ttl           Time to live.
     *     @type string $group         Cache group.
     *     @type array  $tags          Cache tags.
     *     @type array  $compression   Data compression.
     * }
     * @return array {
     *     Cache results.
     *     @type bool   $cached        Cache success status.
     *     @type string $key           Cache key used.
     *     @type array  $info          Cache information.
     *     @type array  $metadata      Cache metadata.
     * }
     */
    public function store_in_cache(string $key, array $palette, array $options = []): array;

    /**
     * Retrieves palette from cache.
     *
     * @param string $key Cache key.
     * @param array $options {
     *     Optional. Cache options.
     *     @type bool   $refresh       Force refresh.
     *     @type array  $fallback      Fallback options.
     *     @type array  $transform     Data transformations.
     *     @type bool   $metadata      Include metadata.
     * }
     * @return array {
     *     Cache retrieval results.
     *     @type array  $palette       Retrieved palette.
     *     @type bool   $hit           Cache hit status.
     *     @type array  $source        Data source info.
     *     @type array  $metadata      Cache metadata.
     * }
     */
    public function retrieve_from_cache(string $key, array $options = []): array;
}
