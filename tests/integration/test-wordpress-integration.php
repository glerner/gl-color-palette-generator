<?php
/**
 * Integration tests for WordPress-specific functionality
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;
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
        parent::tearDown();

        // Reset menu globals
        global $menu, $submenu;
        $menu = null;
        $submenu = null;
    }

    /**
     * Test WordPress integration
     */
    public function test_wordpress_integration() {
        // Verify WordPress core is loaded
        $this->assertTrue(defined('ABSPATH'), 'WordPress not loaded');

        // Check plugin initialization
        $this->assertInstanceOf(Plugin::class, $this->plugin);

        // Verify essential WordPress functions are available
        $this->assertTrue(function_exists('add_action'));
        $this->assertTrue(function_exists('add_filter'));
        $this->assertTrue(function_exists('do_action'));
    }

    /**
     * Test plugin activation
     */
    public function test_plugin_activation() {
        // Test plugin instance
        $this->assertInstanceOf(Plugin::class, $this->plugin);

        // Verify activation hooks are registered
        $this->assertTrue(has_action('activate_gl-color-palette-generator/gl-color-palette-generator.php'));

        // Check if essential plugin components are initialized
        $this->assertTrue(class_exists('GL_Color_Palette_Generator\Core\Plugin'));
        $this->assertTrue(class_exists('GL_Color_Palette_Generator\Color_Management\Color_Calculator'));
    }

    /**
     * Test shortcode registration
     */
    public function test_shortcode_registration() {
        $this->plugin->register_shortcodes();

        global $shortcode_tags;
        $this->assertArrayHasKey('gl_color_palette', $shortcode_tags);

        // Test shortcode callback exists and is callable
        $this->assertTrue(is_callable($shortcode_tags['gl_color_palette']));
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

        // Check if specific settings are registered
        $registered_settings = get_registered_settings();
        $this->assertArrayHasKey('gl_color_palette_generator_options', $registered_settings);
    }

    /**
     * Test AJAX handlers
     */
    public function test_ajax_handlers() {
        $this->plugin->register_ajax_handlers();

        // Test palette generation handler
        $this->assertTrue(
            has_action('wp_ajax_gl_cpg_generate_palette'),
            'Palette generation AJAX handler not registered'
        );

        // Test palette saving handler
        $this->assertTrue(
            has_action('wp_ajax_gl_cpg_save_palette'),
            'Palette saving AJAX handler not registered'
        );

        // Test palette export handler
        $this->assertTrue(
            has_action('wp_ajax_gl_cpg_export_palette'),
            'Palette export AJAX handler not registered'
        );
    }

    /**
     * Test block registration
     */
    public function test_block_registration() {
        $this->plugin->register_blocks();

        // Check block registration
        $registry = WP_Block_Type_Registry::get_instance();
        $this->assertTrue(
            $registry->is_registered('gl-color-palette/generator'),
            'Color palette generator block not registered'
        );

        // Verify block assets are enqueued
        $this->assertTrue(
            wp_script_is('gl-color-palette-generator-block', 'registered'),
            'Block script not registered'
        );
        $this->assertTrue(
            wp_style_is('gl-color-palette-generator-block', 'registered'),
            'Block style not registered'
        );
    }
}
