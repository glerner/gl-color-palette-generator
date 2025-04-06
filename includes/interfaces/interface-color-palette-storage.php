<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_Error;

/**
 * Color Palette Storage Interface
 *
 * Interface for storing and retrieving color palettes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Storage_Interface {
	/**
	 * Store a palette
	 *
	 * @param Color_Palette $palette Palette to store
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function store_palette( Color_Palette $palette ): bool|WP_Error;

	/**
	 * Get a palette by ID
	 *
	 * @param int $id Palette ID
	 * @return Color_Palette|null|WP_Error Palette if found, null if not found, WP_Error on failure
	 */
	public function get_palette( int $id ): Color_Palette|null|WP_Error;

	/**
	 * Delete a palette by ID
	 *
	 * @param int $id Palette ID
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function delete_palette( int $id ): bool|WP_Error;

	/**
	 * List palettes
	 *
	 * @param array $args {
	 *     Optional. Arguments to filter palettes.
	 *
	 *     @type int    $per_page Number of items per page. Default 10.
	 *     @type int    $page     Current page number. Default 1.
	 *     @type string $order    Order direction. Default 'DESC'.
	 *     @type array  $meta     Meta query arguments.
	 * }
	 * @return Color_Palette[]|WP_Error Array of palettes or error
	 */
	public function list_palettes( array $args = array() ): array|WP_Error;

	/**
	 * Count total palettes
	 *
	 * @return int|WP_Error Total number of palettes or error
	 */
	public function count_palettes(): int|WP_Error;

	/**
	 * Search palettes
	 *
	 * @param string $query Search query
	 * @param array  $args {
	 *     Optional. Search arguments.
	 *
	 *     @type string $field Field to search in. Default 'metadata'.
	 *     @type int    $limit Maximum number of results. Default 10.
	 *     @type string $order Order direction. Default 'DESC'.
	 * }
	 * @return Color_Palette[]|WP_Error Array of matching palettes or error
	 */
	public function search_palettes( string $query, array $args = array() ): array|WP_Error;

	/**
	 * Updates a palette.
	 *
	 * @param int           $id      Palette ID.
	 * @param Color_Palette $palette Updated palette.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function update_palette( int $id, Color_Palette $palette ): bool|WP_Error;
}
