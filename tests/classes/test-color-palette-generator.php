<?php
/**
 * Color Palette Generator Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Classes
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Classes;

use GL_Color_Palette_Generator\Tests\Test_Case;
use GL_Color_Palette_Generator\Classes\Color_Palette_Generator;
use GL_Color_Palette_Generator\Classes\Color_Palette_Formatter;

/**
 * Tests for the Color Palette Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Classes
 */
class Test_Color_Palette_Generator extends Test_Case {
    private Color_Palette_Generator $generator;
    private Color_Palette_Formatter $formatter;

    public function setUp(): void {
        $this->formatter = new Color_Palette_Formatter();
        $this->generator = new Color_Palette_Generator($this->formatter);
    }

    public function test_generate_palette_default(): void {
        $palette = $this->generator->generatePalette();

        $this->assertCount(5, $palette->getColors()); // Default count
        $this->assertNotEmpty($palette->getName());
        $this->assertArrayHasKey('algorithm', $palette->getMetadata());
    }

    /**
     * @dataProvider algorithmProvider
     */
    public function test_generate_palette_algorithms(string $algorithm): void {
        $options = ['algorithm' => $algorithm, 'base_color' => '#FF0000'];
        $palette = $this->generator->generatePalette($options);

        $this->assertEquals($algorithm, $palette->getMetadata()['algorithm']);
        $this->assertNotEmpty($palette->getColors());
    }

    public function algorithmProvider(): array {
        return [
            ['complementary'],
            ['analogous'],
            ['triadic'],
            ['monochromatic'],
            ['split_complementary'],
            ['tetradic'],
            ['random']
        ];
    }

    public function test_generate_complementary(): void {
        $base_color = '#FF0000';
        $palette = $this->generator->generateComplementary($base_color);
        $colors = $palette->getColors();

        $this->assertContains($base_color, $colors);
        $this->assertContains('#00FFFF', $colors); // Cyan is complement of Red
    }

    public function test_generate_analogous(): void {
        $base_color = '#FF0000';
        $palette = $this->generator->generateAnalogous($base_color);
        $colors = $palette->getColors();

        $this->assertContains($base_color, $colors);
        $this->assertCount(5, $colors);
    }

    public function test_generate_triadic(): void {
        $base_color = '#FF0000';
        $palette = $this->generator->generateTriadic($base_color);
        $colors = $palette->getColors();

        $this->assertContains($base_color, $colors);
        $this->assertCount(5, $colors);
    }

    public function test_generate_monochromatic(): void {
        $base_color = '#FF0000';
        $palette = $this->generator->generateMonochromatic($base_color);
        $colors = $palette->getColors();

        $this->assertContains($base_color, $colors);
        $this->assertCount(5, $colors);

        // All colors should have same hue
        $base_hsl = $this->formatter->formatColor($base_color, 'hsl');
        foreach ($colors as $color) {
            $color_hsl = $this->formatter->formatColor($color, 'hsl');
            $this->assertEquals($base_hsl[0], $color_hsl[0]);
        }
    }

    public function test_custom_options(): void {
        $options = [
            'count' => 3,
            'saturation_range' => [80, 100],
            'lightness_range' => [40, 60],
            'name' => 'Custom Palette'
        ];

        $palette = $this->generator->generatePalette($options);

        $this->assertCount(3, $palette->getColors());
        $this->assertEquals('Custom Palette', $palette->getName());
    }

    public function test_invalid_algorithm(): void {
        $this->expectException(\InvalidArgumentException::class);

        $options = ['algorithm' => 'invalid_algorithm'];
        $this->generator->generatePalette($options);
    }

    public function test_get_available_algorithms(): void {
        $algorithms = $this->generator->getAvailableAlgorithms();

        $this->assertIsArray($algorithms);
        $this->assertContains('complementary', $algorithms);
        $this->assertContains('analogous', $algorithms);
        $this->assertContains('triadic', $algorithms);
    }

    public function test_get_default_options(): void {
        $options = $this->generator->getDefaultOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('count', $options);
        $this->assertArrayHasKey('saturation_range', $options);
        $this->assertArrayHasKey('lightness_range', $options);
    }

    public function test_color_variations(): void {
        $base_color = '#FF0000';
        $options = ['count' => 10];

        $palette = $this->generator->generatePalette(
            array_merge($options, ['base_color' => $base_color])
        );

        $this->assertCount(10, $palette->getColors());
        $this->assertContains($base_color, $palette->getColors());
    }
}
