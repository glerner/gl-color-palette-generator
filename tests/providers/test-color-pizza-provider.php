<?php
declare(strict_types=1);

/**
 * Color Pizza Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Tests\Providers\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Color_Pizza_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Mock;

/**
 * Tests for the Color Pizza Provider
 */
class Test_Color_Pizza_Provider extends Test_Provider_Mock {
    protected Provider $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Color_Pizza_Provider(new Provider_Config($this->get_test_credentials()));
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'base_url' => 'https://api.color.pizza/v1'
        ];
    }

    /**
     * Test generating a palette
     */
    public function test_generate_palette() {
        $params = [
            'prompt' => 'Modern tech company',
            'count' => 5,
            'format' => 'hex'
        ];

        $colors = $this->provider->generate_palette($params);
        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
    }

    protected function test_get_random_colors() {
        $colors = $this->provider->get_random_colors(5);
        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }

    protected function test_get_random_colors_with_invalid_count() {
        $colors = $this->provider->get_random_colors(0);
        $this->assertIsArray($colors);
        $this->assertEmpty($colors);

        $colors = $this->provider->get_random_colors(-1);
        $this->assertIsArray($colors);
        $this->assertEmpty($colors);
    }

    protected function test_get_random_colors_with_large_count() {
        $colors = $this->provider->get_random_colors(100);
        $this->assertIsArray($colors);
        $this->assertLessThanOrEqual(50, count($colors)); // Should cap at 50 colors
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }
}
