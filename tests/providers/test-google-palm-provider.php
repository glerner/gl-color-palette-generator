<?php declare(strict_types=1);

/**
 * Google PaLM Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Google_PaLM_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use WP_Mock;

/**
 * Google PaLM Provider test case
 */
class Test_Google_PaLM_Provider extends Test_Provider_Mock {
    public function setUp(): void {
        parent::setUp();
        $this->provider = new Google_PaLM_Provider($this->get_test_credentials());
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'model' => 'text-bison-001'
        ];
    }

    public function test_validate_credentials() {
        $provider = new Google_PaLM_Provider([]);
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new Google_PaLM_Provider(['api_key' => 'test']);
        $this->assertTrue($provider->validate_credentials());
    }

    public function test_generate_palette() {
        $params = [
            'prompt' => 'Modern tech company',
            'num_colors' => 4,
            'options' => [
                'temperature' => 0.7,
                'max_tokens' => 500
            ]
        ];

        // Mock the API response
        $mock_response = $this->get_mock_palette_response();
        $this->mock_http_response(json_encode($mock_response));

        $result = $this->provider->generate_palette($params);
        $this->assert_palette_structure($result);
    }

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
