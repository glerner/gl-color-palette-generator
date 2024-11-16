<?php
/**
 * Color Palette Cache
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class Color_Palette_Cache
 *
 * Handles caching of color palettes and related data:
 * - Generated palettes
 * - Analysis results
 * - Rendered output
 * - Export results
 *
 * @since 1.0.0
 */
class Color_Palette_Cache {
    /**
     * Cache group
     * @var string
     */
    private const CACHE_GROUP = 'gl_color_palette';

    /**
     * Cache expiration in seconds
     * @var int
     */
    private const CACHE_EXPIRATION = DAY_IN_SECONDS;

    /**
     * Get a cached item
     *
     * @param string $key Cache key
     * @return mixed|false Cached data or false if not found
     */
    public function get(string $key) {
        return wp_cache_get($this->prepare_key($key), self::CACHE_GROUP);
    }

    /**
     * Set a cached item
     *
     * @param string $key Cache key
     * @param mixed  $data Data to cache
     * @param int    $expiration Optional. Cache expiration in seconds
     * @return bool Success
     */
    public function set(string $key, $data, int $expiration = self::CACHE_EXPIRATION): bool {
        return wp_cache_set(
            $this->prepare_key($key),
            $data,
            self::CACHE_GROUP,
            $expiration
        );
    }

    /**
     * Delete a cached item
     *
     * @param string $key Cache key
     * @return bool Success
     */
    public function delete(string $key): bool {
        return wp_cache_delete($this->prepare_key($key), self::CACHE_GROUP);
    }

    /**
     * Flush all cached items
     *
     * @return bool Success
     */
    public function flush(): bool {
        return wp_cache_flush();
    }

    /**
     * Prepare cache key
     *
     * @param string $key Raw cache key
     * @return string Prepared cache key
     */
    private function prepare_key(string $key): string {
        return sanitize_key('gl_palette_' . $key);
    }
} 
