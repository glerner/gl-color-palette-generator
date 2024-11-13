<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Anthropic_Provider;
use WP_UnitTestCase;

class Anthropic_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Anthropic_Provider(['api_key' => 'test_key']);
    }

    public function test_validate_credentials() {
        $provider = new Anthropic_Provider([]);
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
    }
} 
