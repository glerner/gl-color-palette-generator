<?php
/**
 * Color Palette Cache Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Cache as Color_Palette_Cache_Interface;

/**
 * Class Color_Palette_Cache
 * Handles caching of color palettes and related data
 */
class Color_Palette_Cache implements Color_Palette_Cache_Interface {
    /**
     * Cache group name
     */
    private const CACHE_GROUP = 'gl_color_palette_generator';

    /**
     * Default cache expiration in seconds (24 hours)
     */
    private const DEFAULT_EXPIRATION = 86400;

    /**
     * Cache key prefix
     */
    private const KEY_PREFIX = 'gl_cpg_';

    /**
     * Cache instance
     *
     * @var \WP_Object_Cache
     */
    private $wp_cache;

    /**
     * Constructor
     */
    public function __construct() {
        global $wp_object_cache;
        $this->wp_cache = $wp_object_cache;
    }

    /**
     * Stores a palette in cache.
     *
     * @param string        $key     Cache key.
     * @param Color_Palette $palette Palette to cache.
     * @param int          $ttl     Time to live in seconds.
     * @return bool True on success.
     */
    public function set(string $key, Color_Palette $palette, int $ttl = 3600): bool {
        return wp_cache_set(
            self::KEY_PREFIX . $key,
            $palette,
            self::CACHE_GROUP,
            $ttl
        );
    }

    /**
     * Retrieves a palette from cache.
     *
     * @param string $key Cache key.
     * @return Color_Palette|null Cached palette or null if not found.
     */
    public function get(string $key): ?Color_Palette {
        $result = wp_cache_get(self::KEY_PREFIX . $key, self::CACHE_GROUP);
        return $result instanceof Color_Palette ? $result : null;
    }

    /**
     * Deletes a palette from cache.
     *
     * @param string $key Cache key.
     * @return bool True on success.
     */
    public function delete(string $key): bool {
        return wp_cache_delete(self::KEY_PREFIX . $key, self::CACHE_GROUP);
    }

    /**
     * Checks if a palette exists in cache.
     *
     * @param string $key Cache key.
     * @return bool True if exists.
     */
    public function has(string $key): bool {
        return wp_cache_get(self::KEY_PREFIX . $key, self::CACHE_GROUP) !== false;
    }

    /**
     * Clears all cached palettes.
     *
     * @return bool True on success.
     */
    public function clear(): bool {
        return wp_cache_flush();
    }

    /**
     * Gets cache statistics.
     *
     * @return array Cache stats.
     */
    public function get_stats(): array {
        return [
            'hits' => wp_cache_get_stats('get'),
            'misses' => wp_cache_get_stats('miss'),
            'uptime' => wp_cache_get_stats('uptime'),
            'memory_usage' => wp_cache_get_stats('memory'),
            'cache_size' => wp_cache_get_stats('size')
        ];
    }

    /**
     * Get multiple palettes from cache
     *
     * @param array $keys Array of cache keys.
     * @return array Array of palettes indexed by key.
     */
    public function get_multiple(array $keys): array {
        $prefixed_keys = array_map(
            function($key) {
                return self::KEY_PREFIX . $key;
            },
            $keys
        );

        $results = wp_cache_get_multiple($prefixed_keys, self::CACHE_GROUP);

        // Remove prefix from keys in results
        $palettes = [];
        foreach ($results as $prefixed_key => $value) {
            $original_key = str_replace(self::KEY_PREFIX, '', $prefixed_key);
            if ($value instanceof Color_Palette) {
                $palettes[$original_key] = $value;
            }
        }

        return $palettes;
    }

    /**
     * Get palette from cache
     *
     * @param string $key Cache key.
     * @return array|false Palette data or false if not found.
     */
    public function get_palette($key) {
        return wp_cache_get(self::KEY_PREFIX . $key, self::CACHE_GROUP);
    }

    /**
     * Set palette in cache
     *
     * @param string $key Cache key.
     * @param array  $palette Palette data.
     * @param int    $expiration Optional. Cache expiration in seconds.
     * @return bool True on success, false on failure.
     */
    public function set_palette($key, $palette, $expiration = self::DEFAULT_EXPIRATION) {
        return wp_cache_set(
            self::KEY_PREFIX . $key,
            $palette,
            self::CACHE_GROUP,
            $expiration
        );
    }

    /**
     * Delete palette from cache
     *
     * @param string $key Cache key.
     * @return bool True on success, false on failure.
     */
    public function delete_palette($key) {
        return wp_cache_delete(self::KEY_PREFIX . $key, self::CACHE_GROUP);
    }

