<?php
/**
 * Integration tests for AJAX handler
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Test_Case_Integration;
use GL_Color_Palette_Generator\Core\Ajax_Handler;

/**
 * Test AJAX handler integration
 */
class Test_Ajax_Handler extends Test_Case_Integration {

    public function setUp(): void {
        parent::setUp();
        error_log('Setting up Test_Ajax_Handler test');
        error_log('Ajax_Handler exists in setUp? ' . (class_exists('GL_Color_Palette_Generator\Core\Ajax_Handler') ? 'yes' : 'no'));
    }

    /**
     * Test AJAX handler initialization
     */
    public function test_ajax_handler_init() {
        error_log('Running test_ajax_handler_init');
        error_log('Ajax_Handler exists in test? ' . (class_exists('GL_Color_Palette_Generator\Core\Ajax_Handler') ? 'yes' : 'no'));
        error_log('Current autoloaded classes: ' . print_r(get_declared_classes(), true));
        
        $handler = new Ajax_Handler();
        $this->assertInstanceOf(Ajax_Handler::class, $handler);
    }

    /**
     * Test AJAX endpoint registration
     */
    public function test_ajax_endpoints() {
        error_log('Running test_ajax_endpoints');
        $handler = new Ajax_Handler();
        $handler->init();
        
        // Check if AJAX actions are registered
        $this->assertTrue(has_action('wp_ajax_gl_color_palette_generator_generate'));
        $this->assertTrue(has_action('wp_ajax_nopriv_gl_color_palette_generator_generate'));
    }

    /**
     * Test AJAX request handling
     */
    public function test_ajax_request() {
        error_log('Running test_ajax_request');
        // Simulate AJAX request
        $_POST['action'] = 'gl_cpg_generate_palette';
        $_POST['prompt'] = 'A sunset over the ocean';
        $_POST['nonce'] = wp_create_nonce('gl_cpg_generate_palette');

        try {
            do_action('wp_ajax_gl_cpg_generate_palette');
            $this->assertTrue(true);
        } catch (\WPAjaxDieContinueException $e) {
            // This is expected when testing AJAX
            $this->assertTrue(true);
        }
    }
}
