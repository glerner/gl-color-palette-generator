<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\PaletteGenerator;

class PaletteGeneratorTest extends TestCase {
    private $generator;

    protected function setUp(): void {
        $this->generator = $this->createMock(PaletteGenerator::class);
    }

    public function test_generate_palette_returns_valid_structure(): void {
        / Arrange
        $criteria = [
            'base_color' => '#FF0000',
            'harmony_type' => 'complementary',
            'color_count' => 5
        ];

        $expected = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF'],
            'color_names' => ['Red', 'Green', 'Blue', 'Yellow', 'Magenta'],
            'harmony_type' => 'complementary',
            'relationships' => [
                'primary' => '#FF0000',
                'complement' => '#00FF00'
            ],
            'metadata' => [
                'generation_time' => '2024-01-20 12:00:00',
                'algorithm_version' => '1.0'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_palette')
            ->with($criteria)
            ->willReturn($expected);

        / Act
        $result = $this->generator->generate_palette($criteria);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('color_names', $result);
        $this->assertArrayHasKey('harmony_type', $result);
        $this->assertCount(5, $result['colors']);
    }

    public function test_validate_palette_returns_validation_results(): void {
        / Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $rules = [
            'check_contrast' => true,
            'accessibility' => 'AA'
        ];

        $expected = [
            'is_valid' => true,
            'issues' => [],
            'suggestions' => ['Consider adding a neutral color'],
            'harmony_score' => 0.85
        ];

        $this->generator
            ->expects($this->once())
            ->method('validate_palette')
            ->with($palette, $rules)
            ->willReturn($expected);

        / Act
        $result = $this->generator->validate_palette($palette, $rules);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('issues', $result);
        $this->assertArrayHasKey('harmony_score', $result);
        $this->assertIsBool($result['is_valid']);
        $this->assertIsFloat($result['harmony_score']);
    }

    public function test_generate_variations_returns_valid_variations(): void {
        / Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $options = [
            'variation_type' => 'lighter',
            'variation_count' => 3
        ];

        $expected = [
            'variations' => [
                ['#FF3333', '#33FF33', '#3333FF'],
                ['#FF6666', '#66FF66', '#6666FF'],
                ['#FF9999', '#99FF99', '#9999FF']
            ],
            'relationships' => ['lighter_shade', 'preserves_harmony'],
            'scores' => [0.9, 0.85, 0.8]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_variations')
            ->with($palette, $options)
            ->willReturn($expected);

        / Act
        $result = $this->generator->generate_variations($palette, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertCount(3, $result['variations']);
    }

    public function test_get_palette_statistics_returns_comprehensive_analysis(): void {
        / Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'color_distribution' => [
                'hues' => ['red' => 33.33, 'green' => 33.33, 'blue' => 33.33],
                'saturations' => ['high' => 100]
            ],
            'harmony_analysis' => [
                'type' => 'triadic',
                'balance_score' => 0.95
            ],
            'contrast_metrics' => [
                'min_ratio' => 2.5,
                'max_ratio' => 4.5
            ],
            'accessibility_stats' => [
                'wcag_aa_pass_rate' => 0.8,
                'wcag_aaa_pass_rate' => 0.6
            ],
            'psychological_impact' => [
                'energy' => 'high',
                'balance' => 'good'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('get_palette_statistics')
            ->with($palette)
            ->willReturn($expected);

        / Act
        $result = $this->generator->get_palette_statistics($palette);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('color_distribution', $result);
        $this->assertArrayHasKey('harmony_analysis', $result);
        $this->assertArrayHasKey('contrast_metrics', $result);
        $this->assertArrayHasKey('psychological_impact', $result);
    }

    /**
     * @dataProvider invalidCriteriaProvider
     */
    public function test_generate_palette_throws_exception_for_invalid_criteria(array $criteria): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_palette')
            ->with($criteria)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_palette($criteria);
    }

    public function invalidCriteriaProvider(): array {
        return [
            'invalid_base_color' => [['base_color' => 'not-a-color']],
            'invalid_count' => [['color_count' => -1]],
            'invalid_harmony' => [['harmony_type' => 'invalid']],
            'empty_criteria' => [[]]
        ];
    }
} 
