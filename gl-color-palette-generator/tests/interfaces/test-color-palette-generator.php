<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteGenerator;

class ColorPaletteGeneratorTest extends TestCase {
    private $generator;

    protected function setUp(): void {
        $this->generator = $this->createMock(ColorPaletteGenerator::class);
    }

    public function test_generate_from_color_creates_palette(): void {
        // Arrange
        $base_color = '#FF0000';
        $options = [
            'scheme' => 'complementary',
            'count' => 5
        ];

        $expected = [
            'palette' => [
                'colors' => ['#FF0000', '#00FFFF', '#FF3333', '#00CCCC', '#FF6666']
            ],
            'relationships' => [
                'complementary' => ['#FF0000', '#00FFFF'],
                'analogous' => ['#FF3333', '#FF6666']
            ],
            'metrics' => [
                'harmony' => 0.95,
                'contrast' => 0.85
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'base_color' => '#FF0000'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_from_color')
            ->with($base_color, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_from_color($base_color, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_from_theme_creates_palette(): void {
        // Arrange
        $theme = 'ocean';
        $options = [
            'mood' => 'calm',
            'style' => 'modern'
        ];

        $expected = [
            'palette' => [
                'colors' => ['#0077BE', '#00A3E0', '#004C8C', '#80D6FF']
            ],
            'theme' => [
                'name' => 'ocean',
                'mood' => 'calm',
                'style' => 'modern'
            ],
            'variations' => [
                'light' => ['#80D6FF', '#B3E5FF'],
                'dark' => ['#004C8C', '#003366']
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'theme' => 'ocean'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_from_theme')
            ->with($theme, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_from_theme($theme, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('theme', $result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_random_creates_palette(): void {
        // Arrange
        $options = [
            'count' => 4,
            'constraints' => [
                'saturation' => ['min' => 50, 'max' => 100],
                'lightness' => ['min' => 30, 'max' => 70]
            ]
        ];

        $expected = [
            'palette' => [
                'colors' => ['#FF3366', '#33CC99', '#9933FF', '#FFCC00']
            ],
            'metrics' => [
                'harmony' => 0.75,
                'contrast' => 0.80
            ],
            'validation' => [
                'constraints' => true,
                'harmony' => true
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'method' => 'random'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_random')
            ->with($options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_random($options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_variations_creates_alternatives(): void {
        // Arrange
        $palette = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $options = [
            'types' => ['lighter', 'darker'],
            'count' => 2
        ];

        $expected = [
            'variations' => [
                'lighter' => [
                    ['#FF3333', '#33FF33', '#3333FF'],
                    ['#FF6666', '#66FF66', '#6666FF']
                ],
                'darker' => [
                    ['#CC0000', '#00CC00', '#0000CC'],
                    ['#990000', '#009900', '#000099']
                ]
            ],
            'relationships' => [
                'type' => 'brightness',
                'steps' => [0.2, 0.4]
            ],
            'metrics' => [
                'variance' => 0.2,
                'consistency' => 0.9
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'variation_types' => ['lighter', 'darker']
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_variations')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_variations($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_generate_from_color_validates_color(string $color): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_from_color')
            ->with($color)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_from_color($color);
    }

    public function invalidColorProvider(): array {
        return [
            'empty_color' => [''],
            'invalid_hex' => ['#XYZ'],
            'wrong_format' => ['rgb(0,0,0)'],
            'no_hash' => ['FF0000']
        ];
    }

    /**
     * @dataProvider invalidThemeProvider
     */
    public function test_generate_from_theme_validates_theme(string $theme): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_from_theme')
            ->with($theme)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_from_theme($theme);
    }

    public function invalidThemeProvider(): array {
        return [
            'empty_theme' => [''],
            'invalid_theme' => ['nonexistent'],
            'numeric_theme' => ['123'],
            'special_chars' => ['theme@!']
        ];
    }
} 
