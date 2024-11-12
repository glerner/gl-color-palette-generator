<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteAnalyzer;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteAnalyzerTest extends TestCase {
    private $analyzer;
    private $formatter;
    private $palette;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->analyzer = new ColorPaletteAnalyzer($this->formatter);
        $this->palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ]);
    }

    public function test_analyze_palette_returns_complete_analysis(): void {
        // Act
        $result = $this->analyzer->analyze_palette($this->palette);

        // Assert
        $this->assertArrayHasKey('contrast', $result);
        $this->assertArrayHasKey('harmony', $result);
        $this->assertArrayHasKey('distribution', $result);
        $this->assertArrayHasKey('accessibility', $result);
    }

    public function test_analyze_palette_respects_options(): void {
        // Arrange
        $options = [
            'contrast' => true,
            'harmony' => false,
            'distribution' => true,
            'accessibility' => false
        ];

        // Act
        $result = $this->analyzer->analyze_palette($this->palette, $options);

        // Assert
        $this->assertArrayHasKey('contrast', $result);
        $this->assertArrayNotHasKey('harmony', $result);
        $this->assertArrayHasKey('distribution', $result);
        $this->assertArrayNotHasKey('accessibility', $result);
    }

    public function test_analyze_contrast_calculates_correct_ratios(): void {
        // Arrange
        $black_white_palette = new ColorPalette([
            'name' => 'Black & White',
            'colors' => ['#000000', '#FFFFFF']
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($black_white_palette);

        // Assert
        $this->assertArrayHasKey('contrast', $result);
        $this->assertEquals(21, round($result['contrast']['ratios'][0]['ratio']));
        $this->assertTrue($result['contrast']['ratios'][0]['wcag_aa']);
        $this->assertTrue($result['contrast']['ratios'][0]['wcag_aaa']);
    }

    public function test_analyze_harmony_identifies_relationships(): void {
        // Arrange
        $complementary_palette = new ColorPalette([
            'name' => 'Complementary',
            'colors' => ['#FF0000', '#00FFFF']
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($complementary_palette);

        // Assert
        $this->assertArrayHasKey('harmony', $result);
        $this->assertArrayHasKey('relationships', $result['harmony']);
        $this->assertEquals('complementary', $result['harmony']['relationships'][0]['type']);
    }

    public function test_analyze_distribution_calculates_coverage(): void {
        // Act
        $result = $this->analyzer->analyze_palette($this->palette);

        // Assert
        $this->assertArrayHasKey('distribution', $result);
        $this->assertArrayHasKey('hue_distribution', $result['distribution']);
        $this->assertArrayHasKey('coverage', $result['distribution']['hue_distribution']);
        $this->assertGreaterThan(0, $result['distribution']['hue_distribution']['coverage']);
    }

    public function test_analyze_accessibility_identifies_issues(): void {
        // Arrange
        $low_contrast_palette = new ColorPalette([
            'name' => 'Low Contrast',
            'colors' => ['#CCCCCC', '#DDDDDD']
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($low_contrast_palette);

        // Assert
        $this->assertArrayHasKey('accessibility', $result);
        $this->assertArrayHasKey('wcag_compliance', $result['accessibility']);
        $this->assertEquals(0, $result['accessibility']['wcag_compliance']['aa_pass_rate']);
    }

    public function test_analyze_color_blindness_provides_recommendations(): void {
        // Arrange
        $problematic_palette = new ColorPalette([
            'name' => 'Problematic for Color Blindness',
            'colors' => ['#FF0000', '#00FF00'] // Red-green problematic
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($problematic_palette);

        // Assert
        $this->assertArrayHasKey('accessibility', $result);
        $this->assertArrayHasKey('color_blindness', $result['accessibility']);
        $this->assertNotEmpty($result['accessibility']['color_blindness']['recommendations']);
    }

    public function test_harmony_score_calculation(): void {
        // Arrange
        $harmonious_palette = new ColorPalette([
            'name' => 'Harmonious',
            'colors' => [
                '#FF0000', // Red
                '#FF8000', // Orange
                '#FFFF00'  // Yellow
            ]
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($harmonious_palette);

        // Assert
        $this->assertArrayHasKey('harmony', $result);
        $this->assertArrayHasKey('harmony_score', $result['harmony']);
        $this->assertGreaterThan(0.5, $result['harmony']['harmony_score']);
    }

    public function test_analyze_empty_palette(): void {
        // Arrange
        $empty_palette = new ColorPalette([
            'name' => 'Empty',
            'colors' => []
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($empty_palette);

        // Assert
        $this->assertArrayHasKey('contrast', $result);
        $this->assertEmpty($result['contrast']['ratios']);
        $this->assertEquals(0, $result['distribution']['hue_distribution']['coverage']);
    }

    public function test_analyze_single_color_palette(): void {
        // Arrange
        $single_color = new ColorPalette([
            'name' => 'Single Color',
            'colors' => ['#FF0000']
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($single_color);

        // Assert
        $this->assertArrayHasKey('harmony', $result);
        $this->assertEquals(1.0, $result['harmony']['harmony_score']);
        $this->assertEmpty($result['contrast']['ratios']);
    }

    /**
     * @dataProvider colorRelationshipProvider
     */
    public function test_color_relationships(
        array $colors,
        string $expected_relationship
    ): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Relationship',
            'colors' => $colors
        ]);

        // Act
        $result = $this->analyzer->analyze_palette($palette);

        // Assert
        $this->assertArrayHasKey('harmony', $result);
        $this->assertContains(
            $expected_relationship,
            array_column($result['harmony']['relationships'], 'type')
        );
    }

    public function colorRelationshipProvider(): array {
        return [
            'complementary' => [
                ['#FF0000', '#00FFFF'],
                'complementary'
            ],
            'analogous' => [
                ['#FF0000', '#FF8000'],
                'analogous'
            ],
            'triadic' => [
                ['#FF0000', '#00FF00', '#0000FF'],
                'triadic'
            ],
            'monochromatic' => [
                ['#FF0000', '#FF0033'],
                'monochromatic'
            ]
        ];
    }

    public function test_contrast_ratio_calculation_accuracy(): void {
        // Arrange
        $test_cases = [
            ['#000000', '#FFFFFF', 21],  // Black-White
            ['#FF0000', '#FFFFFF', 4],   // Red-White
            ['#808080', '#FFFFFF', 3.95] // Gray-White
        ];

        foreach ($test_cases as [$color1, $color2, $expected]) {
            // Arrange
            $palette = new ColorPalette([
                'name' => 'Test Contrast',
                'colors' => [$color1, $color2]
            ]);

            // Act
            $result = $this->analyzer->analyze_palette($palette);

            // Assert
            $this->assertEqualsWithDelta(
                $expected,
                $result['contrast']['ratios'][0]['ratio'],
                0.1,
                "Contrast ratio for {$color1}-{$color2} should be approximately {$expected}"
            );
        }
    }
} 
