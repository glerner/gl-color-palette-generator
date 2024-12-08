<?php
/**
 * Color Palette Formatter Tests
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteFormatterTest extends TestCase {
    private ColorPaletteFormatter $formatter;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
    }

    /**
     * @dataProvider validColorFormatProvider
     */
    public function test_format_color_valid_formats(string $input, string $format, string $expected): void {
        $result = $this->formatter->formatColor($input, $format);
        $this->assertEquals($expected, $result);
    }

    public function validColorFormatProvider(): array {
        return [
            'hex_to_rgb' => [
                '#FF0000',
                'rgb',
                'rgb(255, 0, 0)'
            ],
            'rgb_to_hex' => [
                'rgb(255, 0, 0)',
                'hex',
                '#FF0000'
            ],
            'hex_to_hsl' => [
                '#FF0000',
                'hsl',
                'hsl(0, 100%, 50%)'
            ],
            'hsl_to_hex' => [
                'hsl(0, 100%, 50%)',
                'hex',
                '#FF0000'
            ],
            'rgb_to_rgba' => [
                'rgb(255, 0, 0)',
                'rgba',
                'rgba(255, 0, 0, 1.00)'
            ],
            'hsl_to_hsla' => [
                'hsl(0, 100%, 50%)',
                'hsla',
                'hsla(0, 100%, 50%, 1.00)'
            ],
            'short_hex_to_full_hex' => [
                '#F00',
                'hex',
                '#FF0000'
            ]
        ];
    }

    /**
     * @dataProvider invalidColorFormatProvider
     */
    public function test_format_color_invalid_formats(string $input, string $format): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->formatter->formatColor($input, $format);
    }

    public function invalidColorFormatProvider(): array {
        return [
            'invalid_hex' => ['#GG0000', 'hex'],
            'invalid_rgb' => ['rgb(256, 0, 0)', 'rgb'],
            'invalid_hsl' => ['hsl(361, 100%, 50%)', 'hsl'],
            'invalid_format' => ['#FF0000', 'invalid'],
            'malformed_rgb' => ['rgb(255 0 0)', 'rgb'],
            'malformed_hsl' => ['hsl(0,100%,50%)', 'hsl']
        ];
    }

    /**
     * @dataProvider validFormatCheckProvider
     */
    public function test_is_valid_format(string $color, string $format, bool $expected): void {
        $result = $this->formatter->isValidFormat($color, $format);
        $this->assertEquals($expected, $result);
    }

    public function validFormatCheckProvider(): array {
        return [
            'valid_hex' => ['#FF0000', 'hex', true],
            'valid_short_hex' => ['#F00', 'hex', true],
            'valid_rgb' => ['rgb(255, 0, 0)', 'rgb', true],
            'valid_rgba' => ['rgba(255, 0, 0, 1)', 'rgba', true],
            'valid_hsl' => ['hsl(0, 100%, 50%)', 'hsl', true],
            'valid_hsla' => ['hsla(0, 100%, 50%, 1)', 'hsla', true],
            'invalid_hex' => ['#GG0000', 'hex', false],
            'invalid_rgb' => ['rgb(256, 0, 0)', 'rgb', false],
            'invalid_hsl' => ['hsl(361, 100%, 50%)', 'hsl', false],
            'wrong_format' => ['#FF0000', 'rgb', false]
        ];
    }

    public function test_get_supported_formats(): void {
        $formats = $this->formatter->getSupportedFormats();
        $this->assertIsArray($formats);
        $this->assertContains('hex', $formats);
        $this->assertContains('rgb', $formats);
        $this->assertContains('rgba', $formats);
        $this->assertContains('hsl', $formats);
        $this->assertContains('hsla', $formats);
    }

    /**
     * @dataProvider normalizeColorProvider
     */
    public function test_normalize_color(string $input, string $expected): void {
        $result = $this->formatter->normalizeColor($input);
        $this->assertEquals($expected, $result);
    }

    public function normalizeColorProvider(): array {
        return [
            'full_hex' => ['#FF0000', '#FF0000'],
            'short_hex' => ['#F00', '#FF0000'],
            'no_hash' => ['FF0000', '#FF0000'],
            'rgb' => ['rgb(255, 0, 0)', '#FF0000'],
            'rgba' => ['rgba(255, 0, 0, 1)', '#FF0000'],
            'hsl' => ['hsl(0, 100%, 50%)', '#FF0000'],
            'hsla' => ['hsla(0, 100%, 50%, 1)', '#FF0000'],
            'lowercase' => ['#ff0000', '#FF0000'],
            'mixed_case' => ['#Ff0000', '#FF0000']
        ];
    }

    public function test_edge_cases(): void {
        // Black
        $this->assertEquals('#000000', $this->formatter->formatColor('rgb(0, 0, 0)', 'hex'));
        $this->assertEquals('hsl(0, 0%, 0%)', $this->formatter->formatColor('#000000', 'hsl'));

        // White
        $this->assertEquals('#FFFFFF', $this->formatter->formatColor('rgb(255, 255, 255)', 'hex'));
        $this->assertEquals('hsl(0, 0%, 100%)', $this->formatter->formatColor('#FFFFFF', 'hsl'));

        // Gray (no saturation)
        $this->assertEquals('hsl(0, 0%, 50%)', $this->formatter->formatColor('#808080', 'hsl'));
    }

    public function test_color_conversion_precision(): void {
        // Test RGB to HSL to RGB conversion maintains color accuracy
        $original = '#FF0000';
        $hsl = $this->formatter->formatColor($original, 'hsl');
        $back_to_hex = $this->formatter->formatColor($hsl, 'hex');
        $this->assertEquals($original, $back_to_hex);

        // Test with a more complex color
        $original = '#8A2BE2'; // Blue Violet
        $rgb = $this->formatter->formatColor($original, 'rgb');
        $back_to_hex = $this->formatter->formatColor($rgb, 'hex');
        $this->assertEquals($original, $back_to_hex);
    }

    /**
     * @dataProvider hslConversionProvider
     */
    public function test_hsl_conversion_accuracy(string $hex, array $expected_hsl): void {
        $hsl = $this->formatter->formatColor($hex, 'hsl');
        preg_match('/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/', $hsl, $matches);
        array_shift($matches);
        $actual_hsl = array_map('intval', $matches);

        // Allow for small rounding differences
        foreach ($actual_hsl as $i => $value) {
            $this->assertEqualsWithDelta($expected_hsl[$i], $value, 1);
        }
    }

    public function hslConversionProvider(): array {
        return [
            'red' => ['#FF0000', [0, 100, 50]],
            'green' => ['#00FF00', [120, 100, 50]],
            'blue' => ['#0000FF', [240, 100, 50]],
            'yellow' => ['#FFFF00', [60, 100, 50]],
            'cyan' => ['#00FFFF', [180, 100, 50]],
            'magenta' => ['#FF00FF', [300, 100, 50]],
            'gray' => ['#808080', [0, 0, 50]]
        ];
    }
}
