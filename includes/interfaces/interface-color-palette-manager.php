<?php
/**
 * Color Palette Manager Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color palette management
 */
interface Color_Palette_Manager_Interface {
	/**
	 * Create a new palette
	 *
	 * @param array $colors Array of hex colors
	 * @param array $options Palette options
	 * @return int|WP_Error Palette ID or error
	 */
	public function create_palette( $colors, $options = array() );

	/**
	 * Update an existing palette
	 *
	 * @param int   $palette_id Palette ID
	 * @param array $colors Array of hex colors
	 * @param array $options Palette options
	 * @return bool|WP_Error True on success or error
	 */
	public function update_palette( $palette_id, $colors, $options = array() );

	/**
	 * Delete a palette
	 *
	 * @param int $palette_id Palette ID
	 * @return bool|WP_Error True on success or error
	 */
	public function delete_palette( $palette_id );

	/**
	 * Get a palette by ID
	 *
	 * @param int $palette_id Palette ID
	 * @return array|WP_Error Palette data or error
	 */
	public function get_palette( $palette_id );

	/**
	 * Get all palettes
	 *
	 * @param array $args Query arguments
	 * @return array|WP_Error Array of palettes or error
	 */
	public function get_palettes( $args = array() );

	/**
	 * Search palettes
	 *
	 * @param string $query Search query
	 * @param array  $args Search arguments
	 * @return array|WP_Error Array of matching palettes or error
	 */
	public function search_palettes( $query, $args = array() );

	/**
	 * Add color to palette
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $color Color in hex format
	 * @param array  $options Color options
	 * @return bool|WP_Error True on success or error
	 */
	public function add_color( $palette_id, $color, $options = array() );

	/**
	 * Remove color from palette
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $color Color in hex format
	 * @return bool|WP_Error True on success or error
	 */
	public function remove_color( $palette_id, $color );

	/**
	 * Update color in palette
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $old_color Old color in hex format
	 * @param string $new_color New color in hex format
	 * @param array  $options Color options
	 * @return bool|WP_Error True on success or error
	 */
	public function update_color( $palette_id, $old_color, $new_color, $options = array() );

	/**
	 * Reorder colors in palette
	 *
	 * @param int   $palette_id Palette ID
	 * @param array $order New color order
	 * @return bool|WP_Error True on success or error
	 */
	public function reorder_colors( $palette_id, $order );

	/**
	 * Get palette metadata
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $key Optional metadata key
	 * @return mixed|WP_Error Metadata value(s) or error
	 */
	public function get_metadata( $palette_id, $key = '' );

	/**
	 * Update palette metadata
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $key Metadata key
	 * @param mixed  $value Metadata value
	 * @return bool|WP_Error True on success or error
	 */
	public function update_metadata( $palette_id, $key, $value );

	/**
	 * Delete palette metadata
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $key Metadata key
	 * @return bool|WP_Error True on success or error
	 */
	public function delete_metadata( $palette_id, $key );

	/**
	 * Export palette
	 *
	 * @param int    $palette_id Palette ID
	 * @param string $format Export format
	 * @return string|WP_Error Export data or error
	 */
	public function export_palette( $palette_id, $format = 'json' );

	/**
	 * Import palette
	 *
	 * @param string $data Import data
	 * @param string $format Import format
	 * @return int|WP_Error Palette ID or error
	 */
	public function import_palette( $data, $format = 'json' );

	/**
	 * Get palette statistics
	 *
	 * @param int $palette_id Optional palette ID
	 * @return array|WP_Error Statistics data or error
	 */
	public function get_statistics( $palette_id = 0 );
}
