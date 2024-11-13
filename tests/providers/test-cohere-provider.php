<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Cohere_Provider;
use WP_UnitTestCase;

class Cohere_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Cohere_Provider([
            'api_key' => 'test_key'
        ]);
    }

    public function test_validate_credentials() {
        $provider = new Cohere_Provider([]);
        $this->assertWPError($provider->validate_credentials());

        $this->assertTrue($this->provider->validate_credentials());
    }

    public function test_generate_palette_validates_params() {
        $result = $this->provider->generate_palette([
            'theme' => '',
            'count' => 0
        ]);
        $this->assertWPError($result);

        $result = $this->provider->generate_palette([
            'theme' => 'forest',
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

    public function test_generate_palette_integration() {
        if (!getenv('COHERE_API_KEY')) {
            $this->markTestSkipped('Cohere API key not configured');
        }

        $live_provider = new Cohere_Provider([
            'api_key' => getenv('COHERE_API_KEY')
        ]);

        $colors = $live_provider->generate_palette([
            'theme' => 'forest in autumn',
            'count' => 5
        ]);

        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
