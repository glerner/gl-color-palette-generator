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
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Color_Palette_Cache
 * 
 * Handles caching of color palettes
 */
class Color_Palette_Cache {
    /**
     * Cache group name
     */
    private const CACHE_GROUP = 'gl_color_palettes';

    /**
     * Cache expiration time in seconds
     */
    private const CACHE_EXPIRATION = 86400; // 24 hours

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize cache if needed
        if (function_exists('wp_cache_init')) {
            wp_cache_init();
        }
    }

    /**
     * Set a palette in cache
     *
     * @param string $key Cache key
     * @param Color_Palette $palette Palette to cache
     * @return bool Whether the value was set
     */
    public function set(string $key, Color_Palette $palette): bool {
        return wp_cache_set(
            $key,
            $palette->to_array(),
            self::CACHE_GROUP,
            (int) self::CACHE_EXPIRATION
        );
    }

    /**
     * Get a palette from cache
     *
     * @param string $key Cache key
     * @return Color_Palette|null Cached palette or null if not found
     */
    public function get(string $key): ?Color_Palette {
        $data = wp_cache_get($key, self::CACHE_GROUP);
        if ($data === false || !is_array($data)) {
            return null;
        }
        return new Color_Palette($data);
    }

    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public function get_stats(): array {
        $stats = [
            'hits' => 0,
            'misses' => 0,
            'uptime' => 0,
            'memory_usage' => 0,
            'memory_available' => 0
        ];

        if (function_exists('wp_cache_get_last_changed')) {
            $stats['last_changed'] = wp_cache_get_last_changed(self::CACHE_GROUP);
        }

        return $stats;
    }

    /**
     * Delete a palette from cache
     *
     * @param string $key Cache key
     * @return bool Whether the value was deleted
     */
    public function delete(string $key): bool {
        return wp_cache_delete($key, self::CACHE_GROUP);
    }

    /**
     * Flush all cached palettes
     *
     * @return bool Whether the cache was flushed
     */
    public function flush(): bool {
        return wp_cache_flush();
    }

    /**
     * Get cache expiration time
     *
     * @return int Cache expiration time in seconds
     */
    public function get_expiration(): int {
        return (int) self::CACHE_EXPIRATION;
    }

    /**
     * Get multiple palettes from cache
     *
     * @param array $keys Array of cache keys
     * @return array Array of palettes indexed by key
     */
    public function get_multiple(array $keys): array {
        $results = [];
        foreach ($keys as $key) {
            $palette = $this->get($key);
            if ($palette !== null) {
                $results[$key] = $palette;
            }
        }
        return $results;
    }

    /**
     * Set multiple palettes in cache
     *
     * @param array $palettes Array of palettes indexed by key
     * @return bool Whether all values were set
     */
    public function set_multiple(array $palettes): bool {
        $success = true;
        foreach ($palettes as $key => $palette) {
            if (!$this->set($key, $palette)) {
                $success = false;
            }
        }
        return $success;
    }
}
