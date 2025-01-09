<?php
/**
 * Integration Test Case base class
 *
 * @package GL_Color_Palette_Generator
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use WP_UnitTestCase;

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
    }

    /**
     * Clean up the test environment
     */
    protected function tearDown(): void {
        \Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a test post
     *
     * @param array $args Post creation arguments
     * @return int|WP_Error Post ID or error
     */
    protected function create_test_post($args = []) {
        $defaults = [
            'post_title' => 'Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post'
        ];
        return wp_insert_post(wp_parse_args($args, $defaults));
    }

    /**
     * Create a test user
     *
     * @param string $role User role
     * @param array $args User creation arguments
     * @return int|WP_Error User ID or error
     */
    protected function create_test_user($role = 'administrator', $args = []) {
        $defaults = [
            'user_login' => 'testuser_' . rand(1000, 9999),
            'user_pass' => 'password',
            'role' => $role
        ];
        return wp_insert_user(wp_parse_args($args, $defaults));
    }
}
