<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Palm_Provider;
use WP_Mock\Tools\TestCase;

class Palm_Provider_Test extends TestCase {
    protected $provider;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new Palm_Provider(['api_key' => 'test_key']);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_validate_credentials() {
        $provider = new Palm_Provider([]);
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $this->assertTrue($this->provider->validate_credentials());
    }

    public function test_generate_palette_with_invalid_params() {
        $result = $this->provider->generate_palette([
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
                'candidates' => [
                    [
                        'content' => json_encode(['colors' => ['#FF0000', '#00FF00', '#0000FF']])
                    ]
                ]
            ])
        ]);

        $colors = $this->provider->generate_palette(['prompt' => 'test', 'count' => 3]);
        $this->assertIsArray($colors);
        $this->assertCount(3, $colors);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('api_key', $requirements);
    }

    public function test_api_url_format() {
        $reflection = new \ReflectionClass($this->provider);
        $property = $reflection->getProperty('api_url');
        $property->setAccessible(true);

        $this->assertStringContainsString('generativelanguage.googleapis.com', $property->getValue($this->provider));
    }
} 
