<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

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
interface ColorPaletteCacheInterface {
    /**
     * Stores a palette in cache.
     *
     * @param string       $key     Cache key.
     * @param ColorPalette $palette Palette to cache.
     * @param int          $ttl     Time to live in seconds.
     * @return bool True on success.
     */
    public function set(string $key, ColorPalette $palette, int $ttl = 3600): bool;

    /**
     * Retrieves a palette from cache.
     *
     * @param string $key Cache key.
     * @return ColorPalette|null Cached palette or null if not found.
     */
    public function get(string $key): ?ColorPalette;

    /**
     * Deletes a palette from cache.
     *
     * @param string $key Cache key.
     * @return bool True on success.
     */
    public function delete(string $key): bool;

    /**
     * Checks if a palette exists in cache.
     *
     * @param string $key Cache key.
     * @return bool True if exists.
     */
    public function has(string $key): bool;

    /**
     * Clears all cached palettes.
     *
     * @return bool True on success.
     */
    public function clear(): bool;

    /**
     * Gets cache statistics.
     *
     * @return array Cache stats.
     */
    public function getStats(): array;
}
