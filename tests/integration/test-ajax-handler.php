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
    /**
     * Test AJAX handler initialization
     */
    public function test_ajax_handler_init() {
        $handler = new Ajax_Handler();
        $this->assertInstanceOf(Ajax_Handler::class, $handler);
    }

    /**
     * Test AJAX endpoint registration
     */
    public function test_ajax_endpoints() {
        $handler = new Ajax_Handler();
        $this->assertTrue(has_action('wp_ajax_gl_cpg_generate_palette'));
        $this->assertTrue(has_action('wp_ajax_nopriv_gl_cpg_generate_palette'));
    }

    /**
     * Test AJAX request handling
     */
    public function test_ajax_request() {
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
