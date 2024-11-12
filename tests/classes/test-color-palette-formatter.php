<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteFormatterTest extends TestCase {
    private $formatter;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
    }

    /**
     * @dataProvider formatConversionProvider
     */
    public function test_format_color_converts_between_formats(
        string $input_color,
        string $target_format,
        string $expected
    ): void {
        // Act
        $result = $this->formatter->format_color($input_color, $target_format);

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function formatConversionProvider(): array {
        return [
            'hex_to_rgb' => [
                '#FF0000',
                'rgb',
                'rgb(255, 0, 0)'
            ],
            'hex_to_rgba' => [
                '#FF0000',
                'rgba',
                'rgba(255, 0, 0, 1)'
            ],
            'hex_to_hsl' => [
                '#FF0000',
                'hsl',
                'hsl(0, 100%, 50%)'
            ],
            'rgb_to_hex' => [
                'rgb(255, 0, 0)',
                'hex',
                '#ff0000'
            ],
            'rgb_to_hsl' => [
                'rgb(255, 0, 0)',
                'hsl',
                'hsl(0, 100%, 50%)'
            ],
            'hsl_to_hex' => [
                'hsl(0, 100%, 50%)',
                'hex',
                '#ff0000'
            ],
            'hsl_to_rgb' => [
                'hsl(0, 100%, 50%)',
                'rgb',
                'rgb(255, 0, 0)'
            ],
            'short_hex_to_rgb' => [
                '#F00',
                'rgb',
                'rgb(255, 0, 0)'
            ],
            'grayscale_hsl_to_rgb' => [
                'hsl(0, 0%, 50%)',
                'rgb',
                'rgb(128, 128, 128)'
            ]
        ];
    }

    /**
     * @dataProvider formatDetectionProvider
     */
    public function test_detect_format_identifies_color_format(
        string $color,
        string $expected_format
    ): void {
        // Act
        $result = $this->formatter->detect_format($color);

        // Assert
        $this->assertEquals($expected_format, $result);
    }

    public function formatDetectionProvider(): array {
        return [
            'hex_6_digits' => ['#FF0000', 'hex'],
            'hex_3_digits' => ['#F00', 'hex'],
            'hex_lowercase' => ['#ff0000', 'hex'],
            'rgb' => ['rgb(255, 0, 0)', 'rgb'],
            'rgb_spaces' => ['rgb(255,0,0)', 'rgb'],
            'rgba' => ['rgba(255, 0, 0, 1)', 'rgba'],
            'rgba_decimal' => ['rgba(255, 0, 0, 0.5)', 'rgba'],
            'hsl' => ['hsl(0, 100%, 50%)', 'hsl'],
            'hsl_no_percent' => ['hsl(0, 100, 50)', 'hsl'],
            'hsla' => ['hsla(0, 100%, 50%, 1)', 'hsla']
        ];
    }

    public function test_get_supported_formats_returns_array(): void {
        // Act
        $formats = $this->formatter->get_supported_formats();

        // Assert
        $this->assertIsArray($formats);
        $this->assertContains('hex', $formats);
        $this->assertContains('rgb', $formats);
        $this->assertContains('rgba', $formats);
        $this->assertContains('hsl', $formats);
        $this->assertContains('hsla', $formats);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_format_color_validates_input(string $invalid_color): void {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->formatter->format_color($invalid_color, 'rgb');
    }

    public function invalidColorProvider(): array {
        return [
            'empty_color' => [''],
            'invalid_hex' => ['#XYZ'],
            'wrong_hex_length' => ['#FF'],
            'invalid_rgb' => ['rgb(256, 0, 0)'],
            'invalid_hsl' => ['hsl(361, 100%, 50%)'],
            'malformed_rgb' => ['rgb(255 0 0)'],
            'malformed_hsl' => ['hsl(0 100% 50%)']
        ];
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_format_color_validates_format(string $invalid_format): void {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->formatter->format_color('#FF0000', $invalid_format);
    }

    public function invalidFormatProvider(): array {
        return [
            'empty_format' => [''],
            'invalid_format' => ['invalid'],
            'numeric_format' => ['123'],
            'special_chars' => ['format@!']
        ];
    }

    public function test_format_color_handles_edge_cases(): void {
        // Test black
        $this->assertEquals(
            'rgb(0, 0, 0)',
            $this->formatter->format_color('#000000', 'rgb')
        );

        // Test white
        $this->assertEquals(
            'rgb(255, 255, 255)',
            $this->formatter->format_color('#FFFFFF', 'rgb')
        );

        // Test gray
        $this->assertEquals(
            'hsl(0, 0%, 50%)',
            $this->formatter->format_color('rgb(128, 128, 128)', 'hsl')
        );
    }

    public function test_format_color_preserves_precision(): void {
        // Test HSL to RGB conversion precision
        $hsl = 'hsl(210, 50%, 50%)';
        $rgb = $this->formatter->format_color($hsl, 'rgb');
        $back_to_hsl = $this->formatter->format_color($rgb, 'hsl');

        // Allow for small rounding differences
        $this->assertStringStartsWith('hsl(210,', $back_to_hsl);
    }

    public function test_format_color_handles_same_format(): void {
        $color = '#FF0000';
        $this->assertEquals(
            $color,
            $this->formatter->format_color($color, 'hex')
        );
    }

    /**
     * @dataProvider hslEdgeCaseProvider
     */
    public function test_hsl_edge_cases(string $hsl, string $expected_rgb): void {
        // Act
        $result = $this->formatter->format_color($hsl, 'rgb');

        // Assert
        $this->assertEquals($expected_rgb, $result);
    }

    public function hslEdgeCaseProvider(): array {
        return [
            'zero_saturation' => [
                'hsl(0, 0%, 50%)',
                'rgb(128, 128, 128)'
            ],
            'full_saturation' => [
                'hsl(0, 100%, 50%)',
                'rgb(255, 0, 0)'
            ],
            'zero_lightness' => [
                'hsl(0, 100%, 0%)',
                'rgb(0, 0, 0)'
            ],
            'full_lightness' => [
                'hsl(0, 100%, 100%)',
                'rgb(255, 255, 255)'
            ]
        ];
    }
} 
