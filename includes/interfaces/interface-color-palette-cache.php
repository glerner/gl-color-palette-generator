<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Cache Interface
 *
 * Defines the contract for caching color palette data and computed results.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteCache {
    /**
     * Stores palette data in cache.
     *
     * @param string $key Cache key.
     * @param array $data Palette data to cache.
     * @param array $options {
     *     Optional. Cache options.
     *     @type int    $ttl           Time to live in seconds.
     *     @type string $group         Cache group identifier.
     *     @type bool   $compress      Compress cached data.
     *     @type array  $tags          Cache tags for grouping.
     * }
     * @return array {
     *     Cache storage results.
     *     @type bool   $cached        Whether data was cached.
     *     @type string $cache_key     Generated cache key.
     *     @type array  $metadata      Cache entry metadata.
     *     @type array  $diagnostics   Cache operation details.
     * }
     */
    public function store(string $key, array $data, array $options = []): array;

    /**
     * Retrieves palette data from cache.
     *
     * @param string $key Cache key.
     * @param array $options {
     *     Optional. Retrieval options.
     *     @type bool   $refresh       Force refresh from source.
     *     @type mixed  $default       Default value if not found.
     *     @type bool   $with_meta     Include cache metadata.
     *     @type array  $transforms    Data transformations.
     * }
     * @return array {
     *     Cache retrieval results.
     *     @type mixed  $data          Retrieved data or default.
     *     @type bool   $found         Whether entry was found.
     *     @type array  $metadata      Cache entry metadata.
     *     @type array  $performance   Retrieval performance data.
     * }
     */
    public function retrieve(string $key, array $options = []): array;

    /**
     * Invalidates cached palette data.
     *
     * @param mixed $identifier Cache key or tag.
     * @param array $options {
     *     Optional. Invalidation options.
     *     @type string $scope         Invalidation scope ('key', 'tag', 'group').
     *     @type bool   $soft_delete   Mark as invalid vs delete.
     *     @type bool   $cascade       Cascade to related entries.
     *     @type array  $conditions    Conditional invalidation rules.
     * }
     * @return array {
     *     Invalidation results.
     *     @type bool   $invalidated   Whether cache was invalidated.
     *     @type int    $affected      Number of affected entries.
     *     @type array  $details       Invalidation details.
     *     @type array  $errors        Any errors encountered.
     * }
     */
    public function invalidate($identifier, array $options = []): array;

    /**
     * Manages cache statistics and health.
     *
     * @param string $operation Operation type ('stats', 'cleanup', 'optimize').
     * @param array $params {
     *     Optional. Operation parameters.
     *     @type array  $metrics       Stats to collect.
     *     @type array  $cleanup_rules Cleanup criteria.
     *     @type array  $optimize_opts Optimization options.
     *     @type bool   $detailed      Include detailed results.
     * }
     * @return array {
     *     Operation results.
     *     @type array  $statistics    Cache statistics.
     *     @type array  $health        Cache health metrics.
     *     @type array  $actions       Performed actions.
     *     @type array  $recommendations Optimization suggestions.
     * }
     */
    public function manage(string $operation, array $params = []): array;
} 
