<?php declare(strict_types=1);

/**
 * Anthropic Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Anthropic_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Mock;

/**
 * Tests for the Anthropic Provider
 */
class Test_Anthropic_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    public function setUp(): void
    {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new Anthropic_Provider($this->get_test_credentials());
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    protected function get_test_credentials(): array
    {
        return ['api_key' => 'test_key'];
    }

    /**
     * Test generating a palette
     */
    public function test_generate_palette(): void {
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
        $this->mock_http_response(json_encode([
            'content' => [
                'text' => json_encode($mock_response)
            ]
        ]));

        $result = $this->provider->generate_palette($params);
        $this->assert_palette_structure($result);
    }

    public function test_handle_invalid_response(): void {
        $params = [
            'prompt' => 'Test prompt',
            'num_colors' => 4
        ];

        // Mock an invalid response
        $this->mock_http_response('{"content": {"text": "invalid"}}');

        $result = $this->provider->generate_palette($params);
        $this->assertInstanceOf(\WP_Error::class, $result);
    }

    public function test_handle_api_error(): void {
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
