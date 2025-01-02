<?php
/**
 * Color Analyzer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\ColorAnalyzer;

class ColorAnalyzerTest extends TestCase {
    private $analyzer;

    public function setUp(): void {
        $this->analyzer = $this->createMock(ColorAnalyzer::class);
    }

    public function test_analyze_harmony_returns_array(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'harmony_type' => 'triadic',
            'score' => 0.95,
            'suggestions' => [
                'complement' => '#00FFFF',
                'analogous' => ['#FF3300', '#FF0033']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($colors)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_harmony($colors);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('harmony_type', $result);
        $this->assertArrayHasKey('score', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_contrast_ratio_returns_float(): void {
        // Arrange
        $color1 = '#FFFFFF';
        $color2 = '#000000';
        $expected = 21.0;

        $this->analyzer
            ->expects($this->once())
            ->method('get_contrast_ratio')
            ->with($color1, $color2)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->get_contrast_ratio($color1, $color2);

        // Assert
        $this->assertIsFloat($result);
        $this->assertEquals($expected, $result);
    }

    public function test_check_accessibility_returns_array(): void {
        // Arrange
        $foreground = '#000000';
        $background = '#FFFFFF';
        $expected = [
            'wcag_aa' => true,
            'wcag_aaa' => true,
            'contrast_ratio' => 21.0,
            'readable_small_text' => true,
            'readable_large_text' => true
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('check_accessibility')
            ->with($foreground, $background)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->check_accessibility($foreground, $background);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('wcag_aa', $result);
        $this->assertArrayHasKey('wcag_aaa', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_color_properties_returns_array(): void {
        // Arrange
        $color = '#FF0000';
        $expected = [
            'hex' => '#FF0000',
            'rgb' => [255, 0, 0],
            'hsl' => [0, 100, 50],
            'hsv' => [0, 100, 100],
            'luminance' => 0.2126,
            'is_dark' => false
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('get_color_properties')
            ->with($color)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->get_color_properties($color);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('hex', $result);
        $this->assertArrayHasKey('rgb', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_analyze_palette_balance_returns_array(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'balance_score' => 0.85,
            'distribution' => [
                'warm_colors' => 1,
                'cool_colors' => 2,
                'light_colors' => 0,
                'dark_colors' => 1
            ],
            'suggestions' => [
                'add_warm' => ['#FFA500'],
                'add_light' => ['#FFFFFF']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_palette_balance')
            ->with($colors)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_palette_balance($colors);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('balance_score', $result);
        $this->assertArrayHasKey('distribution', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_analyze_harmony_throws_exception_for_invalid_colors($colors): void {
        $this->analyzer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($colors)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->analyze_harmony($colors);
    }

    /**
     * @dataProvider invalidColorPairProvider
     */
    public function test_get_contrast_ratio_throws_exception_for_invalid_colors($color1, $color2): void {
        $this->analyzer
            ->expects($this->once())
            ->method('get_contrast_ratio')
            ->with($color1, $color2)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->get_contrast_ratio($color1, $color2);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_get_color_properties_throws_exception_for_invalid_color($color): void {
        $this->analyzer
            ->expects($this->once())
            ->method('get_color_properties')
            ->with($color)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->get_color_properties($color);
    }

    public function invalidColorsProvider(): array {
        return [
            'empty array' => [[]],
            'invalid hex colors' => [['#GG0000', '#00FF00']],
            'mixed formats' => [['#FF0000', 'rgb(0,255,0)']],
            'non-array input' => ['#FF0000'],
            'null input' => [null]
        ];
    }

    public function invalidColorPairProvider(): array {
        return [
            'invalid first color' => ['invalid', '#000000'],
            'invalid second color' => ['#FFFFFF', 'invalid'],
            'both invalid' => ['invalid1', 'invalid2'],
            'null colors' => [null, null],
            'empty strings' => ['', '']
        ];
    }

    public function invalidColorProvider(): array {
        return [
            'empty string' => [''],
            'invalid hex' => ['#GG0000'],
            'wrong format' => ['rgb(0,255,0)'],
            'numeric input' => [42],
            'null input' => [null]
        ];
    }
}
