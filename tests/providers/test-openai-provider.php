<?php
/**
 * OpenAI Provider Tests
 *
 * @package GLColorPalette
 * @subpackage Tests
 */

namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\OpenAI_Provider;
use WP_UnitTestCase;

class OpenAI_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new OpenAI_Provider([
            'api_key' => 'test_key'
        ]);
    }

    public function test_validate_credentials() {
        $provider = new OpenAI_Provider([]);
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
            'theme' => 'ocean waves',
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
        if (!getenv('OPENAI_API_KEY')) {
            $this->markTestSkipped('OpenAI API key not configured');
        }

        $live_provider = new OpenAI_Provider([
            'api_key' => getenv('OPENAI_API_KEY'),
            'model' => getenv('OPENAI_MODEL') ?? 'gpt-4'
        ]);

        $colors = $live_provider->generate_palette([
            'theme' => 'ocean waves',
            'count' => 5
        ]);

        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
