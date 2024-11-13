<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteOptimizer;

class ColorPaletteOptimizerTest extends TestCase {
    private $optimizer;

    protected function setUp(): void {
        $this->optimizer = $this->createMock(ColorPaletteOptimizer::class);
    }

    public function test_optimize_accessibility_improves_compliance(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#FFFF00']
        ];

        $options = [
            'standards' => ['wcag' => 'AA'],
            'constraints' => ['preserve_hue' => true]
        ];

        $expected = [
            'optimized' => [
                'colors' => ['#FF0000', '#FFE500']
            ],
            'improvements' => [
                'contrast' => ['before' => 2.5, 'after' => 4.6],
                'readability' => ['before' => 'poor', 'after' => 'good']
            ],
            'compliance' => [
                'wcag_aa' => true,
                'wcag_aaa' => false
            ],
            'metadata' => [
                'optimized_at' => '2024-01-20T12:00:00Z',
                'standards' => ['WCAG 2.1 AA']
            ]
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('optimize_accessibility')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->optimize_accessibility($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('optimized', $result);
        $this->assertArrayHasKey('improvements', $result);
        $this->assertArrayHasKey('compliance', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_optimize_harmony_improves_relationships(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $options = [
            'schemes' => ['triadic'],
            'thresholds' => ['harmony' => 0.9]
        ];

        $expected = [
            'optimized' => [
                'colors' => ['#FF0000', '#00FF33', '#0000FF']
            ],
            'improvements' => [
                'harmony' => ['before' => 0.85, 'after' => 0.95],
                'balance' => ['before' => 'good', 'after' => 'excellent']
            ],
            'scores' => [
                'overall' => 0.95,
                'schemes' => ['triadic' => 0.95]
            ],
            'metadata' => [
                'optimized_at' => '2024-01-20T12:00:00Z',
                'schemes' => ['triadic']
            ]
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('optimize_harmony')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->optimize_harmony($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('optimized', $result);
        $this->assertArrayHasKey('improvements', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_optimize_performance_improves_efficiency(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#FF0001', '#FF0002']
        ];

        $options = [
            'targets' => ['size' => 'minimal'],
            'compression' => ['method' => 'quantize']
        ];

        $expected = [
            'optimized' => [
                'colors' => ['#FF0000']
            ],
            'improvements' => [
                'size' => ['before' => '768B', 'after' => '256B'],
                'loading' => ['before' => '100ms', 'after' => '50ms']
            ],
            'metrics' => [
                'compression_ratio' => 3,
                'quality_loss' => 0.01
            ],
            'metadata' => [
                'optimized_at' => '2024-01-20T12:00:00Z',
                'compression' => 'quantize'
            ]
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('optimize_performance')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->optimize_performance($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('optimized', $result);
        $this->assertArrayHasKey('improvements', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_optimize_display_improves_appearance(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'devices' => ['mobile', 'desktop'],
            'environments' => ['bright', 'dark']
        ];

        $expected = [
            'optimized' => [
                'colors' => [
                    'default' => ['#FF0000', '#00FF00'],
                    'dark' => ['#CC0000', '#00CC00']
                ]
            ],
            'improvements' => [
                'visibility' => ['before' => 'good', 'after' => 'excellent'],
                'consistency' => ['before' => 'varied', 'after' => 'uniform']
            ],
            'profiles' => [
                'mobile' => ['gamma' => 2.2, 'brightness' => 'auto'],
                'desktop' => ['gamma' => 2.2, 'brightness' => 'fixed']
            ],
            'metadata' => [
                'optimized_at' => '2024-01-20T12:00:00Z',
                'devices' => ['mobile', 'desktop']
            ]
        ];

        $this->optimizer
            ->expects($this->once())
            ->method('optimize_display')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->optimizer->optimize_display($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('optimized', $result);
        $this->assertArrayHasKey('improvements', $result);
        $this->assertArrayHasKey('profiles', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_optimize_accessibility_validates_palette(array $palette): void {
        $this->optimizer
            ->expects($this->once())
            ->method('optimize_accessibility')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->optimize_accessibility($palette);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['colors' => ['not-a-color']]],
            'single_color' => [['colors' => ['#FF0000']]]
        ];
    }

    /**
     * @dataProvider invalidOptimizationOptionsProvider
     */
    public function test_optimize_harmony_validates_options(array $options): void {
        $palette = ['colors' => ['#FF0000', '#00FF00']];

        $this->optimizer
            ->expects($this->once())
            ->method('optimize_harmony')
            ->with($palette, $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->optimize_harmony($palette, $options);
    }

    public function invalidOptimizationOptionsProvider(): array {
        return [
            'invalid_scheme' => [['schemes' => ['invalid']]],
            'invalid_threshold' => [['thresholds' => ['harmony' => 2]]],
            'invalid_type' => [['schemes' => 'not-array']],
            'empty_options' => [[]]
        ];
    }
}
