<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorMetricsAnalyzer;

class ColorMetricsAnalyzerTest extends TestCase {
    private $analyzer;

    protected function setUp(): void {
        $this->analyzer = $this->createMock(ColorMetricsAnalyzer::class);
    }

    public function test_analyze_relationships_returns_comprehensive_analysis(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $options = [
            'include_contrast' => true,
            'include_harmony' => true,
            'color_space' => 'LAB'
        ];

        $expected = [
            'relationships' => [
                'primary_secondary' => ['type' => 'complementary', 'strength' => 0.95],
                'secondary_accent' => ['type' => 'analogous', 'strength' => 0.85]
            ],
            'contrast_matrix' => [
                ['#FF0000', '#00FF00', 4.5],
                ['#FF0000', '#0000FF', 3.8]
            ],
            'harmony_scores' => [
                'overall' => 0.92,
                'balance' => 0.88,
                'tension' => 0.15
            ],
            'psychology' => [
                'energy' => 'high',
                'mood' => 'vibrant',
                'associations' => ['dynamic', 'bold']
            ],
            'statistics' => [
                'average_distance' => 120.5,
                'variance' => 15.3
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_relationships')
            ->with($colors, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_relationships($colors, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('contrast_matrix', $result);
        $this->assertArrayHasKey('harmony_scores', $result);
        $this->assertArrayHasKey('psychology', $result);
    }

    public function test_calculate_distribution_returns_distribution_metrics(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00'];

        $expected = [
            'hue_distribution' => [
                'red' => 0.25,
                'green' => 0.25,
                'blue' => 0.25,
                'yellow' => 0.25
            ],
            'saturation_spread' => [
                'mean' => 0.95,
                'range' => [0.85, 1.0],
                'distribution' => 'uniform'
            ],
            'lightness_balance' => [
                'mean' => 0.5,
                'variance' => 0.1,
                'distribution' => 'balanced'
            ],
            'color_clusters' => [
                ['center' => '#FF0000', 'members' => ['#FF0000']],
                ['center' => '#00FF00', 'members' => ['#00FF00']]
            ],
            'coverage_score' => 0.85
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('calculate_distribution')
            ->with($colors)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->calculate_distribution($colors);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('hue_distribution', $result);
        $this->assertArrayHasKey('saturation_spread', $result);
        $this->assertArrayHasKey('lightness_balance', $result);
        $this->assertArrayHasKey('coverage_score', $result);
    }

    public function test_measure_difference_returns_detailed_measurements(): void {
        // Arrange
        $color1 = '#FF0000';
        $color2 = '#FF3333';
        $options = [
            'algorithm' => 'CIEDE2000',
            'color_space' => 'LAB'
        ];

        $expected = [
            'delta_e' => 5.2,
            'components' => [
                'lightness' => 2.1,
                'chroma' => 1.8,
                'hue' => 1.3
            ],
            'perceptible' => true,
            'analysis' => [
                'significance' => 'moderate',
                'category' => 'noticeable',
                'details' => ['primary_shift' => 'lightness']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('measure_difference')
            ->with($color1, $color2, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->measure_difference($color1, $color2, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('delta_e', $result);
        $this->assertArrayHasKey('components', $result);
        $this->assertArrayHasKey('perceptible', $result);
        $this->assertIsFloat($result['delta_e']);
    }

    public function test_generate_metrics_report_returns_comprehensive_report(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $criteria = [
            'metrics' => ['harmony', 'contrast', 'distribution'],
            'format' => 'detailed',
            'include_charts' => true
        ];

        $expected = [
            'summary' => [
                'harmony_score' => 0.88,
                'contrast_compliance' => true,
                'coverage' => 'good'
            ],
            'detailed_data' => [
                'harmony_analysis' => ['score' => 0.88, 'type' => 'triadic'],
                'contrast_matrix' => [/* ... */],
                'distribution_metrics' => [/* ... */]
            ],
            'visualizations' => [
                'color_wheel' => [/* ... */],
                'distribution_chart' => [/* ... */]
            ],
            'comparisons' => [
                'industry_average' => ['above', 0.15],
                'accessibility_standards' => ['meets', 'AA']
            ],
            'recommendations' => [
                'Consider adding a neutral color',
                'Increase contrast between secondary colors'
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('generate_metrics_report')
            ->with($colors, $criteria)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->generate_metrics_report($colors, $criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('detailed_data', $result);
        $this->assertArrayHasKey('visualizations', $result);
        $this->assertArrayHasKey('recommendations', $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_analyze_relationships_handles_invalid_colors(array $colors): void {
        $this->analyzer
            ->expects($this->once())
            ->method('analyze_relationships')
            ->with($colors)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->analyze_relationships($colors);
    }

    public function invalidColorsProvider(): array {
        return [
            'empty_array' => [[]],
            'invalid_colors' => [['not-a-color', '#FF0000']],
            'single_color' => [['#FF0000']],
            'null_values' => [['#FF0000', null]]
        ];
    }

    /**
     * @dataProvider invalidMeasurementProvider
     */
    public function test_measure_difference_validates_inputs(
        string $color1,
        string $color2
    ): void {
        $this->analyzer
            ->expects($this->once())
            ->method('measure_difference')
            ->with($color1, $color2)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->measure_difference($color1, $color2);
    }

    public function invalidMeasurementProvider(): array {
        return [
            'invalid_first' => ['invalid', '#FF0000'],
            'invalid_second' => ['#FF0000', 'invalid'],
            'both_invalid' => ['invalid', 'also-invalid'],
            'empty_values' => ['', '']
        ];
    }
} 
