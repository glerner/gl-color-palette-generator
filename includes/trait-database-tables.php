<?php
/**
 * Database Tables Trait
 *
 * @package    GL_Color_Palette_Generator
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 */

trait Database_Tables {
    /**
     * Initialize database tables
     */
    protected function init_tables() {
        if (get_option('gl_color_palette_db_version') !== GLCPG_VERSION) {
            $this->create_tables();
            update_option('gl_color_palette_db_version', GLCPG_VERSION);
        }
    }

    /**
     * Create plugin database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = [
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gl_color_palettes (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                colors text NOT NULL,
                created datetime DEFAULT CURRENT_TIMESTAMP,
                modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                user_id bigint(20) unsigned NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gl_color_cache (
                cache_key varchar(255) NOT NULL,
                cache_value longtext NOT NULL,
                expiry datetime NOT NULL,
                PRIMARY KEY  (cache_key)
            ) $charset_collate;"
        ];

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach ($sql as $query) {
            dbDelta($query);
        }
    }
}
