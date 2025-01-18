<?php
namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Security\Security_Manager;

class Test_Security_Manager extends WP_Mock_Test_Case {
    private Security_Manager $security;

    protected function set_up() {
        parent::set_up();
        $this->security = new Security_Manager();
    }

    public function test_api_key_encryption() {
        $api_key = 'sk-1234567890abcdef1234567890abcdef1234567890abcdef';

        // Mock WordPress functions
        \WP_Mock::userFunction('wp_salt')->andReturn('test_salt');

        $encrypted = $this->security->encrypt_api_key($api_key);
        $this->assertNotEquals($api_key, $encrypted);

        $decrypted = $this->security->decrypt_api_key($encrypted);
        $this->assertEquals($api_key, $decrypted);
    }

    public function test_nonce_verification() {
        $action = 'generate_palette';
        $nonce = 'test_nonce';

        // Mock WordPress nonce functions
        \WP_Mock::userFunction('wp_create_nonce')
            ->with($action)
            ->andReturn($nonce);

        \WP_Mock::userFunction('wp_verify_nonce')
            ->with($nonce, $action)
            ->andReturn(1);

        $created_nonce = $this->security->create_nonce($action);
        $this->assertEquals($nonce, $created_nonce);

        $is_valid = $this->security->verify_nonce($created_nonce, $action);
        $this->assertTrue($is_valid);
    }

    public function test_invalid_nonce_verification() {
        $action = 'generate_palette';

        // Mock WordPress nonce functions
        \WP_Mock::userFunction('wp_verify_nonce')
            ->with('invalid_nonce', $action)
            ->andReturn(false);

        $is_valid = $this->security->verify_nonce('invalid_nonce', $action);
        $this->assertFalse($is_valid);
    }

    public function test_capability_check() {
        $capability = 'manage_options';

        // Mock WordPress capability function
        \WP_Mock::userFunction('current_user_can')
            ->with($capability)
            ->andReturn(true);

        $has_capability = $this->security->current_user_can($capability);
        $this->assertTrue($has_capability);
    }

    public function test_sanitize_prompt() {
        $unsafe_prompt = '<script>alert("xss")</script>Modern tech company';
        $expected = 'Modern tech company';

        // Mock WordPress sanitize function
        \WP_Mock::userFunction('sanitize_text_field')
            ->with($unsafe_prompt)
            ->andReturn($expected);

        $safe_prompt = $this->security->sanitize_prompt($unsafe_prompt);
        $this->assertEquals($expected, $safe_prompt);
    }

    public function test_validate_color_format() {
        $valid_formats = ['hex', 'rgb', 'hsl'];

        foreach ($valid_formats as $format) {
            $this->assertTrue($this->security->validate_color_format($format));
        }

        $this->assertFalse($this->security->validate_color_format('invalid'));
    }

    public function test_rate_limiting() {
        $user_id = 1;
        $action = 'generate_palette';
        $limit = 60; // 60 requests per hour

        // Mock WordPress transient functions for rate limiting
        \WP_Mock::userFunction('get_transient')
            ->with($this->stringContains("rate_limit_{$user_id}_{$action}"))
            ->andReturn(1); // One request made

        \WP_Mock::userFunction('set_transient')->andReturn(true);

        $is_allowed = $this->security->check_rate_limit($user_id, $action, $limit);
        $this->assertTrue($is_allowed);

        // Mock reaching the limit
        \WP_Mock::userFunction('get_transient')
            ->with($this->stringContains("rate_limit_{$user_id}_{$action}"))
            ->andReturn($limit);

        $is_allowed = $this->security->check_rate_limit($user_id, $action, $limit);
        $this->assertFalse($is_allowed);
    }
}
