<?php
/**
 * Color Palette Storage Handler
 *
 * Provides persistent storage for color palettes with support for both WordPress
 * options and custom database tables. Includes caching and migration capabilities.
 *
 * @package GL_Color_Palette_Generator
 * @since   1.0.0
 *
 * @example
 * $storage = new Color_Palette_Storage();
 *
 * / Store a palette
 * $id = $storage->store($palette);
 *
 * / Retrieve a palette
 * $palette = $storage->get($id);
 */

namespace GL_Color_Palette_Generator\Color_Management;

use WP_Error;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Storage_Interface;

/**
 * Class Color_Palette_Storage
 *
 * @since 1.0.0
 */
class Color_Palette_Storage implements Color_Palette_Storage_Interface {
	/**
	 * Option name prefix for WP options storage
	 *
	 * @var string
	 */
	private const OPTION_PREFIX = 'gl_color_palette_';

	/**
	 * Database table name without prefix
	 *
	 * @var string
	 */
	private const TABLE_NAME = 'color_palettes';

	/**
	 * Cache instance
	 *
	 * @var Color_Palette_Cache
	 */
	private Color_Palette_Cache $cache;

	/**
	 * Whether to use database storage instead of options
	 *
	 * @var bool
	 */
	private bool $use_database = false;

	/**
	 * Constructor
	 *
	 * @param Color_Palette_Cache|null $cache Optional. Cache instance.
	 */
	public function __construct( ?Color_Palette_Cache $cache = null ) {
		$this->cache = $cache ?? new Color_Palette_Cache();
	}

	/**
	 * Set storage mode
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_db Whether to use database storage.
	 * @return void
	 */
	public function use_database( bool $use_db ): void {
		$this->use_database = $use_db;
	}

	/**
	 * Store a palette
	 *
	 * @since 1.0.0
	 *
	 * @param Color_Palette $palette Palette to store.
	 * @param string|null   $id      Optional. Existing palette ID to update.
	 * @return string|WP_Error Palette ID on success, WP_Error on failure.
	 */
	public function store( Color_Palette $palette, ?string $id = null ): string|WP_Error {
		$id = $id ?? $palette->get_metadata( 'id' ) ?? wp_generate_uuid4();

		if ( $this->use_database ) {
			return $this->store_in_database( $palette, $id );
		}

		return $this->store_in_options( $palette, $id );
	}

	/**
	 * Store palette in database
	 *
	 * @since 1.0.0
	 *
	 * @param Color_Palette $palette Palette to store.
	 * @param string        $id      Palette ID.
	 * @return string|WP_Error Palette ID on success, WP_Error on failure.
	 */
	private function store_in_database( Color_Palette $palette, string $id ): string|WP_Error {
		global $wpdb;

		$data = array(
			'id'       => $id,
			'colors'   => wp_json_encode( $palette->get_colors() ),
			'metadata' => wp_json_encode(
				array_merge(
					$palette->get_metadata(),
					array( 'id' => $id )
				)
			),
			'modified' => current_time( 'mysql' ),
		);

		$result = $wpdb->replace(
			$wpdb->prefix . self::TABLE_NAME,
			$data,
			array( '%s', '%s', '%s', '%s' )
		);

		if ( $result === false ) {
			return new WP_Error(
				'storage_failed',
				__( 'Failed to store palette in database', 'gl-color-palette-generator' )
			);
		}

		$this->cache->set( $id, $palette );
		return $id;
	}

	/**
	 * Store palette in options
	 *
	 * @since 1.0.0
	 *
	 * @param Color_Palette $palette Palette to store.
	 * @param string        $id      Palette ID.
	 * @return string|WP_Error Palette ID on success, WP_Error on failure.
	 */
	private function store_in_options( Color_Palette $palette, string $id ): string|WP_Error {
		$data = array(
			'colors'   => $palette->get_colors(),
			'metadata' => array_merge(
				$palette->get_metadata(),
				array( 'id' => $id )
			),
			'modified' => current_time( 'mysql' ),
		);

		$stored = update_option( self::OPTION_PREFIX . $id, $data );

		if ( ! $stored ) {
			return new WP_Error(
				'storage_failed',
				__( 'Failed to store palette in options', 'gl-color-palette-generator' )
			);
		}

		$this->cache->set( $id, $palette );
		return $id;
	}

