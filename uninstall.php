<?php

/ If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * GL Color Palette Generator Uninstall Class
 */
class GLColorPaletteUninstall {
    /**
     * Run the uninstaller
     */
    public static function uninstall() {
        self::delete_tables();
        self::delete_options();
        self::delete_transients();
        self::delete_files();
        self::cleanup_roles_capabilities();
    }

    /**
     * Delete plugin tables
     */
    private static function delete_tables() {
        global $wpdb;

        / Drop tables in correct order (due to foreign key constraints)
        $tables = [
            $wpdb->prefix . 'gl_color_history',
            $wpdb->prefix . 'gl_color_preferences',
            $wpdb->prefix . 'gl_color_palettes'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$table}`");
        }
    }

    /**
     * Delete plugin options
     */
    private static function delete_options() {
        / Delete main options
        delete_option('gl_color_palette_db_version');
        delete_option('gl_color_palette_settings');
        delete_option('gl_color_palette_last_sync');
        delete_option('gl_color_palette_api_key');

        / Delete all options with our prefix
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE %s",
                'gl_color_palette_%'
            )
        );
    }

    /**
     * Delete transients
     */
    private static function delete_transients() {
        global $wpdb;

        / Delete all transients and their timeout entries
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_gl_color_%',
                '_transient_timeout_gl_color_%'
            )
        );

        / Delete network transients if in multisite
        if (is_multisite()) {
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$wpdb->sitemeta}` WHERE meta_key LIKE %s OR meta_key LIKE %s",
                    '_site_transient_gl_color_%',
                    '_site_transient_timeout_gl_color_%'
                )
            );
        }
    }

    /**
     * Delete plugin files
     */
    private static function delete_files() {
        / Get WordPress uploads directory
        $upload_dir = wp_upload_dir();

        / Define directories to clean
        $directories = [
            $upload_dir['basedir'] . '/gl-color-palettes',
            $upload_dir['basedir'] . '/gl-color-exports',
            $upload_dir['basedir'] . '/gl-color-cache'
        ];

        / Delete directories and their contents
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                self::delete_directory($directory);
            }
        }
    }

    /**
     * Recursively delete a directory
     */
    private static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                self::delete_directory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * Clean up roles and capabilities
     */
    private static function cleanup_roles_capabilities() {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        / List of capabilities to remove
        $caps_to_remove = [
            'manage_color_palettes',
            'edit_color_palettes',
            'delete_color_palettes',
            'view_color_analytics',
            'export_color_palettes'
        ];

        / Remove capabilities from all roles
        foreach ($wp_roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps_to_remove as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    /**
     * Clean up user meta
     */
    private static function cleanup_user_meta() {
        global $wpdb;

        / Delete all user meta with our prefix
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->usermeta}` WHERE meta_key LIKE %s",
                'gl_color_palette_%'
            )
        );
    }
}

/ Run the uninstaller
GLColorPaletteUninstall::uninstall(); 
