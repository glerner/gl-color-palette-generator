<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteConverter;

class ColorPaletteConverterTest extends TestCase {
    private $converter;

    public function setUp(): void {
        $this->converter = $this->createMock(ColorPaletteConverter::class);
    }

    public function test_convert_color_space_transforms_colors(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00'];
        $from = 'rgb';
        $to = 'hsl';
        $options = [
            'precision' => 2,
            'gamut' => 'clip'
        ];

        $expected = [
            'converted' => [
                ['h' => 0, 's' => 100, 'l' => 50],
                ['h' => 120, 's' => 100, 'l' => 50]
            ],
            'original' => ['#FF0000', '#00FF00'],
            'mapping' => [
                'rgb_to_hsl' => [
                    'algorithm' => 'standard',
                    'precision' => 2
                ]
            ],
            'metadata' => [
                'converted_at' => '2024-01-20T12:00:00Z',
                'color_space' => 'hsl'
            ]
        ];

        $this->converter
            ->expects($this->once())
            ->method('convert_color_space')
            ->with($colors, $from, $to, $options)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_color_space($colors, $from, $to, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('converted', $result);
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('mapping', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_convert_format_transforms_palette(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $format = 'css';
        $options = [
            'structure' => 'variables',
            'formatting' => ['prefix' => '--']
        ];

        $expected = [
            'converted' => [
                '--primary: #FF0000;',
                '--secondary: #00FF00;'
            ],
            'original' => $palette,
            'format' => [
                'type' => 'css',
                'syntax' => 'variables'
            ],
            'metadata' => [
                'converted_at' => '2024-01-20T12:00:00Z',
                'format' => 'css'
            ]
        ];

        $this->converter
            ->expects($this->once())
            ->method('convert_format')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_format($palette, $format, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('converted', $result);
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('format', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_convert_standard_transforms_compliance(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#FFFFFF']
        ];

        $standard = 'wcag2.1';
        $options = [
            'compliance' => ['level' => 'AA'],
            'mapping' => ['text' => 'foreground']
        ];

        $expected = [
            'converted' => [
                'colors' => ['#FF0000', '#FFFFFF'],
                'contrast_ratios' => ['21:1']
            ],
            'compliance' => [
                'level' => 'AA',
                'passed' => true
            ],
            'mapping' => [
                'text' => 'foreground',
                'background' => 'background'
            ],
            'metadata' => [
                'converted_at' => '2024-01-20T12:00:00Z',
                'standard' => 'wcag2.1'
            ]
        ];

        $this->converter
            ->expects($this->once())
            ->method('convert_standard')
            ->with($palette, $standard, $options)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_standard($palette, $standard, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('converted', $result);
        $this->assertArrayHasKey('compliance', $result);
        $this->assertArrayHasKey('mapping', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_convert_system_transforms_palette(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $system = 'sRGB';
        $options = [
            'mapping' => ['text' => 'foreground'],
            'compatibility' => ['text' => 'foreground']
        ];

        $expected = [
            'converted' => [
                'colors' => ['#FF0000', '#00FF00'],
                'compatibility' => ['text' => 'foreground']
            ],
            'compatibility' => [
                'text' => 'foreground'
            ],
            'mapping' => [
                'text' => 'foreground'
            ],
            'metadata' => [
                'converted_at' => '2024-01-20T12:00:00Z',
                'system' => 'sRGB'
            ]
        ];

        $this->converter
            ->expects($this->once())
            ->method('convert_system')
            ->with($palette, $system, $options)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_system($palette, $system, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('converted', $result);
        $this->assertArrayHasKey('compatibility', $result);
        $this->assertArrayHasKey('mapping', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_convert_color_transforms_single_color(): void {
        // Arrange
        $color = '#FF0000';
        $target_type = 'space';
        $target = 'HSL';
        $options = [
            'source' => 'RGB',
            'precision' => 2
        ];

        $expected = [
            'color' => 'hsl(0, 100%, 50%)',
            'original' => [
                'value' => '#FF0000',
                'space' => 'RGB'
            ],
            'conversion' => [
                'type' => 'space',
                'method' => 'rgb_to_hsl'
            ],
            'metadata' => [
                'converted_at' => '2024-01-20T12:00:00Z',
                'precision_applied' => 2
            ]
        ];

        $this->converter
            ->expects($this->once())
            ->method('convert_color')
            ->with($color, $target_type, $target, $options)
            ->willReturn($expected);

        // Act
        $result = $this->converter->convert_color($color, $target_type, $target, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('color', $result);
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('conversion', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_validate_conversion_checks_conversion_data(): void {
        // Arrange
        $conversion = [
            'color' => 'hsl(0, 100%, 50%)',
            'source' => '#FF0000',
            'space' => 'HSL'
        ];

        $rules = [
            'ranges' => [
                'hue' => [0, 360],
                'saturation' => [0, 100],
                'lightness' => [0, 100]
            ]
        ];

        $expected = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'metadata' => [
                'validated_at' => '2024-01-20T12:00:00Z',
                'rules_applied' => ['ranges']
            ]
        ];

        $this->converter
            ->expects($this->once())
            ->method('validate_conversion')
            ->with($conversion, $rules)
            ->willReturn($expected);

        // Act
        $result = $this->converter->validate_conversion($conversion, $rules);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
    }

    /**
     * @dataProvider invalidColorSpaceProvider
     */
    public function test_convert_color_space_validates_target_space(string $target_space): void {
        $palette = ['colors' => ['#FF0000']];

        $this->converter
            ->expects($this->once())
            ->method('convert_color_space')
            ->with($palette, $target_space)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert_color_space($palette, $target_space);
    }

    public function invalidColorSpaceProvider(): array {
        return [
            'empty_space' => [''],
            'invalid_space' => ['INVALID'],
            'lowercase_space' => ['rgb'],
            'numeric_space' => ['123']
        ];
    }

    /**
     * @dataProvider invalidColorFormatProvider
     */
    public function test_convert_format_validates_target_format(string $target_format): void {
        $palette = ['colors' => ['#FF0000']];

        $this->converter
            ->expects($this->once())
            ->method('convert_format')
            ->with($palette, $target_format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert_format($palette, $target_format);
    }

    public function invalidColorFormatProvider(): array {
        return [
            'empty_format' => [''],
            'invalid_format' => ['invalid'],
            'unknown_format' => ['pantone'],
            'numeric_format' => ['123']
        ];
    }
}
