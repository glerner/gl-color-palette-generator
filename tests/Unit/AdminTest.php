<?php
/**
 * Admin Class Tests
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GLColorPalette\Tests\Unit;

use GLColorPalette\Admin\Admin;
use WP_UnitTestCase;

class AdminTest extends WP_UnitTestCase {
    /**
     * @var Admin
     */
    private $admin;

    public function setUp(): void {
        parent::setUp();
        $this->admin = new Admin();
    }

    public function test_menu_pages_are_added() {
        $this->admin->add_menu_pages();

        global $menu, $submenu;

        // Check main menu
        $menu_exists = false;
        foreach ($menu as $item) {
            if ($item[2] === 'gl-color-palette') {
                $menu_exists = true;
                break;
            }
        }
        $this->assertTrue($menu_exists);

        // Check submenu
        $this->assertArrayHasKey('gl-color-palette', $submenu);
        $this->assertCount(2, $submenu['gl-color-palette']); // Main page and settings
    }

    public function test_settings_registration() {
        $this->admin->register_settings();

        $registered_settings = get_registered_settings();

        $this->assertArrayHasKey('gl_color_palette_size', $registered_settings);
        $this->assertArrayHasKey('gl_color_palette_format', $registered_settings);
    }

    public function test_ajax_generate_palette() {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        $_POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');
        $_POST['size'] = 5;

        try {
            $this->admin->handle_generate_palette();
        } catch (\WPDieException $e) {
            // Get the last response
            $response = json_decode($this->_last_response, true);
            $this->assertArrayHasKey('success', $response);
            $this->assertArrayHasKey('data', $response);
            $this->assertArrayHasKey('palette', $response['data']);
            $this->assertCount(5, $response['data']['palette']);
        }
    }

    public function test_unauthorized_users_cannot_generate_palette() {
        wp_set_current_user(0);

        $_POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');

        try {
            $this->admin->handle_generate_palette();
        } catch (\WPDieException $e) {
            $response = json_decode($this->_last_response, true);
            $this->assertFalse($response['success']);
            $this->assertEquals('Unauthorized', $response['data']);
        }
    }
} 
