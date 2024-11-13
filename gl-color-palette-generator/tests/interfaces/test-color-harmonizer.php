<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorHarmonizer;

class ColorHarmonizerTest extends TestCase {
    private $harmonizer;

    protected function setUp(): void {
        $this->harmonizer = $this->createMock(ColorHarmonizer::class);
    }

    public function test_generate_harmony_returns_valid_structure(): void {
        // Arrange
        $base_color = '#FF0000';
        $harmony_type = 'complementary';
        $options = [
            'count' => 4,
            'include_base' => true
        ];

        $expected = [
            'colors' => ['#FF0000', '#00FFFF', '#FF3333', '#00CCCC'],
            'relationships' => [
                'base' => '#FF0000',
                'complement' => '#00FFFF',
                'variants' => ['#FF3333', '#00CCCC']
            ],
            'angles' => [0, 180, 15, 195],
            'harmony_score' => 0.95
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('generate_harmony')
            ->with($base_color, $harmony_type, $options)
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->generate_harmony($base_color, $harmony_type, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('angles', $result);
        $this->assertArrayHasKey('harmony_score', $result);
        $this->assertIsFloat($result['harmony_score']);
    }

    public function test_analyze_harmony_returns_detailed_analysis(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'harmony_type' => 'triadic',
            'harmony_score' => 0.92,
            'relationships' => [
                ['type' => 'triadic', 'colors' => ['#FF0000', '#00FF00', '#0000FF']]
            ],
            'improvements' => [
                'Consider adjusting saturation for better balance'
            ],
            'metrics' => [
                'hue_spacing' => 0.95,
                'value_balance' => 0.88,
                'saturation_coherence' => 0.93
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($colors)
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->analyze_harmony($colors);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('harmony_type', $result);
        $this->assertArrayHasKey('harmony_score', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertIsFloat($result['harmony_score']);
    }

    public function test_suggest_improvements_returns_valid_suggestions(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00'];
        $target_harmony = 'complementary';
        $expected = [
            'adjustments' => [
                ['color' => '#FF0000', 'suggestion' => 'No change needed'],
                ['color' => '#00FF00', 'suggestion' => 'Adjust to #00FFFF']
            ],
            'alternatives' => [
                ['#FF0000', '#00FFFF'],
                ['#FF3333', '#00CCCC']
            ],
            'explanation' => 'Adjusting second color to true complement',
            'impact_score' => 0.85
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('suggest_improvements')
            ->with($colors, $target_harmony)
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->suggest_improvements($colors, $target_harmony);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('adjustments', $result);
        $this->assertArrayHasKey('alternatives', $result);
        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('impact_score', $result);
        $this->assertIsFloat($result['impact_score']);
    }

    public function test_get_harmony_types_returns_complete_list(): void {
        // Arrange
        $expected = [
            'complementary' => [
                'name' => 'Complementary',
                'description' => 'Colors opposite each other on the color wheel',
                'angles' => [0, 180],
                'use_cases' => ['High contrast designs', 'Call to action']
            ],
            'analogous' => [
                'name' => 'Analogous',
                'description' => 'Colors adjacent to each other',
                'angles' => [0, 30, 60],
                'use_cases' => ['Natural, harmonious designs']
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('get_harmony_types')
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->get_harmony_types();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('complementary', $result);
        $this->assertArrayHasKey('analogous', $result);
        $this->assertArrayHasKey('use_cases', $result['complementary']);
    }

    /**
     * @dataProvider invalidHarmonyTypeProvider
     */
    public function test_generate_harmony_throws_exception_for_invalid_type(
        string $base_color,
        string $harmony_type
    ): void {
        $this->harmonizer
            ->expects($this->once())
            ->method('generate_harmony')
            ->with($base_color, $harmony_type)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->harmonizer->generate_harmony($base_color, $harmony_type);
    }

    public function invalidHarmonyTypeProvider(): array {
        return [
            'empty_type' => ['#FF0000', ''],
            'invalid_type' => ['#FF0000', 'invalid'],
            'invalid_color' => ['invalid', 'complementary']
        ];
    }

    /**
     * @dataProvider invalidColorSetProvider
     */
    public function test_analyze_harmony_handles_invalid_color_sets(array $colors): void {
        $expected = [
            'harmony_type' => 'unknown',
            'harmony_score' => 0.0,
            'relationships' => [],
            'improvements' => ['Invalid color set'],
            'metrics' => [
                'hue_spacing' => 0.0,
                'value_balance' => 0.0,
                'saturation_coherence' => 0.0
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($colors)
            ->willReturn($expected);

        $result = $this->harmonizer->analyze_harmony($colors);
        $this->assertEquals(0.0, $result['harmony_score']);
        $this->assertNotEmpty($result['improvements']);
    }

    public function invalidColorSetProvider(): array {
        return [
            'empty_set' => [[]],
            'single_color' => [['#FF0000']],
            'invalid_colors' => [['invalid', '#FF0000']],
            'too_many_colors' => [array_fill(0, 10, '#FF0000')]
        ];
    }
} 
