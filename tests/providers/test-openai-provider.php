<?php
/**
 * OpenAI Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Providers\OpenAI_Provider;
use WP_Mock;

class Test_OpenAI_Provider extends \WP_Mock\Tools\TestCase {
    private $provider;
    private $test_api_key = 'test_key_123';

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new OpenAI_Provider(['api_key' => $this->test_api_key]);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_get_name(): void {
        $this->assertEquals('openai', $this->provider->get_name());
    }

    public function test_get_display_name(): void {
        $this->assertEquals('OpenAI', $this->provider->get_display_name());
    }

    public function test_is_ready(): void {
        $this->assertTrue($this->provider->is_ready());

        $provider = new OpenAI_Provider([]);
        $this->assertFalse($provider->is_ready());
    }

    public function test_get_config_fields(): void {
        $fields = $this->provider->get_config_fields();
        $this->assertIsArray($fields);
        $this->assertArrayHasKey('api_key', $fields);
        $this->assertArrayHasKey('model', $fields);
        $this->assertArrayHasKey('temperature', $fields);
    }

    /**
     * @dataProvider config_validation_provider
     */
    public function test_validate_config(array $config, bool $expected): void {
        $this->assertEquals($expected, $this->provider->validate_config($config));
    }

    public function config_validation_provider(): array {
        return [
            'valid minimal' => [
                ['api_key' => 'test_key'],
                true
            ],
            'valid full' => [
                [
                    'api_key' => 'test_key',
                    'model' => 'gpt-4',
                    'temperature' => 0.7
                ],
                true
            ],
            'missing api key' => [
                [],
                false
            ],
            'invalid model' => [
                [
                    'api_key' => 'test_key',
                    'model' => 'invalid-model'
                ],
                false
            ],
            'temperature too high' => [
                [
                    'api_key' => 'test_key',
                    'temperature' => 1.1
                ],
                false
            ],
            'temperature too low' => [
                [
                    'api_key' => 'test_key',
                    'temperature' => -0.1
                ],
                false
            ],
        ];
    }

    public function test_generate_palette_success(): void {
        $expected_response = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                            'metadata' => [
                                'theme' => 'vibrant',
                                'mood' => 'energetic',
                                'description' => 'A vibrant RGB palette'
                            ]
                        ])
                    ]
                ]
            ]
        ];

        WP_Mock::userFunction('wp_remote_post', [
            'return' => [
                'response' => ['code' => 200],
                'body' => json_encode($expected_response)
            ]
        ]);

        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return' => json_encode($expected_response)
        ]);

        $result = $this->provider->generate_palette('Create a vibrant RGB palette', 3);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertCount(3, $result['colors']);
    }

    public function test_generate_palette_invalid_count(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->provider->generate_palette('Test prompt', 1);
    }

    public function test_generate_palette_empty_prompt(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->provider->generate_palette('', 5);
    }

    public function test_generate_palette_api_error(): void {
        WP_Mock::userFunction('wp_remote_post', [
            'return' => new \WP_Error('http_error', 'API connection failed')
        ]);

        $this->expectException(\Exception::class);
        $this->provider->generate_palette('Test prompt', 5);
    }

    public function test_generate_palette_invalid_response(): void {
        WP_Mock::userFunction('wp_remote_post', [
            'return' => [
                'response' => ['code' => 200],
                'body' => json_encode(['error' => ['message' => 'API error']])
            ]
        ]);

        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return' => json_encode(['error' => ['message' => 'API error']])
        ]);

        $this->expectException(\Exception::class);
        $this->provider->generate_palette('Test prompt', 5);
    }

    public function test_generate_palette_with_options(): void {
        $expected_response = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'colors' => ['#FF0000', '#00FF00'],
                            'metadata' => [
                                'theme' => 'custom',
                                'mood' => 'calm',
                                'description' => 'A custom palette'
                            ]
                        ])
                    ]
                ]
            ]
        ];

        WP_Mock::userFunction('wp_remote_post', [
            'return' => [
                'response' => ['code' => 200],
                'body' => json_encode($expected_response)
            ]
        ]);

        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return' => json_encode($expected_response)
        ]);

        $result = $this->provider->generate_palette(
            'Create a custom palette',
            2,
            [
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.5
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertCount(2, $result['colors']);
    }
}
