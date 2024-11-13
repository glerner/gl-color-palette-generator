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
        $this->provider = new OpenAI_Provider(['api_key' => 'test_key']);
    }

    public function test_validate_credentials() {
        $provider = new OpenAI_Provider([]);
        $this->assertWPError($provider->validate_credentials());

        $provider = new OpenAI_Provider(['api_key' => 'test_key']);
        $this->assertTrue($provider->validate_credentials());
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
        // Note: This would actually make an API call, so we should mock it
        $this->assertNotWPError($result);
    }

    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('api_key', $requirements);
    }
} 
