<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Color_Palette;

/**
 * Color Palette Cache Interface
 *
 * Defines the contract for caching color palette data and computed results.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Cache {
	/**
	 * Stores a palette in cache.
	 *
	 * @param string        $key     Cache key.
	 * @param Color_Palette $palette Palette to cache.
	 * @param int           $ttl     Time to live in seconds.
	 * @return bool True on success.
	 */
	public function set( string $key, Color_Palette $palette, int $ttl = 3600 ): bool;

	/**
	 * Retrieves a palette from cache.
	 *
	 * @param string $key Cache key.
	 * @return Color_Palette|null Cached palette or null if not found.
	 */
	public function get( string $key ): ?Color_Palette;

	/**
	 * Deletes a palette from cache.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success.
	 */
	public function delete( string $key ): bool;

	/**
	 * Checks if a palette exists in cache.
	 *
	 * @param string $key Cache key.
	 * @return bool True if exists.
	 */
	public function has( string $key ): bool;

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
	public function get_stats(): array;
}
