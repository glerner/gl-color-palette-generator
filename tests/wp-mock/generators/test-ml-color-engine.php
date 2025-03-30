<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Generators;

use GL_Color_Palette_Generator\Generators\ML_Color_Engine;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock\Tools\TestCase;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

class Test_ML_Color_Engine extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
    protected ML_Color_Engine $ml_color_engine;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->ml_color_engine = new ML_Color_Engine();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_generate_palette(): void {
        $input = [
            'theme' => 'ocean',
            'mood' => 'calm',
            'count' => 5,
        ];

        $palette = $this->ml_color_engine->generate_palette($input);
        
        $this->assertIsArray($palette);
        $this->assertCount(5, $palette);
        foreach ($palette as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }

    public function test_generate_palette_with_constraints(): void {
        $input = [
            'theme' => 'forest',
            'mood' => 'energetic',
            'count' => 4,
            'constraints' => [
                'include_colors' => ['#00FF00'],
                'exclude_colors' => ['#FF0000'],
                'min_contrast' => 4.5,
            ],
        ];

        $palette = $this->ml_color_engine->generate_palette($input);
        
        $this->assertIsArray($palette);
        $this->assertCount(4, $palette);
        $this->assertContains('#00FF00', $palette);
        $this->assertNotContains('#FF0000', $palette);
    }

    public function test_validate_color(): void {
        $this->assertTrue($this->ml_color_engine->validate_color('#FF0000'));
        $this->assertTrue($this->ml_color_engine->validate_color('#00FF00'));
        $this->assertTrue($this->ml_color_engine->validate_color('#0000FF'));
        
        $this->assertFalse($this->ml_color_engine->validate_color('invalid'));
        $this->assertFalse($this->ml_color_engine->validate_color('#XYZ'));
        $this->assertFalse($this->ml_color_engine->validate_color('#FFFFFFF'));
    }

    public function test_calculate_contrast(): void {
        $contrast = $this->ml_color_engine->calculate_contrast('#FFFFFF', '#000000');
        $this->assertEquals(21, $contrast);
        
        $contrast = $this->ml_color_engine->calculate_contrast('#FF0000', '#FFFFFF');
        $this->assertGreaterThan(0, $contrast);
    }

    public function test_analyze_color_harmony(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $harmony = $this->ml_color_engine->analyze_color_harmony($colors);
        
        $this->assertIsArray($harmony);
        $this->assertArrayHasKey('type', $harmony);
        $this->assertArrayHasKey('score', $harmony);
    }

    public function test_get_color_properties(): void {
        $color = '#FF0000';
        $properties = $this->ml_color_engine->get_color_properties($color);
        
        $this->assertIsArray($properties);
        $this->assertArrayHasKey('hue', $properties);
        $this->assertArrayHasKey('saturation', $properties);
        $this->assertArrayHasKey('lightness', $properties);
    }

    public function test_generate_variations(): void {
        $base_color = '#FF0000';
        $count = 3;
        
        $variations = $this->ml_color_engine->generate_variations($base_color, $count);
        
        $this->assertIsArray($variations);
        $this->assertCount($count, $variations);
        foreach ($variations as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $color);
        }
    }

    public function test_get_color_name(): void {
        $color = '#FF0000';
        $name = $this->ml_color_engine->get_color_name($color);
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_get_complementary_color(): void {
        $color = '#FF0000';
        $complementary = $this->ml_color_engine->get_complementary_color($color);
        
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $complementary);
        $this->assertNotEquals($color, $complementary);
    }

    public function test_get_analogous_colors(): void {
        $color = '#FF0000';
        $analogous = $this->ml_color_engine->get_analogous_colors($color);
        
        $this->assertIsArray($analogous);
        $this->assertCount(2, $analogous);
        foreach ($analogous as $analog) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $analog);
        }
    }
}
