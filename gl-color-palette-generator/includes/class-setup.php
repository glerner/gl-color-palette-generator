<?php

class GLColorPaletteSetup {
    /**
     * Run the installation/upgrade routines
     */
    public static function install() {
        global $wpdb;

        // Get WordPress database character collate
        $charset_collate = $wpdb->get_charset_collate();

        // SQL for creating/updating tables
        $sql = [];

        // Main color palettes table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}gl_color_palettes` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `colors` json NOT NULL,
            `metadata` json,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) $charset_collate;";

        // Color history table for tracking changes
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}gl_color_history` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `palette_id` bigint(20) unsigned NOT NULL,
            `changes` json NOT NULL,
            `user_id` bigint(20) unsigned NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `palette_id` (`palette_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `fk_gl_history_palette` FOREIGN KEY (`palette_id`)
                REFERENCES `{$wpdb->prefix}gl_color_palettes` (`id`)
                ON DELETE CASCADE
        ) $charset_collate;";

        // User preferences table
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}gl_color_preferences` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL,
            `preferences` json NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`)
        ) $charset_collate;";

        // Required for database updates
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute each table creation SQL
        foreach($sql as $query) {
            dbDelta($query);
        }

        // Store database version for future updates
        update_option('gl_color_palette_db_version', '1.0.0');
    }

    /**
     * Handle plugin updates
     */
    public static function update() {
        $current_version = get_option('gl_color_palette_db_version', '0');

        if (version_compare($current_version, '1.0.0', '<')) {
            self::install();
        }
    }

    /**
     * Get table names
     */
    public static function get_table_names() {
        global $wpdb;
        return [
            'palettes' => $wpdb->prefix . 'gl_color_palettes',
            'history' => $wpdb->prefix . 'gl_color_history',
            'preferences' => $wpdb->prefix . 'gl_color_preferences'
        ];
    }

    /**
     * Clean up tables on uninstall
     */
    public static function uninstall() {
        global $wpdb;

        // Drop tables in correct order (due to foreign key constraints)
        $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}gl_color_history`");
        $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}gl_color_preferences`");
        $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}gl_color_palettes`");

        // Remove options
        delete_option('gl_color_palette_db_version');
        delete_option('gl_color_palette_settings');
    }
}
