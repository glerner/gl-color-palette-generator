<?php declare(strict_types=1);

/**
 * Test Color Palette Admin Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Admin;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Admin\Color_Palette_Admin;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use WP_Mock;

/**
 * Tests for Color Palette Admin
 */
class Test_Color_Palette_Admin extends WP_Mock_Test_Case {
    protected Color_Palette_Admin $admin;
    protected Color_Palette_Generator $generator;

    public function setUp(): void {
        parent::setUp();
        $this->generator = $this->getMockBuilder(Color_Palette_Generator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->admin = new Color_Palette_Admin($this->generator);
    }

    public function test_enqueue_admin_assets() {
        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => [
                'gl-color-palette-admin',
                'assets/css/admin.css',
                [],
                GL_CPG_VERSION
            ]
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1,
            'args' => [
                'gl-color-palette-admin',
                'assets/js/admin.js',
                ['jquery'],
                GL_CPG_VERSION,
                true
            ]
        ]);

        WP_Mock::userFunction('wp_localize_script', [
            'times' => 1,
            'args' => [
                'gl-color-palette-admin',
                'gl_color_palette_generator_admin',
                [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('gl_color_palette_admin')
                ]
            ]
        ]);

        $this->admin->enqueue_admin_assets();
    }

    public function test_handle_generate_palette() {
        WP_Mock::userFunction('check_ajax_referer', [
            'times' => 1,
            'args' => ['gl_color_palette_admin', 'nonce'],
            'return' => true
        ]);

        WP_Mock::userFunction('current_user_can', [
            'times' => 1,
            'args' => ['manage_options'],
            'return' => true
        ]);

        $this->generator->expects($this->once())
            ->method('generate_palette')
            ->with(['prompt' => 'Modern tech company'])
            ->willReturn(['#FF0000', '#00FF00', '#0000FF']);

        $_POST['nonce'] = wp_create_nonce('gl_color_palette_admin');
        $_POST['prompt'] = 'Modern tech company';

        ob_start();
        $this->admin->handle_generate_palette();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('colors', $data);
        $this->assertEquals(['#FF0000', '#00FF00', '#0000FF'], $data['colors']);
    }

    public function test_handle_generate_palette_unauthorized() {
        WP_Mock::userFunction('check_ajax_referer', [
            'times' => 1,
            'args' => ['gl_color_palette_admin', 'nonce'],
            'return' => true
        ]);

        WP_Mock::userFunction('current_user_can', [
            'times' => 1,
            'args' => ['manage_options'],
            'return' => false
        ]);

        $_POST['nonce'] = wp_create_nonce('gl_color_palette_admin');

        ob_start();
        $this->admin->handle_generate_palette();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Unauthorized', $data['error']);
    }
}
