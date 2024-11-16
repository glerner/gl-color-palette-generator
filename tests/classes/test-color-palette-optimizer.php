<?php
/**
 * Color Palette Optimizer Tests
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteOptimizer;
use GLColorPalette\ColorPaletteAnalyzer;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteOptimizerTest extends TestCase {
    private ColorPaletteOptimizer $optimizer;
    private ColorPaletteAnalyzer $analyzer;
    private ColorPaletteFormatter $formatter;
    private ColorPalette $test_palette;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->analyzer = new ColorPaletteAnalyzer($this->formatter);
        $this->optimizer = new ColorPaletteOptimizer($this->analyzer, $this->formatter);
        $this->test_palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['type' => 'test']
        ]);
    }

    public function test_optimize_palette_default(): void {
        $optimized = $this->optimizer->optimizePalette($this->test_palette);

        $this->assertInstanceOf(ColorPalette::class, $optimized);
        $this->assertNotEquals($this->test_palette->getColors(), $optimized->getColors());
        $this->assertArrayHasKey('optimization', $optimized->getMetadata());
    }

    public function test_optimize_for_accessibility(): void {
        $optimized = $this->optimizer->optimizeForAccessibility($this->test_palette, 'AA');
        $analysis = $this->analyzer->analyzeAccessibility($optimized, 'AA');

        $this->assertGreaterThan(
            0.5, // At least 50% of color pairs should meet AA standards
            $analysis['compliance_rate']
        );
    }

    public function test_optimize_for_harmony_complementary(): void {
        $optimized = $this->optimizer->optimizeForHarmony($this->test_palette, 'complementary');
        $analysis = $this->analyzer->analyzeHarmony($optimized);

        $this->assertNotEmpty($analysis['complementary']);
        $this->assertGreaterThan(0.5, $analysis['harmony_score']);
    }

    public function test_optimize_for_harmony_analogous(): void {
        $optimized = $this->optimizer->optimizeForHarmony($this->test_palette, 'analogous');
        $analysis = $this->analyzer->analyzeHarmony($optimized);

        $this->assertNotEmpty($analysis['analogous']);
        $this->assertGreaterThan(0.5, $analysis['harmony_score']);
    }

    public function test_optimize_for_harmony_triadic(): void {
        $optimized = $this->optimizer->optimizeForHarmony($this->test_palette, 'triadic');
        $analysis = $this->analyzer->analyzeHarmony($optimized);

        $this->assertNotEmpty($analysis['triadic']);
        $this->assertGreaterThan(0.5, $analysis['harmony_score']);
    }

    public function test_optimize_for_contrast(): void {
        $target_ratio = 4.5;
        $optimized = $this->optimizer->optimizeForContrast($this->test_palette, $target_ratio);
        $contrast_ratios = $this->analyzer->calculateContrastRatios($optimized);

        $meets_target = false;
        foreach ($contrast_ratios as $ratio) {
            if ($ratio['ratio'] >= $target_ratio) {
                $meets_target = true;
                break;
            }
        }

        $this->assertTrue($meets_target);
    }

    public function test_invalid_strategy(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->optimizePalette($this->test_palette, ['strategy' => 'invalid']);
    }

    public function test_invalid_harmony_type(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->optimizeForHarmony($this->test_palette, 'invalid');
    }

    public function test_get_available_strategies(): void {
        $strategies = $this->optimizer->getAvailableStrategies();

        $this->assertIsArray($strategies);
        $this->assertContains('accessibility', $strategies);
        $this->assertContains('harmony', $strategies);
        $this->assertContains('contrast', $strategies);
    }

    public function test_get_strategy_options(): void {
        $options = $this->optimizer->getStrategyOptions('accessibility');

        $this->assertIsArray($options);
        $this->assertArrayHasKey('level', $options);
        $this->assertArrayHasKey('min_contrast', $options);
    }

    public function test_get_invalid_strategy_options(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->optimizer->getStrategyOptions('invalid');
    }

    public function test_optimize_for_balance(): void {
        $optimized = $this->optimizer->optimizePalette($this->test_palette, [
            'strategy' => 'balance',
            'hue_spacing' => 45
        ]);

        $this->assertCount(
            count($this->test_palette->getColors()),
            $optimized->getColors()
        );
    }

    public function test_optimize_for_saturation(): void {
        $target_saturation = 75;
        $optimized = $this->optimizer->optimizePalette($this->test_palette, [
            'strategy' => 'saturation',
            'target' => $target_saturation
        ]);

        foreach ($optimized->getColors() as $color) {
            $hsl = $this->formatter->hexToHsl($color);
            $this->assertEqualsWithDelta($target_saturation, $hsl[1], 10);
        }
    }

    public function test_optimize_for_lightness(): void {
        $target_lightness = 50;
        $optimized = $this->optimizer->optimizePalette($this->test_palette, [
            'strategy' => 'lightness',
            'target' => $target_lightness
        ]);

        foreach ($optimized->getColors() as $color) {
            $hsl = $this->formatter->hexToHsl($color);
            $this->assertEqualsWithDelta($target_lightness, $hsl[2], 10);
        }
    }

    public function test_metadata_preservation(): void {
        $optimized = $this->optimizer->optimizePalette($this->test_palette);
        $original_metadata = $this->test_palette->getMetadata();
        $optimized_metadata = $optimized->getMetadata();

        foreach ($original_metadata as $key => $value) {
            $this->assertArrayHasKey($key, $optimized_metadata);
            $this->assertEquals($value, $optimized_metadata[$key]);
        }
    }

    public function test_color_count_preservation(): void {
        $optimized = $this->optimizer->optimizePalette($this->test_palette);

        $this->assertCount(
            count($this->test_palette->getColors()),
            $optimized->getColors()
        );
    }

    /**
     * @test
     * @dataProvider contrastPairsProvider
     */
    public function it_improves_contrast_ratios($color1, $color2, $expected_improvement) {
        $palette = new Color_Palette([$color1, $color2]);

        $optimized = $this->optimizer->optimize($palette, [
            'target_wcag' => 'AA',
            'max_adjustment' => 0.5
        ]);

        $original_contrast = $this->get_contrast_ratio($color1, $color2);
        $optimized_colors = $optimized->get_colors();
        $new_contrast = $this->get_contrast_ratio($optimized_colors[0], $optimized_colors[1]);

        $this->assertGreaterThan($original_contrast, $new_contrast);
        if ($expected_improvement) {
            $this->assertGreaterThanOrEqual(4.5, $new_contrast); // WCAG AA minimum
        }
    }

    public function contrastPairsProvider() {
        return [
            'similar colors' => ['#666666', '#777777', true],
            'low contrast' => ['#FFFF00', '#FFFFFF', true],
            'already good contrast' => ['#000000', '#FFFFFF', false]
        ];
    }

    /**
     * @test
     */
    public function it_maintains_color_relationships() {
        $palette = new Color_Palette([
            '#FF0000', // Red
            '#00FF00', // Green (complementary)
            '#0000FF'  // Blue (triadic)
        ]);

        $optimized = $this->optimizer->optimize($palette, [
            'preserve_hues' => true
        ]);

        // Check that color relationships are maintained
        $original_relationships = $this->get_color_relationships($palette->get_colors());
        $optimized_relationships = $this->get_color_relationships($optimized->get_colors());

        $this->assertEquals(
            count($original_relationships),
            count($optimized_relationships)
        );
    }

    /**
     * @test
     */
    public function it_improves_saturation_consistency() {
        $palette = new Color_Palette([
            '#FF0000', // Fully saturated red
            '#FF9999', // Light pink (low saturation)
            '#990000'  // Dark red (medium saturation)
        ]);

        $optimized = $this->optimizer->optimize($palette);

        $original_variance = $this->get_saturation_variance($palette->get_colors());
        $optimized_variance = $this->get_saturation_variance($optimized->get_colors());

        $this->assertLessThan($original_variance, $optimized_variance);
    }

    /**
     * @test
     */
    public function it_handles_monochromatic_palettes() {
        $palette = new Color_Palette([
            '#000000',
            '#333333',
            '#666666',
            '#999999',
            '#CCCCCC'
        ]);

        $optimized = $this->optimizer->optimize($palette, [
            'target_wcag' => 'AAA'
        ]);

        // Check that optimized palette maintains monochromatic nature
        $optimized_colors = $optimized->get_colors();
        $this->assertCount(5, $optimized_colors);

        // Verify colors are still grayscale
        foreach ($optimized_colors as $color) {
            $rgb = $this->hex_to_rgb($color);
            $this->assertEquals($rgb['r'], $rgb['g']);
            $this->assertEquals($rgb['g'], $rgb['b']);
        }
    }

    /**
     * @test
     */
    public function it_optimizes_for_different_wcag_levels() {
        $palette = new Color_Palette([
            '#777777',
            '#999999'
        ]);

        $aa_optimized = $this->optimizer->optimize($palette, [
            'target_wcag' => 'AA'
        ]);

        $aaa_optimized = $this->optimizer->optimize($palette, [
            'target_wcag' => 'AAA'
        ]);

        // AAA should have higher contrast than AA
        $aa_contrast = $this->get_contrast_ratio(
            $aa_optimized->get_colors()[0],
            $aa_optimized->get_colors()[1]
        );

        $aaa_contrast = $this->get_contrast_ratio(
            $aaa_optimized->get_colors()[0],
            $aaa_optimized->get_colors()[1]
        );

        $this->assertGreaterThan($aa_contrast, $aaa_contrast);
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First hex color
     * @param string $color2 Second hex color
     * @return float Contrast ratio
     */
    private function get_contrast_ratio(string $color1, string $color2): float {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get relative luminance of a color
     *
     * @param string $color Hex color
     * @return float Relative luminance
     */
    private function get_relative_luminance(string $color): float {
        $rgb = $this->hex_to_rgb($color);

        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);

        return 0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b'];
    }

    /**
     * Get color relationships in a palette
     *
     * @param array $colors Array of hex colors
     * @return array Relationships found
     */
    private function get_color_relationships(array $colors): array {
        $relationships = [];

        foreach ($colors as $i => $color1) {
            foreach (array_slice($colors, $i + 1) as $color2) {
                $hue1 = $this->get_hue($color1);
                $hue2 = $this->get_hue($color2);

                $difference = abs($hue1 - $hue2);

                if ($difference <= 30) {
                    $relationships[] = ['type' => 'analogous', 'colors' => [$color1, $color2]];
                }
                if (abs($difference - 180) <= 30) {
                    $relationships[] = ['type' => 'complementary', 'colors' => [$color1, $color2]];
                }
                if (abs($difference - 120) <= 30) {
                    $relationships[] = ['type' => 'triadic', 'colors' => [$color1, $color2]];
                }
            }
        }

        return $relationships;
    }

    /**
     * Calculate saturation variance
     *
     * @param array $colors Array of hex colors
     * @return float Variance
     */
    private function get_saturation_variance(array $colors): float {
        $saturations = array_map([$this, 'get_saturation'], $colors);

        $mean = array_sum($saturations) / count($saturations);
        $squared_diff_sum = array_sum(array_map(function($s) use ($mean) {
            return pow($s - $mean, 2);
        }, $saturations));

        return $squared_diff_sum / count($saturations);
    }

    private function hex_to_rgb($color) {
        // Implementation of hex to RGB conversion
    }
}
