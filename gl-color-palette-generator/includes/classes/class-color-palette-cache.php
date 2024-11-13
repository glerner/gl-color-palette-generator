<?php
/**
 * Color Palette Cache Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteCacheInterface;
use GLColorPalette\ColorPalette;

/**
 * Handles color palette caching operations.
 */
class ColorPaletteCache implements ColorPaletteCacheInterface {
    /**
     * Cache group name.
     *
     * @var string
     */
    private string $group = 'color_palettes';

    /**
     * Cache statistics.
     *
     * @var array
     */
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0
    ];

    /**
     * Stores a palette in cache.
     *
     * @param string       $key     Cache key.
     * @param ColorPalette $palette Palette to cache.
     * @param int          $ttl     Time to live in seconds.
     * @return bool True on success.
     */
    public function set(string $key, ColorPalette $palette, int $ttl = 3600): bool {
        $data = [
            'name' => $palette->getName(),
            'colors' => $palette->getColors(),
            'metadata' => $palette->getMetadata()
        ];

        $success = wp_cache_set(
            $this->sanitizeKey($key),
            $data,
            $this->group,
            $ttl
        );

        if ($success) {
            $this->stats['writes']++;
        }

        return $success;
    }

    /**
     * Retrieves a palette from cache.
     *
     * @param string $key Cache key.
     * @return ColorPalette|null Cached palette or null if not found.
     */
    public function get(string $key): ?ColorPalette {
        $data = wp_cache_get($this->sanitizeKey($key), $this->group);

        if ($data === false) {
            $this->stats['misses']++;
            return null;
        }

        $this->stats['hits']++;
        return new ColorPalette($data);
    }

    /**
     * Deletes a palette from cache.
     *
     * @param string $key Cache key.
     * @return bool True on success.
     */
    public function delete(string $key): bool {
        $success = wp_cache_delete($this->sanitizeKey($key), $this->group);

        if ($success) {
            $this->stats['deletes']++;
        }

        return $success;
    }

    /**
     * Checks if a palette exists in cache.
     *
     * @param string $key Cache key.
     * @return bool True if exists.
     */
    public function has(string $key): bool {
        $found = null;
        wp_cache_get($this->sanitizeKey($key), $this->group, false, $found);

        if ($found) {
            $this->stats['hits']++;
        } else {
            $this->stats['misses']++;
        }

        return (bool) $found;
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
    public function getStats(): array {
        return array_merge($this->stats, [
            'hit_ratio' => $this->calculateHitRatio(),
            'total_operations' => $this->calculateTotalOperations()
        ]);
    }

    /**
     * Sanitizes a cache key.
     *
     * @param string $key Raw key.
     * @return string Sanitized key.
     */
    private function sanitizeKey(string $key): string {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }

    /**
     * Calculates cache hit ratio.
     *
     * @return float Hit ratio.
     */
    private function calculateHitRatio(): float {
        $total = $this->stats['hits'] + $this->stats['misses'];
        if ($total === 0) {
            return 0.0;
        }
        return round($this->stats['hits'] / $total, 4);
    }

    /**
     * Calculates total cache operations.
     *
     * @return int Total operations.
     */
    private function calculateTotalOperations(): int {
        return array_sum($this->stats);
    }
} 
