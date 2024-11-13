<?php
/**
 * Database Tables Trait
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Trait Database_Tables
 *
 * Handles database table creation and management.
 *
 * @since 1.0.0
 */
trait Database_Tables {
    /**
     * Database version.
     *
     * @var string
     */
    private string $db_version = '1.0.0';

    /**
     * Initialize database tables.
     *
     * @since 1.0.0
     * @return void
     */
    protected function init_tables(): void {
        if ($this->should_update_db()) {
            $this->create_tables();
            $this->update_db_version();
        }
    }

    /**
     * Check if database needs updating.
     *
     * @since 1.0.0
     * @return bool
     */
    private function should_update_db(): bool {
        $installed_version = get_option('gl_color_palette_db_version');
        return $installed_version !== $this->db_version;
    }

    /**
     * Create plugin database tables.
     *
     * @since 1.0.0
     * @return void
     */
    private function create_tables(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        // Color palettes table
        $table_name = $wpdb->prefix . 'gl_color_palettes';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            colors longtext NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta($sql);

        // Color cache table
        $table_name = $wpdb->prefix . 'gl_color_cache';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_value longtext NOT NULL,
            expiration datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY cache_key (cache_key)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Update database version in options.
     *
     * @since 1.0.0
     * @return void
     */
    private function update_db_version(): void {
        update_option('gl_color_palette_db_version', $this->db_version);
    }

    /**
     * Get the database version.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_db_version(): string {
        return $this->db_version;
    }
} 
