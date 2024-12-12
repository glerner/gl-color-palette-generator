<?php
namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Providers\Azure_OpenAI_Provider;
use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use WP_Mock;

class Test_Azure_OpenAI_Provider extends Test_Provider_Mock {
    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key',
            'endpoint' => 'https://test-endpoint.openai.azure.com',
            'deployment' => 'test-deployment'
        ];
    }

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new Azure_OpenAI_Provider($this->get_test_credentials());
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_validate_credentials() {
        $provider = new Azure_OpenAI_Provider([]);
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new Azure_OpenAI_Provider(['api_key' => 'test']);
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new Azure_OpenAI_Provider(['api_key' => 'test', 'endpoint' => 'test']);
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
        // Mock the API response
        WP_Mock::userFunction('wp_remote_post')->once()->andReturn([
            'response' => ['code' => 200],
            'body' => json_encode([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode(['colors' => ['#FF0000', '#00FF00', '#0000FF']])
                        ]
                    ]
                ]
            ])
        ]);

        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertIsArray($colors);
        $this->assertCount(3, $colors);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertArrayHasKey('api_key', $requirements);
        $this->assertArrayHasKey('endpoint', $requirements);
        $this->assertArrayHasKey('deployment_name', $requirements);
    }
}
