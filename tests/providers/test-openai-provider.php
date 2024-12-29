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
            'num_colors' => 4,
            'options' => [
                'temperature' => 0.7,
                'max_tokens' => 500
            ]
        ];

        $expected_response = [
            'colors' => [
                'primary' => [
                    'hex' => '#2C3E50',
                    'name' => 'Midnight Ocean',
                    'emotion' => 'Deep trust and stability'
                ],
                'secondary' => [
                    'hex' => '#E74C3C',
                    'name' => 'Energetic Coral',
                    'emotion' => 'Dynamic and engaging'
                ],
                'tertiary' => [
                    'hex' => '#3498DB',
                    'name' => 'Clear Sky',
                    'emotion' => 'Innovation and clarity'
                ],
                'accent' => [
                    'hex' => '#2ECC71',
                    'name' => 'Growth Green',
                    'emotion' => 'Progress and success'
                ]
            ],
            'palette_story' => 'A modern and professional palette that combines trust and innovation'
        ];

        // Mock the API response
        $this->mock_http_response(json_encode($expected_response));

        $result = $this->provider->generate_palette($params);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('palette_story', $result);
        
        // Check color structure
        foreach (['primary', 'secondary', 'tertiary', 'accent'] as $role) {
            $this->assertArrayHasKey($role, $result['colors']);
            $this->assertArrayHasKey('hex', $result['colors'][$role]);
            $this->assertArrayHasKey('name', $result['colors'][$role]);
            $this->assertArrayHasKey('emotion', $result['colors'][$role]);
            $this->assertMatchesRegularExpression('/^#[A-Fa-f0-9]{6}$/', $result['colors'][$role]['hex']);
        }
    }

    /**
     * Test handling invalid API response
     */
    public function test_handle_invalid_response() {
        $params = [
            'prompt' => 'Test prompt',
            'num_colors' => 4
        ];

        // Mock an invalid response
        $this->mock_http_response('{"invalid": "response"}');

        $result = $this->provider->generate_palette($params);
        $this->assertInstanceOf(\WP_Error::class, $result);
    }

    /**
     * Test handling API error
     */
    public function test_handle_api_error() {
        $params = [
            'prompt' => 'Test prompt',
            'num_colors' => 4
        ];

        // Mock an error response
        $this->mock_http_error('API Error');

        $result = $this->provider->generate_palette($params);
        $this->assertInstanceOf(\WP_Error::class, $result);
    }
}
