<?php declare(strict_types=1);

/**
 * Google PaLM Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Providers\Google_PaLM_Provider;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use WP_Mock;

/**
 * Google PaLM Provider test case
 */
class Test_Google_PaLM_Provider extends Test_Provider_Mock {
    public function setUp(): void {
        parent::setUp();
        $this->provider = new Google_PaLM_Provider($this->get_test_credentials());
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'model' => 'text-bison-001'
        ];
    }

    public function test_validate_credentials() {
        $provider = new Google_PaLM_Provider([]);
        $this->assertInstanceOf(\WP_Error::class, $provider->validate_credentials());

        $provider = new Google_PaLM_Provider(['api_key' => 'test']);
        $this->assertTrue($provider->validate_credentials());
    }

    public function test_generate_palette() {
        $this->mock_http_request([
            'candidates' => [
                [
                    'output' => json_encode([
                        'colors' => [
                            '#FF0000',
                            '#00FF00',
                            '#0000FF'
                        ]
                    ])
                ]
            ]
        ]);

        $palette = $this->provider->generate_palette('Create a vibrant color palette');
        $this->assertCount(3, $palette);
        $this->assertEquals('#FF0000', $palette[0]);
        $this->assertEquals('#00FF00', $palette[1]);
        $this->assertEquals('#0000FF', $palette[2]);
    }

    public function test_generate_palette_error() {
        $this->mock_http_request(null, true);
        $this->expectException(PaletteGenerationException::class);
        $this->provider->generate_palette('Create a vibrant color palette');
    }
}
