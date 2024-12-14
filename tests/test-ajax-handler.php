<?php
/**
 * Tests for Ajax Handler
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Ajax_Handler;
use Test_Case_Integration;
use Brain\Monkey\Functions;

/**
 * Test Ajax Handler functionality
 */
class Test_Ajax_Handler extends Test_Case_Integration {
    private Ajax_Handler $handler;

    public function setUp(): void {
        parent::setUp();
        $this->handler = new Ajax_Handler();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    public function test_register_hooks() {
        Functions\expect('add_action')
            ->once()
            ->with('wp_ajax_gl_generate_palette', [$this->handler, 'handle_generate_palette']);
        
        Functions\expect('add_action')
            ->once()
            ->with('wp_ajax_nopriv_gl_generate_palette', [$this->handler, 'handle_generate_palette']);

        $this->handler->register_hooks();
    }

    /**
     * @test
     */
    public function test_test_api_connection_requires_nonce(): void {
        $this->expectException('WPAjaxDieStopException');

        $_POST['provider'] = 'openai';
        $_POST['api_key'] = 'sk-test123';

        $this->_handleAjax('gl_test_api_connection');
    }

    /**
     * @test
     */
    public function test_test_api_connection_requires_admin(): void {
        wp_set_current_user(0);

        $_POST['provider'] = 'openai';
        $_POST['api_key'] = 'sk-test123';
        $_POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');

        try {
            $this->_handleAjax('gl_test_api_connection');
        } catch (\WPAjaxDieContinueException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('Insufficient permissions', $response['data']['message']);
        }
    }

    /**
     * @test
     */
    public function test_test_api_connection_validates_provider(): void {
        $_POST['provider'] = 'invalid_provider';
        $_POST['api_key'] = 'sk-test123';
        $_POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');

        try {
            $this->_handleAjax('gl_test_api_connection');
        } catch (\WPAjaxDieContinueException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('Invalid provider', $response['data']['message']);
        }
    }

    /**
     * @test
     */
    public function test_save_settings_saves_valid_data(): void {
        $_POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');
        $_POST['settings'] = [
            'ai_provider' => 'openai',
            'api_key' => 'sk-test123',
            'openai_model' => 'gpt-4'
        ];

        try {
            $this->_handleAjax('gl_save_settings');
        } catch (\WPAjaxDieContinueException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);

            // Verify settings were saved
            $this->assertEquals('openai', get_option('gl_color_palette_ai_provider'));
            $this->assertEquals('sk-test123', get_option('gl_color_palette_api_key'));
            $this->assertEquals('gpt-4', get_option('gl_color_palette_openai_model'));
        }
    }
} 
