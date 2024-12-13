<?php
/**
 * Test Assets Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Core;

use GL_Color_Palette_Generator\Core\Assets;
use WP_Mock;
use Mockery;

class Test_Assets extends \WP_Mock\Tools\TestCase {
    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    protected function tearDown(): void {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_assets_not_loaded_on_wrong_page(): void {
        $screen = Mockery::mock('WP_Screen');
        $screen->id = 'wrong_page';

        WP_Mock::userFunction('get_current_screen', [
            'return' => $screen,
        ]);

        // Expect no asset enqueuing
        WP_Mock::expectFilterNotCalled('wp_enqueue_style');
        WP_Mock::expectFilterNotCalled('wp_enqueue_script');

        Assets::enqueue_admin_assets();
    }

    public function test_admin_assets_loaded_on_plugin_page(): void {
        $screen = Mockery::mock('WP_Screen');
        $screen->id = 'toplevel_page_gl-color-palette-generator';

        WP_Mock::userFunction('get_current_screen', [
            'return' => $screen,
        ]);

        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 2, // admin.css and wp-color-picker
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1,
        ]);

        WP_Mock::userFunction('admin_url', [
            'return' => 'http://example.com/wp-admin/admin-ajax.php',
        ]);

        WP_Mock::userFunction('wp_create_nonce', [
            'return' => 'test-nonce',
        ]);

        WP_Mock::userFunction('wp_localize_script');

        Assets::enqueue_admin_assets();
    }

    public function test_frontend_assets_not_loaded_without_shortcode(): void {
        global $post;
        $post = new \stdClass();
        $post->post_content = 'Regular content without shortcode';

        WP_Mock::userFunction('is_singular', [
            'return' => true,
        ]);

        WP_Mock::userFunction('has_shortcode', [
            'return' => false,
        ]);

        WP_Mock::userFunction('has_block', [
            'return' => false,
        ]);

        // Expect no asset enqueuing
        WP_Mock::expectFilterNotCalled('wp_enqueue_style');
        WP_Mock::expectFilterNotCalled('wp_enqueue_script');

        Assets::enqueue_frontend_assets();
    }

    public function test_frontend_assets_loaded_with_shortcode(): void {
        global $post;
        $post = new \stdClass();
        $post->post_content = '[gl_color_palette]';

        WP_Mock::userFunction('is_singular', [
            'return' => true,
        ]);

        WP_Mock::userFunction('has_shortcode', [
            'return' => true,
        ]);

        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1,
        ]);

        WP_Mock::userFunction('admin_url', [
            'return' => 'http://example.com/wp-admin/admin-ajax.php',
        ]);

        WP_Mock::userFunction('wp_create_nonce', [
            'return' => 'test-nonce',
        ]);

        WP_Mock::userFunction('wp_localize_script');

        Assets::enqueue_frontend_assets();
    }

    public function test_frontend_assets_loaded_with_block(): void {
        global $post;
        $post = new \stdClass();
        $post->post_content = '<!-- wp:gl-color-palette-generator/palette /-->';

        WP_Mock::userFunction('is_singular', [
            'return' => true,
        ]);

        WP_Mock::userFunction('has_shortcode', [
            'return' => false,
        ]);

        WP_Mock::userFunction('has_block', [
            'return' => true,
        ]);

        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1,
        ]);

        WP_Mock::userFunction('admin_url', [
            'return' => 'http://example.com/wp-admin/admin-ajax.php',
        ]);

        WP_Mock::userFunction('wp_create_nonce', [
            'return' => 'test-nonce',
        ]);

        WP_Mock::userFunction('wp_localize_script');

        Assets::enqueue_frontend_assets();
    }
}
