<?php
/**
 * Theme Generator Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ThemeGenerator;

class ThemeGeneratorTest extends TestCase {
    private $generator;

    protected function setUp(): void {
        $this->generator = $this->createMock(ThemeGenerator::class);
    }

    public function test_generate_theme_returns_array(): void {
        // Arrange
        $base_color = '#FF0000';
        $options = [
            'style' => 'modern',
            'contrast' => 'high',
            'scheme' => 'complementary'
        ];
        $expected = [
            'primary' => '#FF0000',
            'secondary' => '#00FFFF',
            'accent' => '#FF8800',
            'background' => '#FFFFFF',
            'text' => '#333333',
            'metadata' => [
                'style' => 'modern',
                'generated' => '2024-12-08 18:30:50'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_theme')
            ->with($base_color, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_theme($base_color, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary', $result);
        $this->assertArrayHasKey('secondary', $result);
        $this->assertArrayHasKey('accent', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_theme_variations_returns_array(): void {
        // Arrange
        $theme = [
            'primary' => '#FF0000',
            'secondary' => '#00FFFF'
        ];
        $expected = [
            'light' => [
                'primary' => '#FF3333',
                'secondary' => '#33FFFF'
            ],
            'dark' => [
                'primary' => '#CC0000',
                'secondary' => '#00CCCC'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('get_theme_variations')
            ->with($theme)
            ->willReturn($expected);

        // Act
        $result = $this->generator->get_theme_variations($theme);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('light', $result);
        $this->assertArrayHasKey('dark', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_export_theme_returns_string(): void {
        // Arrange
        $theme = [
            'primary' => '#FF0000',
            'secondary' => '#00FFFF'
        ];
        $format = 'css';
        $expected = ":root {\n  --primary: #FF0000;\n  --secondary: #00FFFF;\n}";

        $this->generator
            ->expects($this->once())
            ->method('export_theme')
            ->with($theme, $format)
            ->willReturn($expected);

        // Act
        $result = $this->generator->export_theme($theme, $format);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_validate_theme_returns_true_for_valid_theme(): void {
        // Arrange
        $theme = [
            'primary' => '#FF0000',
            'secondary' => '#00FFFF'
        ];

        $this->generator
            ->expects($this->once())
            ->method('validate_theme')
            ->with($theme)
            ->willReturn(true);

        // Act
        $result = $this->generator->validate_theme($theme);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @dataProvider invalidBaseColorProvider
     */
    public function test_generate_theme_throws_exception_for_invalid_base_color($base_color): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_theme')
            ->with($base_color, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_theme($base_color, []);
    }

    /**
     * @dataProvider invalidThemeProvider
     */
    public function test_validate_theme_throws_exception_for_invalid_theme($theme): void {
        $this->generator
            ->expects($this->once())
            ->method('validate_theme')
            ->with($theme)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->validate_theme($theme);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_export_theme_throws_exception_for_invalid_format($format): void {
        $theme = ['primary' => '#FF0000'];
        
        $this->generator
            ->expects($this->once())
            ->method('export_theme')
            ->with($theme, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->export_theme($theme, $format);
    }

    public function invalidBaseColorProvider(): array {
        return [
            'empty string' => [''],
            'invalid hex' => ['#GG0000'],
            'rgb format' => ['rgb(255,0,0)'],
            'no hash' => ['FF0000'],
            'invalid length' => ['#F00']
        ];
    }

    public function invalidThemeProvider(): array {
        return [
            'empty array' => [[]],
            'missing required colors' => [['accent' => '#FF0000']],
            'invalid color values' => [['primary' => 'red']],
            'non-array theme' => ['invalid'],
            'null theme' => [null]
        ];
    }

    public function invalidFormatProvider(): array {
        return [
            'empty string' => [''],
            'invalid format' => ['invalid'],
            'unsupported format' => ['pdf'],
            'non-string format' => [42],
            'null format' => [null]
        ];
    }
}
