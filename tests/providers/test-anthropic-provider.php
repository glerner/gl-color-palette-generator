<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Anthropic_Provider;
use WP_UnitTestCase;

class Anthropic_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Anthropic_Provider([
            'api_key' => 'test_key'
        ]);
    }

    public function test_validate_credentials() {
        $provider = new Anthropic_Provider([]);
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
            'theme' => 'northern lights',
            'count' => 5
        ]);
        // Would make API call in real scenario
        $this->assertNotWPError($result);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('api_key', $requirements);
        $this->assertArrayHasKey('model', $requirements);
    }

    public function test_generate_palette_integration() {
        if (!getenv('ANTHROPIC_API_KEY')) {
            $this->markTestSkipped('Anthropic API key not configured');
        }

        $live_provider = new Anthropic_Provider([
            'api_key' => getenv('ANTHROPIC_API_KEY'),
            'model' => getenv('ANTHROPIC_MODEL') ?? 'claude-3-opus'
        ]);

        $colors = $live_provider->generate_palette([
            'theme' => 'northern lights',
            'count' => 5
        ]);

        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
