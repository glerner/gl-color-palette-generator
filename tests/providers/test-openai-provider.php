<?php declare(strict_types=1);

/**
 * OpenAI Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\OpenAI_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Mock;

/**
 * Tests for the OpenAI Provider
 */
class Test_OpenAI_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new OpenAI_Provider(new Provider_Config($this->get_test_credentials()));
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    protected function get_test_credentials(): array
    {
        return [
            'api_key' => 'test_key_123',
            'model' => 'gpt-4'
        ];
    }

    /**
     * Test generating a palette
     */
    public function test_generate_palette() {
        $params = [
            'prompt' => 'Modern tech company',
            'count' => 5,
            'format' => 'hex'
        ];

        // Mock the API response
        WP_Mock::userFunction('wp_remote_post')->andReturn([
            'response' => ['code' => 200],
            'body' => json_encode([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                '#2C3E50',
                                '#E74C3C',
                                '#ECF0F1',
                                '#3498DB',
                                '#2ECC71'
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $colors = $this->provider->generate_palette($params);
        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
    }
}
