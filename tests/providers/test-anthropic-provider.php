<?php declare(strict_types=1);

/**
 * Anthropic Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Anthropic_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use WP_Mock;

/**
 * Tests for the Anthropic Provider
 */
class Test_Anthropic_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    public function setUp(): void
    {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new Anthropic_Provider($this->get_test_credentials());
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    protected function get_test_credentials(): array
    {
        return ['api_key' => 'test_key'];
    }

    /**
     * Test generating a palette
     */
    public function test_generate_palette(): void {
        $params = [
            'prompt' => 'Modern tech company',
            'count' => 5,
            'format' => 'hex'
        ];

        // Mock the API response
        WP_Mock::userFunction('wp_remote_post')->andReturn([
            'response' => ['code' => 200],
            'body' => json_encode([
                'content' => [
                    [
                        'text' => json_encode([
                            '#2C3E50',
                            '#E74C3C',
                            '#ECF0F1',
                            '#3498DB',
                            '#2ECC71'
                        ])
                    ]
                ]
            ])
        ]);

        $colors = $this->provider->generate_palette($params);
        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
    }
}
