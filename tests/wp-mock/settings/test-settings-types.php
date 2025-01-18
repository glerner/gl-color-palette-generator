<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Types;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class Test_Settings_Types extends WP_Mock_Test_Case {
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_ai_providers_constant(): void {
        $this->assertIsArray(Settings_Types::AI_PROVIDERS);
        $this->assertArrayHasKey('openai', Settings_Types::AI_PROVIDERS);
        $this->assertArrayHasKey('anthropic', Settings_Types::AI_PROVIDERS);
        $this->assertArrayHasKey('palm', Settings_Types::AI_PROVIDERS);
        $this->assertArrayHasKey('cohere', Settings_Types::AI_PROVIDERS);
    }

    public function test_default_settings_constant(): void {
        $defaults = Settings_Types::DEFAULT_SETTINGS;
        
        $this->assertIsArray($defaults);
        $this->assertArrayHasKey('ai_provider', $defaults);
        $this->assertArrayHasKey('api_key', $defaults);
        $this->assertArrayHasKey('cache_duration', $defaults);
        $this->assertArrayHasKey('max_colors', $defaults);
        $this->assertArrayHasKey('default_colors', $defaults);
        $this->assertArrayHasKey('enable_analytics', $defaults);
        $this->assertArrayHasKey('rate_limit', $defaults);
        $this->assertArrayHasKey('debug_mode', $defaults);

        $this->assertIsString($defaults['ai_provider']);
        $this->assertIsString($defaults['api_key']);
        $this->assertIsInt($defaults['cache_duration']);
        $this->assertIsInt($defaults['max_colors']);
        $this->assertIsInt($defaults['default_colors']);
        $this->assertIsBool($defaults['enable_analytics']);
        $this->assertIsInt($defaults['rate_limit']);
        $this->assertIsBool($defaults['debug_mode']);
    }

    public function test_field_definitions(): void {
        $fields = Settings_Types::get_field_definitions();
        
        $this->assertIsArray($fields);
        $this->assertArrayHasKey('ai_provider', $fields);
        $this->assertArrayHasKey('api_key', $fields);
        $this->assertArrayHasKey('cache_duration', $fields);

        // Test ai_provider field
        $ai_provider = $fields['ai_provider'];
        $this->assertEquals('select', $ai_provider['type']);
        $this->assertIsCallable($ai_provider['validator']);
        $this->assertTrue($ai_provider['required']);
        $this->assertIsArray($ai_provider['options']);

        // Test api_key field
        $api_key = $fields['api_key'];
        $this->assertEquals('password', $api_key['type']);
        $this->assertIsCallable($api_key['validator']);
        $this->assertTrue($api_key['required']);

        // Test cache_duration field
        $cache_duration = $fields['cache_duration'];
        $this->assertEquals('number', $cache_duration['type']);
        $this->assertIsCallable($cache_duration['validator']);
        $this->assertTrue($cache_duration['required']);
        $this->assertIsInt($cache_duration['min']);
        $this->assertIsInt($cache_duration['max']);
    }

    public function test_validate_ai_provider(): void {
        $this->assertTrue(Settings_Types::validate_ai_provider('openai'));
        $this->assertTrue(Settings_Types::validate_ai_provider('anthropic'));
        $this->assertTrue(Settings_Types::validate_ai_provider('palm'));
        $this->assertTrue(Settings_Types::validate_ai_provider('cohere'));
        $this->assertFalse(Settings_Types::validate_ai_provider('invalid_provider'));
    }

    public function test_validate_api_key(): void {
        $this->assertTrue(Settings_Types::validate_api_key('sk-1234567890abcdef1234567890abcdef1234567890abcdef'));
        $this->assertFalse(Settings_Types::validate_api_key(''));
        $this->assertFalse(Settings_Types::validate_api_key('short_key'));
    }

    public function test_validate_cache_duration(): void {
        $this->assertTrue(Settings_Types::validate_cache_duration(0));
        $this->assertTrue(Settings_Types::validate_cache_duration(3600));
        $this->assertTrue(Settings_Types::validate_cache_duration(86400));
        $this->assertFalse(Settings_Types::validate_cache_duration(-1));
        $this->assertFalse(Settings_Types::validate_cache_duration(86401));
    }

    public function test_validate_max_colors(): void {
        $this->assertTrue(Settings_Types::validate_max_colors(2));
        $this->assertTrue(Settings_Types::validate_max_colors(10));
        $this->assertTrue(Settings_Types::validate_max_colors(20));
        $this->assertFalse(Settings_Types::validate_max_colors(1));
        $this->assertFalse(Settings_Types::validate_max_colors(21));
    }

    public function test_validate_default_colors(): void {
        $this->assertTrue(Settings_Types::validate_default_colors(2));
        $this->assertTrue(Settings_Types::validate_default_colors(5));
        $this->assertTrue(Settings_Types::validate_default_colors(10));
        $this->assertFalse(Settings_Types::validate_default_colors(1));
        $this->assertFalse(Settings_Types::validate_default_colors(11));
    }

    public function test_validate_boolean(): void {
        $this->assertTrue(Settings_Types::validate_boolean(true));
        $this->assertTrue(Settings_Types::validate_boolean(false));
    }

    public function test_validate_rate_limit(): void {
        $this->assertTrue(Settings_Types::validate_rate_limit(1));
        $this->assertTrue(Settings_Types::validate_rate_limit(60));
        $this->assertTrue(Settings_Types::validate_rate_limit(100));
        $this->assertFalse(Settings_Types::validate_rate_limit(0));
        $this->assertFalse(Settings_Types::validate_rate_limit(101));
    }
}
