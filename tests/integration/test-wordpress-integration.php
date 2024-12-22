<?php
namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Test_Case;
use GL_Color_Palette_Generator\Core\Plugin;

/**
 * Integration tests for WordPress-specific functionality
 */
class Test_WordPress_Integration extends Test_Case {
    private $plugin;

    public function set_up() {
        parent::set_up();
        $this->plugin = Plugin::get_instance();
    }

    public function test_plugin_activation() {
        // Simulate plugin activation
        do_action('activate_gl-color-palette-generator/gl-color-palette-generator.php');

        // Check if options are set
        $settings = get_option('gl_cpg_settings');
        $this->assertNotFalse($settings);

        // Check if default provider is set
        $this->assertArrayHasKey('default_provider', $settings);
    }

    public function test_shortcode_registration() {
        global $shortcode_tags;

        // Check if our shortcodes are registered
        $this->assertArrayHasKey('gl_color_palette', $shortcode_tags);
    }

    public function test_admin_menu_registration() {
        // Simulate admin user
        $admin_user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user_id);

        // Initialize admin menu
        do_action('admin_menu');

        // Check if our menu items exist
        global $menu, $submenu;

        $menu_slug = 'gl-color-palette-generator';
        $found = false;

        foreach ($menu as $item) {
            if ($item[2] === $menu_slug) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Admin menu not found');
    }

    public function test_settings_registration() {
        // Initialize admin menu and settings
        do_action('admin_init');

        // Check if our settings are registered
        global $wp_settings_sections;

        $this->assertArrayHasKey('gl_cpg_settings', $wp_settings_sections);
    }

    public function test_ajax_handlers() {
        // Test generate palette AJAX handler
        $_POST['prompt'] = 'Modern tech company';
        $_POST['count'] = '5';
        $_POST['format'] = 'hex';
        $_POST['action'] = 'gl_cpg_generate_palette';
        $_POST['nonce'] = wp_create_nonce('gl_cpg_generate_palette');

        // Simulate AJAX request
        try {
            do_action('wp_ajax_gl_cpg_generate_palette');
            $this->assertTrue(false); // Should not reach here
        } catch (\WPAjaxDieContinueException $e) {
            // This is expected for unit tests
            $response = json_decode($e->getMessage(), true);

            $this->assertIsArray($response);
            $this->assertArrayHasKey('success', $response);
            $this->assertArrayHasKey('data', $response);
        }
    }

    public function test_block_registration() {
        // Check if block is registered
        $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

        $this->assertArrayHasKey('gl-color-palette-generator/palette', $registered_blocks);
    }
}
