<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Generators;

use GL_Color_Palette_Generator\Generators\Variation_Generator;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class Test_Variation_Generator extends TestCase {
    private Variation_Generator $variation_generator;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->variation_generator = new Variation_Generator();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

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

    public function test_generate_variations_with_type(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $type = 'analogous';
        
        $variations = $this->variation_generator->generate_variations($colors, ['type' => $type]);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
    }

    public function test_generate_variations_with_count(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $count = 3;
        
        $variations = $this->variation_generator->generate_variations($colors, ['count' => $count]);
        
        $this->assertIsArray($variations);
        $this->assertCount($count, $variations);
    }

    public function test_generate_monochromatic_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_monochromatic_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    public function test_generate_analogous_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_analogous_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    public function test_generate_complementary_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_complementary_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    public function test_generate_split_complementary_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_split_complementary_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    public function test_generate_triadic_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_triadic_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    public function test_generate_tetradic_variations(): void {
        $color = '#FF0000';
        $variations = $this->variation_generator->generate_tetradic_variations($color);
        
        $this->assertIsArray($variations);
        $this->assertNotEmpty($variations);
        foreach ($variations as $variation) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $variation);
        }
    }

    public function test_adjust_saturation(): void {
        $color = '#FF0000';
        $amount = 0.5;
        
        $adjusted = $this->variation_generator->adjust_saturation($color, $amount);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $adjusted);
    }

    public function test_adjust_lightness(): void {
        $color = '#FF0000';
        $amount = 0.5;
        
        $adjusted = $this->variation_generator->adjust_lightness($color, $amount);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $adjusted);
    }

    public function test_rotate_hue(): void {
        $color = '#FF0000';
        $degrees = 180;
        
        $rotated = $this->variation_generator->rotate_hue($color, $degrees);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $rotated);
    }
}
