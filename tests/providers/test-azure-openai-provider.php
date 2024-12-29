<?php declare(strict_types=1);

/**
 * Azure OpenAI Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Azure_OpenAI_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use WP_Mock;

/**
 * Azure OpenAI Provider test case
 */
class Test_Azure_OpenAI_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Azure_OpenAI_Provider(new Provider_Config($this->get_test_credentials()));
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'endpoint' => 'https://test.openai.azure.com',
            'deployment' => 'test-deployment',
            'model' => 'gpt-4'
        ];
    }

    public function test_validate_credentials() {
        $provider = new Azure_OpenAI_Provider(new Provider_Config([]));
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new Azure_OpenAI_Provider(new Provider_Config(['api_key' => 'test']));
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new Azure_OpenAI_Provider(new Provider_Config(['api_key' => 'test', 'endpoint' => 'test']));
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $this->assertTrue($this->provider->validate_credentials());
    }

    public function test_generate_palette_validates_params() {
        $result = $this->provider->generate_palette([
            'base_color' => 'invalid',
            'mode' => 'invalid',
            'count' => 0
        ]);
        $this->assertInstanceOf(\WP_Error::class, $result);
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

    public function test_custom_endpoint() {
        $config = new Provider_Config([
            'api_key' => 'test_key_123',
            'model' => 'gpt-4',
            'endpoint' => 'https://custom-azure-endpoint.com'
        ]);
        
        $provider = new Azure_OpenAI_Provider($config);
        
        // Mock successful response with custom endpoint
        $mock_response = $this->get_mock_palette_response();
        WP_Mock::userFunction('wp_remote_post')
            ->with(
                'https://custom-azure-endpoint.com',
                \Mockery::any()
            )
            ->andReturn([
                'response' => ['code' => 200],
                'body' => json_encode($mock_response)
            ]);

        $result = $provider->generate_palette([
            'prompt' => 'Test prompt',
            'num_colors' => 4
        ]);
        
        $this->assert_palette_structure($result);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertArrayHasKey('api_key', $requirements);
        $this->assertArrayHasKey('endpoint', $requirements);
        $this->assertArrayHasKey('deployment_name', $requirements);
    }
}
