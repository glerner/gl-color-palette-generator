<?php
declare(strict_types=1);

/**
 * Color Pizza Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Providers\Color_Pizza_Provider;
use WP_Mock;

/**
 * Color Pizza Provider test case
 */
class Test_Color_Pizza_Provider extends \WP_Mock\Tools\TestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new Color_Pizza_Provider();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_get_random_colors() {
        $colors = $this->provider->get_random_colors(5);
        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }

    public function test_get_random_colors_with_invalid_count() {
        $colors = $this->provider->get_random_colors(0);
        $this->assertIsArray($colors);
        $this->assertEmpty($colors);

        $colors = $this->provider->get_random_colors(-1);
        $this->assertIsArray($colors);
        $this->assertEmpty($colors);
    }

    public function test_get_random_colors_with_large_count() {
        $colors = $this->provider->get_random_colors(100);
        $this->assertIsArray($colors);
        $this->assertLessThanOrEqual(50, count($colors)); // Should cap at 50 colors
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }
}
