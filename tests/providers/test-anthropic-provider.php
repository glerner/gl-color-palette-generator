<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Anthropic_Provider;
use WP_Mock;

class Anthropic_Provider_Test extends \WP_Mock\Tools\TestCase {
    protected $provider;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new Anthropic_Provider(['api_key' => 'test_key']);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
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

        $colors = $this->provider->generate_palette(['prompt' => 'test', 'count' => 3]);
        $this->assertIsArray($colors);
        $this->assertCount(3, $colors);
    }
}
