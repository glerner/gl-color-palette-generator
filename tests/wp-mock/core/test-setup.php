<?php
/**
 * Test Setup Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\Core;

use GL_Color_Palette_Generator\Core\Setup;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Class Test_Setup
 */
class Test_Setup extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Setup test environment
     */
    public function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();
    }

    /**
     * Teardown test environment
     */
    public function tearDown(): void {
        \Brain\Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test plugin initialization
     */
    public function test_init() {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

        $setup = new Setup();
        $setup->init();

        $this->assertTrue(
            has_action('init', [$setup, 'load_textdomain'])
        );
        $this->assertTrue(
            has_action('rest_api_init', [$setup, 'register_rest_routes'])
        );
    }

    /**
     * Test activation creates tables
     */
    public function test_activate_creates_tables() {
        global $wpdb;
        $wpdb = Mockery::mock('\wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
            ->once()
            ->andReturn('DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        Functions\expect('dbDelta')
            ->once()
            ->with(Mockery::type('string'));

        $setup = new Setup();
        $setup->activate();
    }

    /**
     * Test default options are set
     */
    public function test_default_options_are_set() {
        Functions\expect('get_option')
            ->times(3)
            ->andReturn(false);

        Functions\expect('add_option')
            ->times(3)
            ->andReturn(true);

        $setup = new Setup();
        $setup->activate();
    }

    /**
     * Test deactivation cleanup
     */
    public function test_deactivate_cleanup() {
        Functions\expect('wp_upload_dir')
            ->once()
            ->andReturn(['basedir' => '/tmp']);

        $setup = new Setup();
        $setup->deactivate();
    }
} 
