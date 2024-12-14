<?php
declare(strict_types=1);

/**
 * Tests for the Variation Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Generators
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Generators;

use GL_Color_Palette_Generator\Generators\Variation_Generator;
use WP_Mock;
use WP_Mock\Tools\TestCase;

/**
 * Test class for Variation_Generator
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Generators\Variation_Generator
 */
class Test_Variation_Generator extends TestCase {
    /**
     * Instance of Variation_Generator for testing
     *
     * @var Variation_Generator
     */
    private Variation_Generator $variation_generator;

    /**
     * Set up test environment
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->variation_generator = new Variation_Generator();
    }

    /**
     * Tear down test environment
     *
     * @return void
     */
    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * Test generating color variations
     *
     * @covers ::generate_variations
     * @return void
     */
    public function test_generate_variations(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $variations = $this->variation_generator->generate_variations($colors);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertIsArray($variation);
            $this->assertCount(count($colors), $variation);
            foreach ($variation as $color) {
                $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
            }
        }
    }

    /**
     * Test generating color variations with a specific type
     *
     * @covers ::generate_variations
     * @return void
     */
    public function test_generate_variations_with_type(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $type = 'analogous';
        
        $variations = $this->variation_generator->generate_variations($colors, ['type' => $type]);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
    }

    /**
     * Test generating color variations with a specific count
     *
     * @covers ::generate_variations
     * @return void
     */
    public function test_generate_variations_with_count(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $count = 3;
        
        $variations = $this->variation_generator->generate_variations($colors, ['count' => $count]);
        
        $this->assertIsArray($variations);
        $this->assertCount($count, $variations);
    }

    /**
     * Test generating monochromatic color variations
     *
     * @covers ::generate_monochromatic_variations
     * @return void
     */
    public function test_generate_monochromatic_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_monochromatic_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    /**
     * Test generating analogous color variations
     *
     * @covers ::generate_analogous_variations
     * @return void
     */
    public function test_generate_analogous_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_analogous_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    /**
     * Test generating complementary color variations
     *
     * @covers ::generate_complementary_variations
     * @return void
     */
    public function test_generate_complementary_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_complementary_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    /**
     * Test generating split complementary color variations
     *
     * @covers ::generate_split_complementary_variations
     * @return void
     */
    public function test_generate_split_complementary_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_split_complementary_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    /**
     * Test generating triadic color variations
     *
     * @covers ::generate_triadic_variations
     * @return void
     */
    public function test_generate_triadic_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_triadic_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    /**
     * Test generating tetradic color variations
     *
     * @covers ::generate_tetradic_variations
     * @return void
     */
    public function test_generate_tetradic_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_tetradic_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    /**
     * Test adjusting color saturation
     *
     * @covers ::adjust_saturation
     * @return void
     */
    public function test_adjust_saturation(): void {
        $color = '#FF0000';
        $amount = 0.5;
        
        $adjusted = $this->variation_generator->adjust_saturation($color, $amount);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $adjusted);
    }

    /**
     * Test adjusting color lightness
     *
     * @covers ::adjust_lightness
     * @return void
     */
    public function test_adjust_lightness(): void {
        $color = '#FF0000';
        $amount = 0.5;
        
        $adjusted = $this->variation_generator->adjust_lightness($color, $amount);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $adjusted);
    }

    /**
     * Test rotating color hue
     *
     * @covers ::rotate_hue
     * @return void
     */
    public function test_rotate_hue(): void {
        $color = '#FF0000';
        $degrees = 180;
        
        $rotated = $this->variation_generator->rotate_hue($color, $degrees);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $rotated);
    }
}
