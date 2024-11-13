<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\HuggingFace_Provider;
use WP_UnitTestCase;

class HuggingFace_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new HuggingFace_Provider([
            'api_key' => 'test_key',
            'model_id' => 'test_model'
        ]);
    }

    public function test_validate_credentials() {
        $provider = new HuggingFace_Provider([]);
        $this->assertWPError($provider->validate_credentials());

        $provider = new HuggingFace_Provider(['api_key' => 'test_key']);
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
            'theme' => 'spring garden',
            'count' => 5
        ]);
        // Would make API call in real scenario
        $this->assertNotWPError($result);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('api_key', $requirements);
        $this->assertArrayHasKey('model_id', $requirements);
    }

    public function test_generate_palette_integration() {
        if (!getenv('HUGGINGFACE_API_KEY') || !getenv('HUGGINGFACE_MODEL_ID')) {
            $this->markTestSkipped('HuggingFace credentials not configured');
        }

        $live_provider = new HuggingFace_Provider([
            'api_key' => getenv('HUGGINGFACE_API_KEY'),
            'model_id' => getenv('HUGGINGFACE_MODEL_ID')
        ]);

        $colors = $live_provider->generate_palette([
            'theme' => 'spring garden',
            'count' => 5
        ]);

        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
