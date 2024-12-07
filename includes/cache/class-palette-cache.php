<?php
/**
 * Color Palette Cache Handler
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Cache
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Cache;

use GL_Color_Palette_Generator\Core\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Palette_Cache
 * 
 * Handles caching of generated color palettes to improve performance
 * and reduce API calls.
 *
 * @since 1.0.0
 */
class Palette_Cache {
    /**
     * Cache group name
     *
     * @var string
     */
    private const CACHE_GROUP = 'gl_cpg_palettes';

    /**
     * Default cache expiration in seconds (24 hours)
     *
     * @var int
     */
    private const DEFAULT_EXPIRATION = 86400;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->init_cache_group();
    }

    /**
     * Initialize cache group
     *
     * @return void
     */
    private function init_cache_group() {
        wp_cache_add_global_groups([self::CACHE_GROUP]);
    }

    /**
     * Generate cache key for palette
     *
     * @param array $params Parameters used to generate palette
     * @return string Cache key
     */
    private function generate_cache_key(array $params): string {
        return md5(json_encode($params));
    }

    /**
     * Get palette from cache
     *
     * @param array $params Parameters used to generate palette
     * @return array|false Palette data or false if not found
     */
    public function get(array $params) {
        $key = $this->generate_cache_key($params);
        $cached = wp_cache_get($key, self::CACHE_GROUP);

        if ($cached !== false) {
            $this->logger->debug('Cache hit for palette', [
                'params' => $params,
                'key' => $key
            ]);
            return $cached;
        }

        $this->logger->debug('Cache miss for palette', [
            'params' => $params,
            'key' => $key
        ]);
        return false;
    }

    /**
     * Store palette in cache
     *
     * @param array $params Parameters used to generate palette
     * @param array $palette Palette data to cache
     * @param int $expiration Cache expiration in seconds
     * @return bool Success status
     */
    public function set(array $params, array $palette, int $expiration = self::DEFAULT_EXPIRATION): bool {
        $key = $this->generate_cache_key($params);
        $result = wp_cache_set($key, $palette, self::CACHE_GROUP, $expiration);

        $this->logger->debug(
            $result ? 'Successfully cached palette' : 'Failed to cache palette',
            [
                'params' => $params,
                'key' => $key,
                'expiration' => $expiration
            ]
        );

        return $result;
    }

    /**
     * Delete palette from cache
     *
     * @param array $params Parameters used to generate palette
     * @return bool Success status
     */
    public function delete(array $params): bool {
        $key = $this->generate_cache_key($params);
        $result = wp_cache_delete($key, self::CACHE_GROUP);

        $this->logger->debug(
            $result ? 'Successfully deleted palette from cache' : 'Failed to delete palette from cache',
            [
                'params' => $params,
                'key' => $key
            ]
        );

        return $result;
    }

    /**
     * Clear all cached palettes
     *
     * @return bool Success status
     */
    public function clear_all(): bool {
        $result = wp_cache_flush_group(self::CACHE_GROUP);

        $this->logger->debug(
            $result ? 'Successfully cleared all cached palettes' : 'Failed to clear cached palettes'
        );

        return $result;
    }

    /**
     * Check if palette exists in cache
     *
     * @param array $params Parameters used to generate palette
     * @return bool Whether palette exists in cache
     */
    public function exists(array $params): bool {
        $key = $this->generate_cache_key($params);
        return wp_cache_get($key, self::CACHE_GROUP) !== false;
    }
}
