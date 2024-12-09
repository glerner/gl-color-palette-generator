<?php
declare(strict_types=1);

/**
 * OpenAI Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Providers\OpenAI_Provider;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use WP_Mock;

/**
 * OpenAI Provider test case
 */
class Test_OpenAI_Provider extends \WP_Mock\Tools\TestCase {
    /**
     * Provider instance
     *
     * @var OpenAI_Provider
     */
    private OpenAI_Provider $provider;

    /**
     * Test API key
     *
     * @var string
     */
    private string $test_api_key = 'test_key_123';

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        
        $this->provider = new OpenAI_Provider([
            'api_key' => $this->test_api_key,
            'base_url' => 'https://api.openai.com/v1'
        ]);
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * Test provider name
     */
    public function test_get_name(): void {
        $this->assertEquals('openai', $this->provider->get_name());
    }

    /**
     * Test provider display name
     */
    public function test_get_display_name(): void {
        $this->assertEquals('OpenAI', $this->provider->get_display_name());
    }

    /**
     * Test provider readiness
     */
    public function test_is_ready(): void {
        $this->assertTrue($this->provider->is_ready());

        $this->expectException(\InvalidArgumentException::class);
        new OpenAI_Provider([]);
    }

    /**
     * Test provider capabilities
     */
    public function test_get_capabilities(): void {
        $capabilities = $this->provider->get_capabilities();
        
        $this->assertIsArray($capabilities);
        $this->assertArrayHasKey('max_colors', $capabilities);
        $this->assertArrayHasKey('supports_streaming', $capabilities);
        $this->assertArrayHasKey('supports_batch', $capabilities);
        $this->assertArrayHasKey('supports_style_transfer', $capabilities);
        $this->assertArrayHasKey('max_prompt_length', $capabilities);
        $this->assertArrayHasKey('rate_limit', $capabilities);
        
        $this->assertEquals(10, $capabilities['max_colors']);
        $this->assertTrue($capabilities['supports_streaming']);
        $this->assertFalse($capabilities['supports_batch']);
        $this->assertFalse($capabilities['supports_style_transfer']);
        $this->assertEquals(4000, $capabilities['max_prompt_length']);
    }

    /**
     * Test options validation
     */
    public function test_validate_options(): void {
        // Valid options
        $valid_options = [
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'max_tokens' => 150,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0
        ];
        $this->assertTrue($this->provider->validate_options($valid_options));

        // Invalid options
        $invalid_options = [
            'model' => 123,
            'temperature' => 1.5,
            'invalid_option' => 'value'
        ];
        $this->assertFalse($this->provider->validate_options($invalid_options));
    }

    /**
     * Test palette generation success
     */
    public function test_generate_palette_success(): void {
        $prompt = 'Test prompt';
        $expected_response = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'colors' => ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF'],
                            'metadata' => [
                                'theme' => 'Test theme',
                                'mood' => 'Test mood',
                                'description' => 'Test description'
                            ]
                        ])
                    ]
                ]
            ]
        ];

        // Mock WordPress HTTP request
        WP_Mock::userFunction('wp_remote_request')
            ->once()
            ->andReturn([
                'response' => ['code' => 200],
                'body' => json_encode($expected_response)
            ]);

        WP_Mock::userFunction('wp_remote_retrieve_body')
            ->once()
            ->andReturn(json_encode($expected_response));

        $result = $this->provider->generate_palette($prompt);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertCount(5, $result['colors']);
        $this->assertEquals('openai', $result['metadata']['provider']);
    }

    /**
     * Test palette generation with invalid prompt
     */
    public function test_generate_palette_invalid_prompt(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->provider->generate_palette('');
    }

    /**
     * Test palette generation with invalid number of colors
     */
    public function test_generate_palette_invalid_num_colors(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->provider->generate_palette('Test prompt', 11);
    }

    /**
     * Test palette generation with API error
     */
    public function test_generate_palette_api_error(): void {
        WP_Mock::userFunction('wp_remote_request')
            ->once()
            ->andReturn(new \WP_Error('http_request_failed', 'API error'));

        $this->expectException(PaletteGenerationException::class);
        $this->provider->generate_palette('Test prompt');
    }

    /**
     * Test palette generation with invalid API response
     */
    public function test_generate_palette_invalid_response(): void {
        $invalid_response = [
            'choices' => [
                [
                    'message' => [
                        'content' => 'not json'
                    ]
                ]
            ]
        ];

        WP_Mock::userFunction('wp_remote_request')
            ->once()
            ->andReturn([
                'response' => ['code' => 200],
                'body' => json_encode($invalid_response)
            ]);

        WP_Mock::userFunction('wp_remote_retrieve_body')
            ->once()
            ->andReturn(json_encode($invalid_response));

        $this->expectException(PaletteGenerationException::class);
        $this->provider->generate_palette('Test prompt');
    }
}
