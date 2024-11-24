<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorValidator;

class ColorValidatorTest extends TestCase {
    private $validator;

    protected function setUp(): void {
        $this->validator = $this->createMock(ColorValidator::class);
    }

    public function test_validate_color_returns_valid_structure(): void {
        / Arrange
        $color = '#FF0000';
        $format = 'hex';
        $expected = [
            'is_valid' => true,
            'format' => 'hex',
            'normalized' => [
                'hex' => '#FF0000',
                'rgb' => [255, 0, 0],
                'hsl' => [0, 100, 50]
            ],
            'errors' => []
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_color')
            ->with($color, $format)
            ->willReturn($expected);

        / Act
        $result = $this->validator->validate_color($color, $format);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('format', $result);
        $this->assertArrayHasKey('normalized', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertIsBool($result['is_valid']);
    }

    public function test_validate_combination_returns_analysis(): void {
        / Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $rules = [
            'min_contrast' => 4.5,
            'harmony_type' => 'triadic'
        ];

        $expected = [
            'passes_rules' => true,
            'harmony_scores' => [
                'overall' => 0.95,
                'balance' => 0.90,
                'spacing' => 1.0
            ],
            'contrast_ratios' => [
                ['colors' => ['#FF0000', '#00FF00'], 'ratio' => 5.2],
                ['colors' => ['#FF0000', '#0000FF'], 'ratio' => 4.8]
            ],
            'violations' => []
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_combination')
            ->with($colors, $rules)
            ->willReturn($expected);

        / Act
        $result = $this->validator->validate_combination($colors, $rules);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('passes_rules', $result);
        $this->assertArrayHasKey('harmony_scores', $result);
        $this->assertArrayHasKey('contrast_ratios', $result);
        $this->assertIsBool($result['passes_rules']);
    }

    public function test_get_color_info_returns_detailed_information(): void {
        / Arrange
        $color = '#FF0000';
        $expected = [
            'formats' => [
                'hex' => '#FF0000',
                'rgb' => 'rgb(255, 0, 0)',
                'hsl' => 'hsl(0, 100%, 50%)'
            ],
            'properties' => [
                'brightness' => 0.5,
                'saturation' => 1.0,
                'temperature' => 'warm'
            ],
            'name' => 'Pure Red',
            'metadata' => [
                'category' => 'primary',
                'psychology' => 'energetic'
            ]
        ];

        $this->validator
            ->expects($this->once())
            ->method('get_color_info')
            ->with($color)
            ->willReturn($expected);

        / Act
        $result = $this->validator->get_color_info($color);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('formats', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_validate_against_guidelines_checks_compliance(): void {
        / Arrange
        $color = '#FF0000';
        $guidelines = [
            'allowed_colors' => ['#FF0000', '#00FF00'],
            'allowed_ranges' => [
                'hue' => [0, 360],
                'saturation' => [80, 100],
                'lightness' => [45, 55]
            ],
            'tolerance' => 0.05
        ];

        $expected = [
            'compliant' => true,
            'matches' => ['#FF0000'],
            'suggestions' => [],
            'violations' => []
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_against_guidelines')
            ->with($color, $guidelines)
            ->willReturn($expected);

        / Act
        $result = $this->validator->validate_against_guidelines($color, $guidelines);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('compliant', $result);
        $this->assertArrayHasKey('matches', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('violations', $result);
        $this->assertIsBool($result['compliant']);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_validate_color_identifies_invalid_colors(string $color): void {
        $expected = [
            'is_valid' => false,
            'format' => null,
            'normalized' => [],
            'errors' => ['Invalid color format']
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_color')
            ->with($color)
            ->willReturn($expected);

        $result = $this->validator->validate_color($color);
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function invalidColorProvider(): array {
        return [
            'empty_string' => [''],
            'invalid_hex' => ['#GG0000'],
            'wrong_format' => ['rgb(256,0,0)'],
            'malformed' => ['#FF00']
        ];
    }

    /**
     * @dataProvider invalidCombinationProvider
     */
    public function test_validate_combination_identifies_invalid_combinations(
        array $colors,
        array $rules
    ): void {
        $expected = [
            'passes_rules' => false,
            'harmony_scores' => [],
            'contrast_ratios' => [],
            'violations' => ['Insufficient contrast ratio']
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_combination')
            ->with($colors, $rules)
            ->willReturn($expected);

        $result = $this->validator->validate_combination($colors, $rules);
        $this->assertFalse($result['passes_rules']);
        $this->assertNotEmpty($result['violations']);
    }

    public function invalidCombinationProvider(): array {
        return [
            'low_contrast' => [
                ['#FFFFFF', '#FEFEFE'],
                ['min_contrast' => 4.5]
            ],
            'wrong_harmony' => [
                ['#FF0000', '#00FF00'],
                ['harmony_type' => 'analogous']
            ]
        ];
    }
} 
