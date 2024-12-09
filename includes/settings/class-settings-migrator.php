<?php
declare(strict_types=1);

/**
 * Settings Migrator Class
 *
 * Handles settings migrations between plugin versions
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Settings
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Migrator class
 */
class Settings_Migrator {
    /**
     * Version key in options table
     */
    private const VERSION_KEY = 'gl_cpg_settings_version';

    /**
     * Current settings version
     */
    private const CURRENT_VERSION = '1.0.0';

    /**
     * Run migrations if needed
     *
     * @return void
     */
    public static function maybe_migrate(): void {
        $current_version = get_option(self::VERSION_KEY, '0.0.0');
        
        if (version_compare($current_version, self::CURRENT_VERSION, '<')) {
            self::run_migrations($current_version);
            update_option(self::VERSION_KEY, self::CURRENT_VERSION);
        }
    }

    /**
     * Run migrations from current version
     *
     * @param string $from_version Version to migrate from
     * @return void
     */
    private static function run_migrations(string $from_version): void {
        // Version-specific migrations
        if (version_compare($from_version, '0.5.0', '<')) {
            self::migrate_to_0_5_0();
        }

        if (version_compare($from_version, '1.0.0', '<')) {
            self::migrate_to_1_0_0();
        }
    }

    /**
     * Migrate settings to version 0.5.0
     *
     * @return void
     */
    private static function migrate_to_0_5_0(): void {
        $options = get_option('gl_cpg_options', []);

        // Migrate old cache settings
        if (isset($options['cache_enabled'])) {
            $options['cache_duration'] = $options['cache_enabled'] ? 3600 : 0;
            unset($options['cache_enabled']);
        }

        // Migrate old API settings
        if (isset($options['api_settings'])) {
            $api_settings = $options['api_settings'];
            $options['ai_provider'] = $api_settings['provider'] ?? 'openai';
            $options['api_key'] = $api_settings['key'] ?? '';
            unset($options['api_settings']);
        }

        update_option('gl_cpg_options', $options);
    }

    /**
     * Migrate settings to version 1.0.0
     *
     * @return void
     */
    private static function migrate_to_1_0_0(): void {
        $options = get_option('gl_cpg_options', []);

        // Add new settings with defaults
        $new_settings = [
            'max_colors' => 10,
            'default_colors' => 5,
            'enable_analytics' => true,
            'rate_limit' => 60,
            'debug_mode' => false,
        ];

        foreach ($new_settings as $key => $default_value) {
            if (!isset($options[$key])) {
                $options[$key] = $default_value;
            }
        }

        // Convert old numeric values to integers
        $int_fields = ['cache_duration', 'max_colors', 'default_colors', 'rate_limit'];
        foreach ($int_fields as $field) {
            if (isset($options[$field])) {
                $options[$field] = (int) $options[$field];
            }
        }

        // Convert old boolean values to actual booleans
        $bool_fields = ['enable_analytics', 'debug_mode'];
        foreach ($bool_fields as $field) {
            if (isset($options[$field])) {
                $options[$field] = (bool) $options[$field];
            }
        }

        update_option('gl_cpg_options', $options);
    }
}
