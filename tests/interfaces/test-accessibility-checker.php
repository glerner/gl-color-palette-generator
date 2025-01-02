<?php

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\AccessibilityChecker;

class AccessibilityCheckerTest extends TestCase {
    private $checker;

    public function setUp(): void {
        $this->checker = $this->createMock(AccessibilityChecker::class);
    }

    public function test_check_contrast_ratio_returns_valid_analysis(): void {
        // Arrange
        $foreground = '#000000';
        $background = '#FFFFFF';
        $expected = [
            'ratio' => 21,
            'wcag_aa_normal' => true,
            'wcag_aa_large' => true,
            'wcag_aaa_normal' => true,
            'wcag_aaa_large' => true
        ];

        $this->checker
            ->expects($this->once())
            ->method('check_contrast_ratio')
            ->with($foreground, $background)
            ->willReturn($expected);

        // Act
        $result = $this->checker->check_contrast_ratio($foreground, $background);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('ratio', $result);
        $this->assertArrayHasKey('wcag_aa_normal', $result);
        $this->assertIsFloat($result['ratio']);
        $this->assertIsBool($result['wcag_aa_normal']);
    }

    public function test_validate_palette_accessibility_returns_complete_analysis(): void {
        // Arrange
        $palette = ['#000000', '#FFFFFF', '#FF0000'];
        $options = ['standard' => 'AA', 'check_all' => true];
        $expected = [
            'passes_wcag' => true,
            'combinations' => [
                ['colors' => ['#000000', '#FFFFFF'], 'ratio' => 21],
                ['colors' => ['#000000', '#FF0000'], 'ratio' => 5.25]
            ],
            'improvements' => [],
            'problem_pairs' => []
        ];

        $this->checker
            ->expects($this->once())
            ->method('validate_palette_accessibility')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->checker->validate_palette_accessibility($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('passes_wcag', $result);
        $this->assertArrayHasKey('combinations', $result);
        $this->assertArrayHasKey('improvements', $result);
        $this->assertIsBool($result['passes_wcag']);
    }

    public function test_get_accessibility_score_returns_valid_score(): void {
        // Arrange
        $foreground = '#000000';
        $background = '#FFFFFF';
        $expected = 1.0;

        $this->checker
            ->expects($this->once())
            ->method('get_accessibility_score')
            ->with($foreground, $background)
            ->willReturn($expected);

        // Act
        $result = $this->checker->get_accessibility_score($foreground, $background);

        // Assert
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(1, $result);
    }

    public function test_suggest_accessible_alternatives_returns_valid_suggestions(): void {
        // Arrange
        $color = '#FF0000';
        $constraints = [
            'min_contrast' => 4.5,
            'base_color' => '#FFFFFF'
        ];
        $expected = [
            'colors' => ['#990000', '#CC0000', '#800000'],
            'scores' => [0.9, 0.85, 0.95],
            'explanations' => [
                'Darker shade with improved contrast',
                'Balanced alternative with good visibility',
                'High contrast option for maximum readability'
            ]
        ];

        $this->checker
            ->expects($this->once())
            ->method('suggest_accessible_alternatives')
            ->with($color, $constraints)
            ->willReturn($expected);

        // Act
        $result = $this->checker->suggest_accessible_alternatives($color, $constraints);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('explanations', $result);
        $this->assertCount(count($result['colors']), $result['scores']);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_check_contrast_ratio_throws_exception_for_invalid_colors(
        string $foreground,
        string $background
    ): void {
        $this->checker
            ->expects($this->once())
            ->method('check_contrast_ratio')
            ->with($foreground, $background)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->checker->check_contrast_ratio($foreground, $background);
    }

    public function invalidColorProvider(): array {
        return [
            'invalid_hex' => ['#GG0000', '#FFFFFF'],
            'wrong_length' => ['#FF', '#FFFFFF'],
            'no_hash' => ['FF0000', 'FFFFFF'],
            'empty_color' => ['', '#FFFFFF']
        ];
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_validate_palette_accessibility_throws_exception_for_invalid_palette(
        array $palette
    ): void {
        $this->checker
            ->expects($this->once())
            ->method('validate_palette_accessibility')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->checker->validate_palette_accessibility($palette);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty_palette' => [[]],
            'invalid_colors' => [['#GG0000', '#FFFFFF']],
            'single_color' => [['#FF0000']],
            'mixed_invalid' => [['#FF0000', 'not-a-color']]
        ];
    }
} 
