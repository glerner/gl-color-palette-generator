<?php
namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Test_Case;
use GL_Color_Palette_Generator\Providers\Palm_Provider;
use WP_Mock;

/**
 * Tests for the Palm Provider
 */
class Test_Palm_Provider extends Test_Case {
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
                'candidates' => [
                    [
                        'content' => json_encode([
                            '#2C3E50',
                            '#E74C3C',
                            '#ECF0F1',
                            '#3498DB',
                            '#2ECC71'
                        ])
                    ]
                ]
            ])
        ]);

        $colors = $this->provider->generate_palette($params);
        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
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