    /**
     * Get multiple palettes from cache
     *
     * @param array $keys Array of cache keys.
     * @return array Array of palettes indexed by key.
     */
    public function get_multiple_palettes($keys) {
        $prefixed_keys = array_map(
            function($key) {
                return self::KEY_PREFIX . $key;
            },
            $keys
        );

        $results = wp_cache_get_multiple($prefixed_keys, self::CACHE_GROUP);

        // Remove prefix from keys in results
        $palettes = [];
        foreach ($results as $key => $value) {
            $original_key = str_replace(self::KEY_PREFIX, '', $key);
            $palettes[$original_key] = $value;
        }

        return $palettes;
    }

    /**
     * Set multiple palettes in cache
     *
     * @param array $palettes Array of palettes indexed by key.
     * @param int   $expiration Optional. Cache expiration in seconds.
     * @return bool True if all palettes were cached, false otherwise.
     */
    public function set_multiple_palettes($palettes, $expiration = self::DEFAULT_EXPIRATION) {
        $success = true;
        foreach ($palettes as $key => $palette) {
            if (!$this->set_palette($key, $palette, $expiration)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Delete multiple palettes from cache
     *
     * @param array $keys Array of cache keys.
     * @return bool True if all palettes were deleted, false otherwise.
     */
    public function delete_multiple_palettes($keys) {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete_palette($key)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Clear all cached palettes
     *
     * @return bool True on success, false on failure.
     */
    public function clear_cache() {
        return wp_cache_flush_group(self::CACHE_GROUP);
    }

    /**
     * Get cache statistics
     *
     * @return array Cache statistics.
     */
    public function get_cache_stats() {
        return [
            'hits' => wp_cache_get_stats('get_hits', self::CACHE_GROUP),
            'misses' => wp_cache_get_stats('get_misses', self::CACHE_GROUP),
            'data' => wp_cache_get_stats('get_data', self::CACHE_GROUP),
            'size' => $this->get_cache_size()
        ];
    }

    /**
     * Get approximate cache size
     *
     * @return int Approximate size in bytes.
     */
    private function get_cache_size() {
        $size = 0;
        $keys = $this->get_all_cache_keys();

        foreach ($keys as $key) {
            $data = wp_cache_get($key, self::CACHE_GROUP);
            if ($data !== false) {
                $size += strlen(serialize($data));
            }
        }

        return $size;
    }

    /**
     * Get all cache keys for this group
     *
     * @return array Array of cache keys.
     */
    private function get_all_cache_keys() {
        global $wp_object_cache;

        if (method_exists($wp_object_cache, 'get_group_keys')) {
            return $wp_object_cache->get_group_keys(self::CACHE_GROUP);
        }

        // Fallback if method doesn't exist
        $keys = [];
        if (isset($wp_object_cache->cache[self::CACHE_GROUP])) {
            $keys = array_keys($wp_object_cache->cache[self::CACHE_GROUP]);
        }

        return $keys;
    }

    /**
     * Check if a palette exists in cache
     *
     * @param string $key Cache key.
     * @return bool True if palette exists, false otherwise.
     */
    public function has_palette($key) {
        return wp_cache_get(self::KEY_PREFIX . $key, self::CACHE_GROUP) !== false;
    }

    /**
     * Get palette with metadata
     *
     * @param string $key Cache key.
     * @return array|false Palette data with metadata or false if not found.
     */
    public function get_palette_with_metadata($key) {
        $data = $this->get_palette($key);
        if ($data === false) {
            return false;
        }

        return [
            'palette' => $data,
            'metadata' => [
                'cached_at' => wp_cache_get_last_changed(self::CACHE_GROUP),
                'expires_at' => wp_cache_get_last_changed(self::CACHE_GROUP) + self::DEFAULT_EXPIRATION
            ]
        ];
    }

    /**
     * Set palette with version tag
     *
     * @param string $key Cache key.
     * @param array  $palette Palette data.
     * @param string $version Version tag.
     * @param int    $expiration Optional. Cache expiration in seconds.
     * @return bool True on success, false on failure.
     */
    public function set_palette_versioned($key, $palette, $version, $expiration = self::DEFAULT_EXPIRATION) {
        $data = [
            'palette' => $palette,
            'version' => $version
        ];

        return $this->set_palette($key, $data, $expiration);
    }

    /**
     * Get palette if version matches
     *
     * @param string $key Cache key.
     * @param string $version Version to check against.
     * @return array|false Palette data or false if not found or version mismatch.
     */
    public function get_palette_if_version_matches($key, $version) {
        $data = $this->get_palette($key);
        if ($data === false || !isset($data['version']) || $data['version'] !== $version) {
            return false;
        }

        return $data['palette'];
    }
}
