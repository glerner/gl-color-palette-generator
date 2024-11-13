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
}
