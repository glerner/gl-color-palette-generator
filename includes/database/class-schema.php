<?php
/**
 * Database Schema Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Database
 */

namespace GL_Color_Palette_Generator\Database;

/**
 * Class Schema
 * Handles database table creation and updates
 */
class Schema {
    /**
     * Create database tables
     *
     * @return void
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Color palettes table
        $table_name = $wpdb->prefix . 'gl_cpg_palettes';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            colors text NOT NULL,
            prompt text,
            provider varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Color schemes table
        $table_schemes = $wpdb->prefix . 'gl_cpg_schemes';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_schemes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            palette_id bigint(20) unsigned NOT NULL,
            name varchar(255) NOT NULL,
            description text,
            colors text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY palette_id (palette_id),
            FOREIGN KEY (palette_id) REFERENCES {$wpdb->prefix}gl_cpg_palettes(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Settings table
        $table_settings = $wpdb->prefix . 'gl_cpg_settings';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_settings (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            setting_name varchar(255) NOT NULL,
            setting_value text NOT NULL,
            autoload varchar(20) DEFAULT 'yes',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_name (setting_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
