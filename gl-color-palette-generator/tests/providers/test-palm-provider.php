<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Palm_Provider;
use WP_UnitTestCase;

class Palm_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Palm_Provider(['api_key' => 'test_key']);
    }

    public function test_validate_credentials() {
        $provider = new Palm_Provider([]);
        $this->assertWPError($provider->validate_credentials());

        $this->assertTrue($this->provider->validate_credentials());
    }

    public function test_generate_palette_validates_params() {
        $result = $this->provider->generate_palette([
            'base_color' => 'invalid',
            'mode' => 'invalid',
            'count' => 0
        ]);
        $this->assertWPError($result);

        $result = $this->provider->generate_palette([
            'base_color' => '#FF0000',
            'mode' => 'analogous',
            'count' => 5
        ]);
        // Would make API call in real scenario
        $this->assertNotWPError($result);
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
