<?php
/**
 * Test Color Palette Admin Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\Admin;

use GL_Color_Palette_Generator\Admin\Color_Palette_Admin;
use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Class Test_Color_Palette_Admin
 */
class Test_Color_Palette_Admin extends TestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Admin instance
     *
     * @var Color_Palette_Admin
     */
    private $admin;

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->admin = new Color_Palette_Admin();
    }

    /**
     * Teardown test environment
     */
    protected function tearDown(): void {
        \Brain\Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin menu creation
     */
    public function test_add_admin_menu() {
        Functions\expect('add_menu_page')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type('string'),
                'manage_options',
                'gl-color-palette-generator',
                Mockery::type('array'),
                'dashicons-art',
                30
            );

        Functions\expect('add_submenu_page')
            ->once()
            ->with(
                'gl-color-palette-generator',
                Mockery::type('string'),
                Mockery::type('string'),
                'manage_options',
                'gl-color-palette-settings',
                Mockery::type('array')
            );

        $this->admin->add_admin_menu();
    }

    /**
     * Test admin assets enqueuing
     */
    public function test_enqueue_admin_assets() {
        Functions\expect('wp_enqueue_style')
            ->once()
            ->with(
                'gl-cpg-admin',
                Mockery::type('string'),
                [],
                GL_CPG_VERSION
            );

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with(
                'gl-cpg-admin',
                Mockery::type('string'),
                ['jquery', 'wp-color-picker'],
                GL_CPG_VERSION,
                true
            );

        Functions\expect('wp_localize_script')
            ->once()
            ->with(
                'gl-cpg-admin',
                'glCpgAdmin',
                Mockery::type('array')
            );

        $this->admin->enqueue_admin_assets('toplevel_page_gl-color-palette-generator');
    }

    /**
     * Test AJAX palette generation handling
     */
    public function test_handle_generate_palette() {
        Functions\expect('check_ajax_referer')
            ->once()
            ->with('gl_cpg_admin', 'nonce');

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(true);

        Functions\expect('sanitize_text_field')
            ->once()
            ->andReturn('Test prompt');

        Functions\expect('wp_send_json_success')
            ->once()
            ->with(Mockery::type('array'));

        $_POST['prompt'] = 'Test prompt';
        $_POST['nonce'] = 'test_nonce';

        $this->admin->handle_generate_palette();
    }

    /**
     * Test unauthorized access to generate palette
     */
    public function test_handle_generate_palette_unauthorized() {
        Functions\expect('check_ajax_referer')
            ->once()
            ->with('gl_cpg_admin', 'nonce');

        Functions\expect('current_user_can')
            ->once()
            ->with('manage_options')
            ->andReturn(false);

        Functions\expect('wp_send_json_error')
            ->once()
            ->with('Unauthorized');

        $_POST['nonce'] = 'test_nonce';

        $this->admin->handle_generate_palette();
    }
} 
