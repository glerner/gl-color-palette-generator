<?php
/**
 * Integration Test Case base class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Base;

use WP_UnitTestCase;
use WP_Error;

/**
 * Base Test Case class for WordPress integration tests
 * Extends WP_UnitTestCase to provide full WordPress test environment
 */
class Integration_Test_Case extends WP_UnitTestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Set up the test environment
     */
    protected function setUp(): void {
        parent::setUp();

        // Reset global WordPress state
        global $post, $wp_query, $wp_the_query;
        $post = null;
        $wp_query = null;
        $wp_the_query = null;

        // Clear any cached data
        wp_cache_flush();

        // Reset any modifications to the test database
        $this->clean_test_db();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void {
        parent::tearDown();

        // Clean up any Mockery expectations
        \Mockery::close();
    }

    /**
     * Clean the test database
     */
    protected function clean_test_db(): void {
        global $wpdb;

        // List of core tables to preserve
        $core_tables = array(
            $wpdb->prefix . 'options',
            $wpdb->prefix . 'users',
            $wpdb->prefix . 'usermeta',
        );

        foreach ($wpdb->tables() as $table) {
            if (!in_array($table, $core_tables, true)) {
                $wpdb->query("TRUNCATE TABLE {$table}");
            }
        }

        // Reset sequences
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'gl_color_palette_%'");

        // Clear object cache
        wp_cache_flush();
    }

    /**
     * Create a test post with optional meta data
     *
     * @param array $post_data Optional. Post data to override defaults.
     * @param array $meta_data Optional. Meta data to add to the post.
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    protected function create_test_post(array $post_data = [], array $meta_data = []): int|WP_Error {
        $defaults = array(
            'post_title'    => 'Test Post',
            'post_content'  => 'Test content.',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'post'
        );

        $post_data = wp_parse_args($post_data, $defaults);
        $post_id = wp_insert_post($post_data, true);

        if (!is_wp_error($post_id) && !empty($meta_data)) {
            foreach ($meta_data as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }

        return $post_id;
    }
}
