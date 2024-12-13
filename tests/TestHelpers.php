<?php
/**
 * Test Helper Functions
 *
 * @package    GL_Color_Palette_Generator
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GL_Color_Palette_Generator\Tests;

class TestHelpers {
    /**
     * Create a test palette
     *
     * @param string $name Palette name
     * @param array  $colors Array of colors
     * @return int Palette ID
     */
    public static function create_test_palette(string $name, array $colors): int {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'gl_color_palettes',
            [
                'name' => $name,
                'colors' => json_encode($colors),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s']
        );

        return $wpdb->insert_id;
    }

    /**
     * Clean up test palettes
     *
     * @return void
     */
    public static function cleanup_test_palettes(): void {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}gl_color_palettes");
    }
} 
