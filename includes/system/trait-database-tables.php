<?php
namespace GL_Color_Palette_Generator;

trait GL_Color_Palette_GeneratorDatabaseTables {
	/**
	 * Get table name
	 */
	protected function get_table( $table ) {
		global $wpdb;
		$tables = array(
			'palettes'    => $wpdb->prefix . 'gl_color_palettes',
			'history'     => $wpdb->prefix . 'gl_color_history',
			'preferences' => $wpdb->prefix . 'gl_color_preferences',
		);
		return isset( $tables[ $table ] ) ? $tables[ $table ] : false;
	}
}
