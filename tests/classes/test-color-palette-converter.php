<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteConverter;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteConverterTest extends TestCase {
    private $converter;
    private $formatter;
    private $palette;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->converter = new ColorPaletteConverter($this->formatter);
        $this->palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ]);
    }

    public function test_convert_to_css_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'css');

        / Assert
        $this->assertStringContainsString(':root {', $result);
        $this->assertStringContainsString('--color-1: #FF0000;', $result);
        $this->assertStringContainsString('--color-2: #00FF00;', $result);
        $this->assertStringContainsString('--color-3: #0000FF;', $result);
    }

    public function test_convert_to_scss_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'scss');

        / Assert
        $this->assertStringContainsString('$color-1: #FF0000;', $result);
        $this->assertStringContainsString('$color-2: #00FF00;', $result);
        $this->assertStringContainsString('$color-3: #0000FF;', $result);
    }

    public function test_convert_to_less_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'less');

        / Assert
        $this->assertStringContainsString('@color-1: #FF0000;', $result);
        $this->assertStringContainsString('@color-2: #00FF00;', $result);
        $this->assertStringContainsString('@color-3: #0000FF;', $result);
    }

    public function test_convert_to_json_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'json');
        $data = json_decode($result, true);

        / Assert
        $this->assertIsArray($data);
        $this->assertEquals('Test Palette', $data['name']);
        $this->assertCount(3, $data['colors']);
        $this->assertEquals('#FF0000', $data['colors'][0]['hex']);
        $this->assertArrayHasKey('rgb', $data['colors'][0]);
        $this->assertArrayHasKey('hsl', $data['colors'][0]);
    }

    public function test_convert_to_svg_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'svg');

        / Assert
        $this->assertStringContainsString('<?xml version="1.0"', $result);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('fill="#FF0000"', $result);
        $this->assertStringContainsString('fill="#00FF00"', $result);
        $this->assertStringContainsString('fill="#0000FF"', $result);
    }

    public function test_convert_to_ase_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'ase');

        / Assert
        $this->assertStringStartsWith('ASEF', $result);
        $this->assertGreaterThan(20, strlen($result));
        / Additional binary format checks could be added
    }

    public function test_convert_to_gpl_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'gpl');

        / Assert
        $this->assertStringContainsString('GIMP Palette', $result);
        $this->assertStringContainsString('255   0   0', $result);
        $this->assertStringContainsString('0   255   0', $result);
        $this->assertStringContainsString('0     0 255', $result);
    }

    public function test_convert_to_act_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'act');

        / Assert
        $this->assertEquals(768, strlen($result)); / 256 colors * 3 bytes
        $this->assertEquals("\xFF\x00\x00", substr($result, 0, 3)); / First color (red)
    }

    public function test_convert_to_sketchpalette_format(): void {
        / Act
        $result = $this->converter->convert_palette($this->palette, 'sketchpalette');
        $data = json_decode($result, true);

        / Assert
        $this->assertIsArray($data);
        $this->assertArrayHasKey('colors', $data);
        $this->assertCount(3, $data['colors']);
        $this->assertEquals(1, $data['colors'][0]['red']);
        $this->assertEquals(0, $data['colors'][0]['green']);
        $this->assertEquals(0, $data['colors'][0]['blue']);
    }

    public function test_convert_with_custom_options(): void {
        / Arrange
        $options = [
            'prefix' => 'theme',
            'names' => ['primary', 'secondary', 'accent'],
            'width' => 200,
            'height' => 150
        ];

        / Test CSS format with options
        $css = $this->converter->convert_palette($this->palette, 'css', $options);
        $this->assertStringContainsString('--theme-primary:', $css);

        / Test SVG format with options
        $svg = $this->converter->convert_palette($this->palette, 'svg', $options);
        $this->assertStringContainsString('width="200"', $svg);
        $this->assertStringContainsString('height="150"', $svg);
    }

    public function test_convert_handles_empty_palette(): void {
        / Arrange
        $empty_palette = new ColorPalette([
            'name' => 'Empty',
            'colors' => []
        ]);

        / Test various formats
        $formats = ['css', 'scss', 'less', 'json', 'svg', 'gpl'];
        foreach ($formats as $format) {
            $result = $this->converter->convert_palette($empty_palette, $format);
            $this->assertNotEmpty($result);
        }
    }

    public function test_convert_handles_single_color(): void {
        / Arrange
        $single_color = new ColorPalette([
            'name' => 'Single',
            'colors' => ['#FF0000']
        ]);

        / Test various formats
        $formats = ['css', 'scss', 'less', 'json', 'svg', 'gpl'];
        foreach ($formats as $format) {
            $result = $this->converter->convert_palette($single_color, $format);
            $this->assertStringContainsString('FF0000', $result);
        }
    }

    public function test_convert_throws_exception_for_invalid_format(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert_palette($this->palette, 'invalid_format');
    }

    /**
     * @dataProvider colorNameFormatProvider
     */
    public function test_color_name_generation(
        array $options,
        array $expected_names
    ): void {
        / Act
        $css = $this->converter->convert_palette($this->palette, 'css', $options);

        / Assert
        foreach ($expected_names as $name) {
            $this->assertStringContainsString("--{$options['prefix']}-{$name}:", $css);
        }
    }

    public function colorNameFormatProvider(): array {
        return [
            'numeric_names' => [
                ['prefix' => 'color'],
                ['1', '2', '3']
            ],
            'custom_names' => [
                [
                    'prefix' => 'theme',
                    'names' => ['primary', 'secondary', 'accent']
                ],
                ['primary', 'secondary', 'accent']
            ],
            'formatted_names' => [
                [
                    'prefix' => 'palette',
                    'name_format' => 'color-%d'
                ],
                ['color-1', 'color-2', 'color-3']
            ]
        ];
    }

    public function test_binary_format_integrity(): void {
        / Test ASE format
        $ase = $this->converter->convert_palette($this->palette, 'ase');
        $this->assertEquals('ASEF', substr($ase, 0, 4));
        $this->assertEquals(pack('N', 3), substr($ase, 8, 4)); / 3 colors

        / Test ACT format
        $act = $this->converter->convert_palette($this->palette, 'act');
        $this->assertEquals(768, strlen($act)); / 256 colors * 3 bytes
    }

    public function test_format_specific_features(): void {
        / Test JSON with metadata
        $palette_with_meta = new ColorPalette([
            'name' => 'Meta Test',
            'colors' => ['#FF0000'],
            'metadata' => ['author' => 'Test User']
        ]);
        $json = $this->converter->convert_palette($palette_with_meta, 'json');
        $data = json_decode($json, true);
        $this->assertArrayHasKey('metadata', $data);
        $this->assertEquals('Test User', $data['metadata']['author']);

        / Test Sketch palette version info
        $sketch = $this->converter->convert_palette($this->palette, 'sketchpalette');
        $data = json_decode($sketch, true);
        $this->assertArrayHasKey('compatibleVersion', $data);
        $this->assertArrayHasKey('pluginVersion', $data);
    }
} 