	/**
	 * Retrieve a palette
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Palette ID.
	 * @return Color_Palette|WP_Error Palette on success, WP_Error if not found.
	 */
	public function get( string $id ): Color_Palette|WP_Error {
		// Check cache first
		$cached = $this->cache->get( $id );
		if ( $cached instanceof Color_Palette ) {
			return $cached;
		}

		if ( $this->use_database ) {
			return $this->get_from_database( $id );
		}

		return $this->get_from_options( $id );
	}

	/**
	 * Get palette from database
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Palette ID.
	 * @return Color_Palette|WP_Error Palette on success, WP_Error if not found.
	 */
	private function get_from_database( string $id ): Color_Palette|WP_Error {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT colors, metadata FROM {$wpdb->prefix}" . self::TABLE_NAME . ' WHERE id = %s',
				$id
			)
		);

		if ( ! $row ) {
			return new WP_Error(
				'not_found',
				__( 'Palette not found', 'gl-color-palette-generator' )
			);
		}

		$colors   = json_decode( $row->colors, true );
		$metadata = json_decode( $row->metadata, true );

		$palette = new Color_Palette( $colors, $metadata );
		$this->cache->set( $id, $palette );

		return $palette;
	}

	/**
	 * Get palette from options
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Palette ID.
	 * @return Color_Palette|WP_Error Palette on success, WP_Error if not found.
	 */
	private function get_from_options( string $id ): Color_Palette|WP_Error {
		$data = get_option( self::OPTION_PREFIX . $id );

		if ( ! $data ) {
			return new WP_Error(
				'not_found',
				__( 'Palette not found', 'gl-color-palette-generator' )
			);
		}

		$palette = new Color_Palette( $data['colors'], $data['metadata'] );
		$this->cache->set( $id, $palette );

		return $palette;
	}

	/**
	 * Delete a palette
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Palette ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete( string $id ): bool {
		$this->cache->delete( $id );

		if ( $this->use_database ) {
			return $this->delete_from_database( $id );
		}

		return $this->delete_from_options( $id );
	}

	/**
	 * Delete palette from database
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Palette ID.
	 * @return bool True on success, false on failure.
	 */
	private function delete_from_database( string $id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->prefix . self::TABLE_NAME,
			array( 'id' => $id ),
			array( '%s' )
		);

		return $result !== false;
	}

	/**
	 * Delete palette from options
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Palette ID.
	 * @return bool True on success, false on failure.
	 */
	private function delete_from_options( string $id ): bool {
		return delete_option( self::OPTION_PREFIX . $id );
	}

	/**
	 * List all palettes
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Query arguments.
	 * @return array Array of Color_Palette objects.
	 */
	public function list( array $args = array() ): array {
		if ( $this->use_database ) {
			return $this->list_from_database( $args );
		}

		return $this->list_from_options( $args );
	}

	/**
	 * List palettes from database
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. Arguments to filter palettes.
	 *     @type int    $limit  Maximum number of palettes to return.
	 *     @type int    $offset Offset for pagination.
	 *     @type string $order  Order direction. 'ASC' or 'DESC'.
	 *     @type array  $meta   Meta query parameters.
	 * }
	 * @return array Array of Color_Palette objects.
	 */
	private function list_from_database( array $args = array() ): array {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		$defaults = array(
			'limit'  => 20,
			'offset' => 0,
			'order'  => 'DESC',
			'meta'   => array(),
		);

		$args   = wp_parse_args( $args, $defaults );
		$where  = '1=1';
		$params = array();

		// Handle meta query
		if ( ! empty( $args['meta'] ) ) {
			foreach ( $args['meta'] as $key => $value ) {
				$where   .= ' AND metadata LIKE %s';
				$params[] = "%\"{$key}\":\"{$value}\"%";
			}
		}

		$sql = $wpdb->prepare(
			"SELECT * FROM $table WHERE $where ORDER BY modified {$args['order']} LIMIT %d OFFSET %d",
			array_merge( $params, array( $args['limit'], $args['offset'] ) )
		);

		$results  = $wpdb->get_results( $sql );
		$palettes = array();

		foreach ( $results as $row ) {
			$colors     = json_decode( $row->colors, true );
			$metadata   = json_decode( $row->metadata, true );
			$palettes[] = new Color_Palette( $colors, $metadata );
		}

		return $palettes;
	}

	/**
	 * List palettes from options
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Same as list_from_database().
	 * @return array Array of Color_Palette objects.
	 */
	private function list_from_options( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'limit'  => 20,
			'offset' => 0,
			'order'  => 'DESC',
			'meta'   => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM $wpdb->options
                WHERE option_name LIKE %s
                ORDER BY option_id {$args['order']}
                LIMIT %d OFFSET %d",
				$wpdb->esc_like( self::OPTION_PREFIX ) . '%',
				$args['limit'],
				$args['offset']
			)
		);

		$palettes = array();

		foreach ( $options as $option ) {
			$data = maybe_unserialize( $option->option_value );
			if ( ! empty( $args['meta'] ) ) {
				$match = true;
				foreach ( $args['meta'] as $key => $value ) {
					if ( ! isset( $data['metadata'][ $key ] ) || $data['metadata'][ $key ] !== $value ) {
						$match = false;
						break;
					}
				}
				if ( ! $match ) {
					continue;
				}
			}
			$palettes[] = new Color_Palette( $data['colors'], $data['metadata'] );
		}

		return $palettes;
	}

	/**
	 * Search palettes
	 *
	 * @since 1.0.0
	 *
	 * @param string $query Search query.
	 * @param array  $args {
	 *     Optional. Search arguments.
	 *     @type string $field Field to search in ('name', 'metadata', 'colors').
	 *     @type int    $limit Maximum results to return.
	 *     @type string $order Order direction ('ASC' or 'DESC').
	 * }
	 * @return array Array of Color_Palette objects.
	 */
	public function search( string $query, array $args = array() ): array {
		if ( $this->use_database ) {
			return $this->search_in_database( $query, $args );
		}
		return $this->search_in_options( $query, $args );
	}

	/**
	 * Search palettes in database
	 *
	 * @since 1.0.0
	 *
	 * @param string $query Search query.
	 * @param array  $args  Search arguments.
	 * @return array Array of Color_Palette objects.
	 */
	private function search_in_database( string $query, array $args = array() ): array {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		$defaults = array(
			'field' => 'metadata',
			'limit' => 20,
			'order' => 'DESC',
		);

		$args        = wp_parse_args( $args, $defaults );
		$search_term = '%' . $wpdb->esc_like( $query ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table WHERE {$args['field']} LIKE %s
            ORDER BY modified {$args['order']}
            LIMIT %d",
			$search_term,
			$args['limit']
		);

		$results  = $wpdb->get_results( $sql );
		$palettes = array();

		foreach ( $results as $row ) {
			$colors     = json_decode( $row->colors, true );
			$metadata   = json_decode( $row->metadata, true );
			$palettes[] = new Color_Palette( $colors, $metadata );
		}

		return $palettes;
	}

	/**
	 * Search palettes in options
	 *
	 * @since 1.0.0
	 *
	 * @param string $query Search query.
	 * @param array  $args  Search arguments.
	 * @return array Array of Color_Palette objects.
	 */
	private function search_in_options( string $query, array $args = array() ): array {
		$defaults = array(
			'field' => 'metadata',
			'limit' => 20,
			'order' => 'DESC',
		);

		$args     = wp_parse_args( $args, $defaults );
		$palettes = array();
		$count    = 0;

		$all_palettes = $this->list( array( 'limit' => PHP_INT_MAX ) );

		foreach ( $all_palettes as $palette ) {
			if ( $count >= $args['limit'] ) {
				break;
			}

			$match = false;
			switch ( $args['field'] ) {
				case 'name':
					$name  = $palette->get_metadata( 'name' ) ?? '';
					$match = stripos( $name, $query ) !== false;
					break;

				case 'metadata':
					$metadata = json_encode( $palette->get_metadata() );
					$match    = stripos( $metadata, $query ) !== false;
					break;

				case 'colors':
					$colors = json_encode( $palette->get_colors() );
					$match  = stripos( $colors, $query ) !== false;
					break;
			}

			if ( $match ) {
				$palettes[] = $palette;
				++$count;
			}
		}

		return $palettes;
	}

	/**
	 * Count total palettes
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta Optional. Metadata to filter by.
	 * @return int Total number of palettes.
	 */
	public function count( array $meta = array() ): int {
		if ( $this->use_database ) {
			return $this->count_in_database( $meta );
		}
		return $this->count_in_options( $meta );
	}

	/**
	 * Count palettes in database
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta Metadata to filter by.
	 * @return int Total number of palettes.
	 */
	private function count_in_database( array $meta = array() ): int {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		$where  = '1=1';
		$params = array();

		if ( ! empty( $meta ) ) {
			foreach ( $meta as $key => $value ) {
				$where   .= ' AND metadata LIKE %s';
				$params[] = "%\"{$key}\":\"{$value}\"%";
			}
		}

		$sql = "SELECT COUNT(*) FROM $table WHERE $where";
		if ( ! empty( $params ) ) {
			$sql = $wpdb->prepare( $sql, $params );
		}

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Count palettes in options
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta Metadata to filter by.
	 * @return int Total number of palettes.
	 */
	private function count_in_options( array $meta = array() ): int {
		if ( empty( $meta ) ) {
			global $wpdb;
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s",
					$wpdb->esc_like( self::OPTION_PREFIX ) . '%'
				)
			);
		}

		// If we have meta filters, we need to count matching palettes
		$palettes = $this->list( array( 'limit' => PHP_INT_MAX ) );
		$count    = 0;

		foreach ( $palettes as $palette ) {
			$matches = true;
			foreach ( $meta as $key => $value ) {
				if ( $palette->get_metadata( $key ) !== $value ) {
					$matches = false;
					break;
				}
			}
			if ( $matches ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Save a palette to the database
	 *
	 * @param array  $colors Array of colors in the palette
	 * @param string $prompt Optional. The prompt used to generate the palette
	 * @param array  $metadata Optional. Additional metadata about the palette
	 * @return int|false The ID of the saved palette or false on failure
	 */
	public function save_palette( array $colors, string $prompt = '', array $metadata = array() ): int|false {
		global $wpdb;

		$data = array(
			'colors'     => maybe_serialize( $colors ),
			'prompt'     => $prompt,
			'metadata'   => maybe_serialize( $metadata ),
			'created_at' => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( self::TABLE_NAME, $data );
		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get a palette by its ID
	 *
	 * @param int $id Palette ID
	 * @return array|null Palette data or null if not found
	 */
	public function get_palette( int $id ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %d', $id ),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return array(
			'id'         => $row['id'],
			'colors'     => maybe_unserialize( $row['colors'] ),
			'prompt'     => $row['prompt'],
			'metadata'   => maybe_unserialize( $row['metadata'] ),
			'created_at' => $row['created_at'],
		);
	}

	/**
	 * Get palettes created within a date range
	 *
	 * @param string $start_date Start date in Y-m-d format
	 * @param string $end_date End date in Y-m-d format
	 * @return array Array of palettes
	 */
	public function get_palettes_by_date_range( string $start_date, string $end_date ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_NAME . ' WHERE DATE(created_at) BETWEEN %s AND %s ORDER BY created_at DESC',
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		return array_map(
			function ( $row ) {
				return array(
					'id'         => $row['id'],
					'colors'     => maybe_unserialize( $row['colors'] ),
					'prompt'     => $row['prompt'],
					'metadata'   => maybe_unserialize( $row['metadata'] ),
					'created_at' => $row['created_at'],
				);
			},
			$rows
		);
	}

	/**
	 * Get recent palettes
	 *
	 * @param int $limit Number of palettes to return
	 * @return array Array of palettes
	 */
	public function get_recent_palettes( int $limit = 10 ): array {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_NAME . ' ORDER BY created_at DESC LIMIT %d',
				$limit
			),
			ARRAY_A
		);

		return array_map(
			function ( $row ) {
				return array(
					'id'         => $row['id'],
					'colors'     => maybe_unserialize( $row['colors'] ),
					'prompt'     => $row['prompt'],
					'metadata'   => maybe_unserialize( $row['metadata'] ),
					'created_at' => $row['created_at'],
				);
			},
			$rows
		);
	}

	/**
	 * Delete a palette
	 *
	 * @param int $id Palette ID
	 * @return bool True on success, false on failure
	 */
	public function delete_palette( int $id ): bool {
		global $wpdb;

		return (bool) $wpdb->delete(
			self::TABLE_NAME,
			array( 'id' => $id ),
			array( '%d' )
		);
	}
}
