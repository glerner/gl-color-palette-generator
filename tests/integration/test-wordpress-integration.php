<?php
/**
 * Integration tests for WordPress-specific functionality
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Test_Case_Integration;
use GL_Color_Palette_Generator\Core\Plugin;

/**
 * Test WordPress integration
 */
class Test_WordPress_Integration extends Test_Case_Integration {
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        // Remove all actions that might send headers
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('wp_headers');
        remove_all_actions('send_headers');

        parent::setUp();

        // Initialize plugin
        $this->plugin = Plugin::get_instance();
        $this->plugin->init();

        // Set up admin environment
        set_current_screen('dashboard');
        do_action('admin_menu');
        do_action('admin_init');
    }

    /**
     * Clean up test environment
     */
    public function tearDown(): void {
        // Reset menu globals
        global $menu, $submenu;
        $menu = null;
        $submenu = null;

        parent::tearDown();
    }

    /**
     * Test WordPress integration functionality
     */
    public function test_wordpress_integration() {
        $this->assertTrue(defined('ABSPATH'), 'WordPress not loaded');
    }

    /**
     * Test plugin activation
     */
    public function test_plugin_activation() {
        $this->assertInstanceOf(Plugin::class, $this->plugin);
    }

    /**
     * Test shortcode registration
     */
    public function test_shortcode_registration() {
        $this->plugin->register_shortcodes();
        global $shortcode_tags;
        $this->assertArrayHasKey('gl_color_palette', $shortcode_tags);
    }

    /**
     * Test admin menu registration
     */
    public function test_admin_menu_registration() {
        global $menu;

        if (!is_array($menu)) {
            $menu = [];
        }

        $this->plugin->register_admin_menu();

        $menu_slug = 'gl-color-palette-generator';
        $found = false;

        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === $menu_slug) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Admin menu not found');
    }

    /**
     * Test settings registration
     */
    public function test_settings_registration() {
        $this->plugin->register_settings();

        global $wp_settings_sections;
        $this->assertIsArray($wp_settings_sections);
        $this->assertArrayHasKey('gl-color-palette-generator', $wp_settings_sections);
    }

    /**
     * Test AJAX handlers
     */
    public function test_ajax_handlers() {
        $this->plugin->register_ajax_handlers();
        $this->assertTrue(has_action('wp_ajax_gl_cpg_generate_palette'));
        $this->assertTrue(has_action('wp_ajax_gl_cpg_save_palette'));
    }

    /**
     * Test block registration
     */
    public function test_block_registration() {
        $this->plugin->register_blocks();
        global $wp_registered_blocks;
        $this->assertArrayHasKey('gl-color-palette-generator/palette', $wp_registered_blocks);
    }
}
