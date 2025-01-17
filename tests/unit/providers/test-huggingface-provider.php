<?php declare(strict_types=1);

/**
 * HuggingFace Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\HuggingFace_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;

/**
 * Tests for the HuggingFace Provider
 */
class Test_HuggingFace_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new HuggingFace_Provider(new Provider_Config($this->get_test_credentials()));
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'model' => 'gpt2'
        ];
    }

    public function test_validate_credentials() {
        $provider = new HuggingFace_Provider(new Provider_Config([]));
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new HuggingFace_Provider(new Provider_Config(['api_key' => 'test_key']));
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

        $result = $this->provider->generate_palette([
            'base_color' => '#FF0000',
            'mode' => 'analogous',
            'count' => 5
        ]);
        // Would make API call in real scenario
        $this->assertNotInstanceOf(\WP_Error::class, $result);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('api_key', $requirements);
        $this->assertArrayHasKey('model_id', $requirements);
    }

    public function test_generate_palette() {
        // Mock the API response
        WP_Mock::userFunction('wp_remote_post')->once()->andReturn([
            'response' => ['code' => 200],
            'body' => json_encode([
                'generated_text' => json_encode(['colors' => ['#FF0000', '#00FF00', '#0000FF']])
            ])
        ]);

        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertIsArray($colors);
        $this->assertCount(3, $colors);
    }
}
