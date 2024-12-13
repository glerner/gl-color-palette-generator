<?php declare(strict_types=1);

/**
 * OpenAI Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Providers\OpenAI_Provider;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use WP_Mock;

/**
 * OpenAI Provider test case
 */
class Test_OpenAI_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        
        $this->provider = new OpenAI_Provider($this->get_test_credentials());
    }

    protected function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'base_url' => 'https://api.openai.com/v1'
        ];
    }

    public function test_get_name(): void {
        $this->assertEquals('openai', $this->provider->get_name());
    }

    public function test_get_display_name(): void {
        $this->assertEquals('OpenAI', $this->provider->get_display_name());
    }

    public function test_get_capabilities(): void {
        $capabilities = $this->provider->get_capabilities();
        $this->assertIsArray($capabilities);
        $this->assertArrayHasKey('max_colors', $capabilities);
        $this->assertArrayHasKey('supports_streaming', $capabilities);
        $this->assertArrayHasKey('supports_batch', $capabilities);
    }

    public function test_validate_credentials_with_empty_credentials(): void {
        $provider = new OpenAI_Provider([]);
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());
    }

    public function test_validate_credentials_with_valid_credentials(): void {
        $this->assertTrue($this->provider->validate_credentials());
    }

    public function test_generate_palette_with_invalid_params(): void {
        $result = $this->provider->generate_palette([
            'base_color' => 'invalid',
            'count' => -1
        ]);
        $this->assertInstanceOf(\WP_Error::class, $result);
    }

    public function test_generate_palette_success(): void {
        // Mock the API response
        WP_Mock::userFunction('wp_remote_post')->once()->andReturn([
            'response' => ['code' => 200],
            'body' => json_encode([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                '#FF0000',
                                '#00FF00',
                                '#0000FF'
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->provider->generate_palette([
            'prompt' => 'Test prompt',
            'count' => 3
        ]);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        foreach ($result as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }

    public function test_generate_palette_api_error(): void {
        // Mock API error response
        WP_Mock::userFunction('wp_remote_post')->once()->andReturn([
            'response' => ['code' => 400],
            'body' => json_encode(['error' => 'Invalid request'])
        ]);

        $result = $this->provider->generate_palette([
            'prompt' => 'Test prompt',
            'count' => 3
        ]);

        $this->assertInstanceOf(\WP_Error::class, $result);
    }

    public function test_generate_palette_invalid_response(): void {
        // Mock invalid API response
        WP_Mock::userFunction('wp_remote_post')->once()->andReturn([
            'response' => ['code' => 200],
            'body' => 'invalid json'
        ]);

        $result = $this->provider->generate_palette([
            'prompt' => 'Test prompt',
            'count' => 3
        ]);

        $this->assertInstanceOf(\WP_Error::class, $result);
    }
}
