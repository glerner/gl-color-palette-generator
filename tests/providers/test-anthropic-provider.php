<?php declare(strict_types=1);

/**
 * Anthropic Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Providers\Anthropic_Provider;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use WP_Mock;

/**
 * Anthropic Provider test case
 */
class Test_Anthropic_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        
        $this->provider = new Anthropic_Provider($this->get_test_credentials());
    }

    protected function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'base_url' => 'https://api.anthropic.com/v1'
        ];
    }

    public function test_validate_credentials() {
        $provider = new Anthropic_Provider([]);
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
            'body' => json_encode(['colors' => ['#FF0000', '#00FF00', '#0000FF']])
        ]);

        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertIsArray($colors);
        $this->assertCount(3, $colors);
    }
}
