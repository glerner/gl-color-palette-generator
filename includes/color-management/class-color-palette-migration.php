<?php
/**
 * Color Palette Migration Handler
 *
 * Handles migration of color palettes between storage methods and
 * manages database schema updates.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use WP_Error;

/**
 * Class Color_Palette_Migration
 *
 * @since 1.0.0
 */
class Color_Palette_Migration {
	/**
	 * Storage instance
	 *
	 * @var Color_Palette_Storage
	 */
	private Color_Palette_Storage $storage;

	/**
	 * Constructor
	 *
	 * @param Color_Palette_Storage $storage Storage instance.
	 */
	public function __construct( Color_Palette_Storage $storage ) {
		$this->storage = $storage;
	}

	/**
	 * Migrate palettes from options to database
	 *
	 * @since 1.0.0
	 *
	 * @return array{success: int, failed: int} Migration statistics.
	 */
	public function migrate_to_database(): array {
		global $wpdb;
		$stats = array(
			'success' => 0,
			'failed'  => 0,
		);

		// Ensure table exists
		$this->create_table();

		// Get all palette options
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( 'gl_color_palette_' ) . '%'
			)
		);

		foreach ( $options as $option ) {
			$id   = str_replace( 'gl_color_palette_', '', $option->option_name );
			$data = maybe_unserialize( $option->option_value );

			if ( ! is_array( $data ) || empty( $data['colors'] ) ) {
				++$stats['failed'];
				continue;
			}

			$palette = new Color_Palette( $data['colors'], $data['metadata'] ?? array() );

			// Temporarily enable database storage
			$this->storage->use_database( true );
			$result = $this->storage->store( $palette, $id );
			$this->storage->use_database( false );

			if ( is_wp_error( $result ) ) {
				++$stats['failed'];
			} else {
				++$stats['success'];
				delete_option( $option->option_name );
			}
		}

		return $stats;
	}

	/**
	 * Create or update the database table
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function create_table(): bool {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'color_palettes';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id varchar(36) NOT NULL,
            colors text NOT NULL,
            metadata text NOT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP,
            modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY modified (modified)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		return (bool) dbDelta( $sql );
	}

	/**
	 * Check if migration is needed
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if migration is needed.
	 */
	public function needs_migration(): bool {
		global $wpdb;

		// Check if we have any palettes in options
		$has_options = (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( 'gl_color_palette_' ) . '%'
			)
		);

		// Check if table exists and is empty
		$table_name   = $wpdb->prefix . 'color_palettes';
		$table_exists = (bool) $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->esc_like( $table_name )
			)
		);

		if ( ! $table_exists ) {
			return $has_options;
		}

		$has_table_data = (bool) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		return $has_options && ! $has_table_data;
	}
}
