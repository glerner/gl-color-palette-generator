<?php
/**
 * Palette Optimizer Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\PaletteOptimizer;

class PaletteOptimizerTest extends TestCase {
    private $optimizer;

    protected function setUp(): void {
        $this->optimizer = $this->createMock(PaletteOptimizer::class);
    }

    public function test_optimize_palette_returns_array(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $criteria = [
            'contrast_minimum' => 4.5,
            'harmony_weight' => 0.7,
            'accessibility_weight' => 0.3
        ];
        $expected = [
            'optimized_colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'score' => 0.92,
            'improvements' => [
                'contrast' => 0.15,
                'harmony' => 0.08,
                'accessibility' => 0.12
            ]
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('optimize_palette')
            ->with($colors, $criteria)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->optimize_palette($colors, $criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('optimized_colors', $result);
        $this->assertArrayHasKey('score', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_optimization_suggestions_returns_array(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'suggestions' => [
                [
                    'type' => 'contrast',
                    'description' => 'Increase contrast between red and green',
                    'current_value' => 2.5,
                    'target_value' => 4.5,
                    'suggested_changes' => [
                        '#FF0000' => '#FF1111',
                        '#00FF00' => '#00EE00'
                    ]
                ]
            ],
            'priority' => 'high',
            'impact_score' => 0.8
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('get_optimization_suggestions')
            ->with($colors)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->get_optimization_suggestions($colors);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('priority', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_apply_optimization_returns_array(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $optimization_type = 'contrast';
        $params = ['target_ratio' => 4.5];
        $expected = [
            'optimized_colors' => ['#FF1111', '#00EE00', '#0000FF'],
            'changes' => [
                '#FF0000' => '#FF1111',
                '#00FF00' => '#00EE00'
            ],
            'improvement' => 0.15
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('apply_optimization')
            ->with($colors, $optimization_type, $params)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->apply_optimization($colors, $optimization_type, $params);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('optimized_colors', $result);
        $this->assertArrayHasKey('changes', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_optimization_score_returns_float(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $criteria = [
            'contrast_weight' => 0.5,
            'harmony_weight' => 0.3,
            'accessibility_weight' => 0.2
        ];
        $expected = 0.85;

        $this->optimizer
            ->expects($this->once())
            ->method('get_optimization_score')
            ->with($colors, $criteria)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->get_optimization_score($colors, $criteria);

        // Assert
        $this->assertIsFloat($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_optimize_palette_throws_exception_for_invalid_colors($colors): void {
        $this->optimizer
            ->expects($this->once())
            ->method('optimize_palette')
            ->with($colors, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->optimize_palette($colors, []);
    }

    /**
     * @dataProvider invalidCriteriaProvider
     */
    public function test_optimize_palette_throws_exception_for_invalid_criteria($criteria): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        
        $this->optimizer
            ->expects($this->once())
            ->method('optimize_palette')
            ->with($colors, $criteria)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->optimize_palette($colors, $criteria);
    }

    /**
     * @dataProvider invalidOptimizationTypeProvider
     */
    public function test_apply_optimization_throws_exception_for_invalid_type($type): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        
        $this->optimizer
            ->expects($this->once())
            ->method('apply_optimization')
            ->with($colors, $type, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->apply_optimization($colors, $type, []);
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

    public function invalidCriteriaProvider(): array {
        return [
            'empty array' => [[]],
            'invalid weights' => [['contrast_weight' => 2.0]],
            'missing required criteria' => [['invalid_key' => 0.5]],
            'non-array input' => ['invalid'],
            'null input' => [null]
        ];
    }

    public function invalidOptimizationTypeProvider(): array {
        return [
            'empty string' => [''],
            'invalid type' => ['invalid_type'],
            'numeric type' => [42],
            'array type' => [[]],
            'null type' => [null]
        ];
    }
}
