<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteOptimizer;
use GLColorPalette\ColorPaletteFormatter;
use GLColorPalette\ColorPaletteAnalyzer;

class ColorPaletteOptimizerTest extends TestCase {
    private $optimizer;
    private $formatter;
    private $analyzer;
    private $palette;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->analyzer = new ColorPaletteAnalyzer($this->formatter);
        $this->optimizer = new ColorPaletteOptimizer($this->formatter, $this->analyzer);
        $this->palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ]);
    }

    public function test_optimize_palette_improves_scores(): void {
        // Arrange
        $initial_analysis = $this->analyzer->analyze_palette($this->palette);

        // Act
        $optimized = $this->optimizer->optimize_palette($this->palette);
        $final_analysis = $this->analyzer->analyze_palette($optimized);

        // Assert
        $this->assertGreaterThanOrEqual(
            $initial_analysis['harmony']['harmony_score'],
            $final_analysis['harmony']['harmony_score']
        );
        $this->assertGreaterThanOrEqual(
            $initial_analysis['contrast']['statistics']['avg'],
            $final_analysis['contrast']['statistics']['avg']
        );
    }

    public function test_optimize_palette_preserves_primary_color(): void {
        // Arrange
        $primary_color = '#FF0000';
        $palette = new ColorPalette([
            'name' => 'Primary Test',
            'colors' => [$primary_color, '#00FF00', '#0000FF']
        ]);

        // Act
        $optimized = $this->optimizer->optimize_palette($palette, [
            'preserve_primary' => true
        ]);

        // Assert
        $this->assertEquals($primary_color, $optimized->get_colors()[0]);
    }

    public function test_optimize_palette_respects_contrast_requirements(): void {
        // Arrange
        $options = [
            'target_contrast_ratio' => 4.5,
            'min_contrast_ratio' => 3.0,
            'contrast_weight' => 0.8,
            'harmony_weight' => 0.1,
            'accessibility_weight' => 0.1
        ];

        // Act
        $optimized = $this->optimizer->optimize_palette($this->palette, $options);
        $analysis = $this->analyzer->analyze_palette($optimized);

        // Assert
        $this->assertGreaterThanOrEqual(
            3.0,
            $analysis['contrast']['statistics']['min']
        );
    }

    public function test_optimize_palette_handles_empty_palette(): void {
        // Arrange
        $empty_palette = new ColorPalette([
            'name' => 'Empty',
            'colors' => []
        ]);

        // Act
        $optimized = $this->optimizer->optimize_palette($empty_palette);

        // Assert
        $this->assertEmpty($optimized->get_colors());
    }

    public function test_optimize_palette_handles_single_color(): void {
        // Arrange
        $single_color = new ColorPalette([
            'name' => 'Single',
            'colors' => ['#FF0000']
        ]);

        // Act
        $optimized = $this->optimizer->optimize_palette($single_color);

        // Assert
        $this->assertCount(1, $optimized->get_colors());
        $this->assertEquals('#FF0000', $optimized->get_colors()[0]);
    }

    public function test_optimize_palette_adds_optimization_metadata(): void {
        // Act
        $optimized = $this->optimizer->optimize_palette($this->palette);
        $metadata = $optimized->get_metadata();

        // Assert
        $this->assertArrayHasKey('optimized', $metadata);
        $this->assertArrayHasKey('optimization_score', $metadata);
        $this->assertArrayHasKey('optimization_iterations', $metadata);
        $this->assertTrue($metadata['optimized']);
    }

    public function test_optimize_palette_with_different_strategies(): void {
        // Test global strategy
        $global_result = $this->optimizer->optimize_palette($this->palette, [
            'optimization_strategy' => 'global'
        ]);

        // Test local strategy
        $local_result = $this->optimizer->optimize_palette($this->palette, [
            'optimization_strategy' => 'local'
        ]);

        // Assert both strategies produce valid results
        $this->assertCount(3, $global_result->get_colors());
        $this->assertCount(3, $local_result->get_colors());
    }

    public function test_optimize_palette_convergence(): void {
        // Arrange
        $options = [
            'max_iterations' => 10,
            'convergence_threshold' => 0.001
        ];

        // Act
        $optimized = $this->optimizer->optimize_palette($this->palette, $options);
        $metadata = $optimized->get_metadata();

        // Assert
        $this->assertLessThanOrEqual(
            10,
            $metadata['optimization_iterations']
        );
    }

    public function test_optimize_palette_accessibility_improvements(): void {
        // Arrange
        $problematic_palette = new ColorPalette([
            'name' => 'Problematic',
            'colors' => ['#FF0000', '#00FF00'] // Red-green problematic
        ]);

        // Act
        $optimized = $this->optimizer->optimize_palette($problematic_palette, [
            'accessibility_weight' => 0.8
        ]);
        $analysis = $this->analyzer->analyze_palette($optimized);

        // Assert
        $this->assertLessThan(
            count($analysis['accessibility']['color_blindness']['issues']),
            count($this->analyzer->analyze_palette($problematic_palette)['accessibility']['color_blindness']['issues'])
        );
    }

    /**
     * @dataProvider optimizationOptionsProvider
     */
    public function test_optimize_palette_with_different_options(
        array $options,
        array $expectations
    ): void {
        // Act
        $optimized = $this->optimizer->optimize_palette($this->palette, $options);
        $analysis = $this->analyzer->analyze_palette($optimized);

        // Assert
        foreach ($expectations as $key => $expected) {
            $this->assertGreaterThanOrEqual(
                $expected,
                $this->get_nested_value($analysis, $key)
            );
        }
    }

    public function optimizationOptionsProvider(): array {
        return [
            'contrast_focused' => [
                [
                    'contrast_weight' => 0.8,
                    'harmony_weight' => 0.1,
                    'accessibility_weight' => 0.1
                ],
                [
                    'contrast.statistics.avg' => 3.0
                ]
            ],
            'harmony_focused' => [
                [
                    'contrast_weight' => 0.1,
                    'harmony_weight' => 0.8,
                    'accessibility_weight' => 0.1
                ],
                [
                    'harmony.harmony_score' => 0.6
                ]
            ],
            'accessibility_focused' => [
                [
                    'contrast_weight' => 0.1,
                    'harmony_weight' => 0.1,
                    'accessibility_weight' => 0.8
                ],
                [
                    'accessibility.wcag_compliance.aa_pass_rate' => 0.5
                ]
            ]
        ];
    }

    /**
     * Helper method to get nested array value.
     */
    private function get_nested_value(array $array, string $path) {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    public function test_optimize_palette_color_space_handling(): void {
        // Test HSL color space
        $hsl_result = $this->optimizer->optimize_palette($this->palette, [
            'color_space' => 'hsl'
        ]);

        // Verify all colors are valid
        foreach ($hsl_result->get_colors() as $color) {
            $this->assertMatchesRegularExpression(
                '/^#[0-9a-fA-F]{6}$/',
                $color
            );
        }
    }

    public function test_optimize_palette_performance(): void {
        // Arrange
        $start_time = microtime(true);
        $max_execution_time = 2.0; // seconds

        // Act
        $this->optimizer->optimize_palette($this->palette);
        $execution_time = microtime(true) - $start_time;

        // Assert
        $this->assertLessThan($max_execution_time, $execution_time);
    }
} 
