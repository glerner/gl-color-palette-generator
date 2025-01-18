<?php
/**
 * Color Palette Analyzer Tests
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Palette;
use GL_Color_Palette_Generator\Color_Palette_Analyzer;
use GL_Color_Palette_Generator\Color_Palette_Formatter;

class Test_Color_Palette_Analyzer extends Unit_Test_Case {
    protected Color_Palette_Analyzer $analyzer;
    protected Color_Palette_Formatter $formatter;
    protected Color_Palette $test_palette;

    public function setUp(): void {
        $this->formatter = new Color_Palette_Formatter();
        $this->analyzer = new Color_Palette_Analyzer($this->formatter);
        $this->test_palette = new Color_Palette([
            'name' => 'Test Palette',
            'colors' => ['#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF'],
            'metadata' => ['type' => 'test']
        ]);
    }

    public function test_analyze_palette(): void {
        $analysis = $this->analyzer->analyzePalette($this->test_palette);

        $this->assertArrayHasKey('contrast_ratios', $analysis);
        $this->assertArrayHasKey('harmony', $analysis);
        $this->assertArrayHasKey('accessibility', $analysis);
        $this->assertArrayHasKey('relationships', $analysis);
        $this->assertArrayHasKey('statistics', $analysis);
    }

    public function test_calculate_contrast_ratios(): void {
        $ratios = $this->analyzer->calculateContrastRatios($this->test_palette);

        $this->assertIsArray($ratios);
        $this->assertNotEmpty($ratios);
        $this->assertArrayHasKey('ratio', $ratios[0]);
        $this->assertArrayHasKey('colors', $ratios[0]);
    }

    public function test_analyze_harmony(): void {
        $harmony = $this->analyzer->analyzeHarmony($this->test_palette);

        $this->assertArrayHasKey('complementary', $harmony);
        $this->assertArrayHasKey('analogous', $harmony);
        $this->assertArrayHasKey('triadic', $harmony);
        $this->assertArrayHasKey('harmony_score', $harmony);
        $this->assertIsFloat($harmony['harmony_score']);
        $this->assertGreaterThanOrEqual(0, $harmony['harmony_score']);
        $this->assertLessThanOrEqual(1, $harmony['harmony_score']);
    }

    public function test_analyze_accessibility(): void {
        $accessibility = $this->analyzer->analyzeAccessibility($this->test_palette, 'AA');

        $this->assertArrayHasKey('level', $accessibility);
        $this->assertArrayHasKey('compliant_pairs', $accessibility);
        $this->assertArrayHasKey('non_compliant_pairs', $accessibility);
        $this->assertArrayHasKey('compliance_rate', $accessibility);
        $this->assertIsFloat($accessibility['compliance_rate']);
    }

    public function test_get_color_relationships(): void {
        $relationships = $this->analyzer->getColorRelationships($this->test_palette);

        $this->assertIsArray($relationships);
        $this->assertNotEmpty($relationships);
        $this->assertArrayHasKey('colors', $relationships[0]);
        $this->assertArrayHasKey('hue_difference', $relationships[0]);
        $this->assertArrayHasKey('saturation_difference', $relationships[0]);
        $this->assertArrayHasKey('lightness_difference', $relationships[0]);
    }

    public function test_get_palette_stats(): void {
        $stats = $this->analyzer->getPaletteStats($this->test_palette);

        $this->assertArrayHasKey('color_count', $stats);
        $this->assertArrayHasKey('hue_range', $stats);
        $this->assertArrayHasKey('saturation_range', $stats);
        $this->assertArrayHasKey('lightness_range', $stats);
        $this->assertArrayHasKey('contrast_range', $stats);
    }

    public function test_complementary_colors(): void {
        $palette = new Color_Palette([
            'colors' => ['#FF0000', '#00FFFF'] // Red and Cyan
        ]);

        $harmony = $this->analyzer->analyzeHarmony($palette);
        $this->assertNotEmpty($harmony['complementary']);
    }

    public function test_analogous_colors(): void {
        $palette = new Color_Palette([
            'colors' => ['#FF0000', '#FF3300', '#FF6600'] // Red and orange shades
        ]);

        $harmony = $this->analyzer->analyzeHarmony($palette);
        $this->assertNotEmpty($harmony['analogous']);
    }

    public function test_triadic_colors(): void {
        $palette = new Color_Palette([
            'colors' => ['#FF0000', '#00FF00', '#0000FF'] // Red, Green, Blue
        ]);

        $harmony = $this->analyzer->analyzeHarmony($palette);
        $this->assertNotEmpty($harmony['triadic']);
    }

    public function test_accessibility_levels(): void {
        $palette = new Color_Palette([
            'colors' => ['#000000', '#FFFFFF'] // Maximum contrast
        ]);

        $aa_analysis = $this->analyzer->analyzeAccessibility($palette, 'AA');
        $aaa_analysis = $this->analyzer->analyzeAccessibility($palette, 'AAA');

        $this->assertEquals(1.0, $aa_analysis['compliance_rate']);
        $this->assertEquals(1.0, $aaa_analysis['compliance_rate']);
    }

    public function test_contrast_ratio_calculation(): void {
        $palette = new Color_Palette([
            'colors' => ['#000000', '#FFFFFF'] // Maximum contrast
        ]);

        $ratios = $this->analyzer->calculateContrastRatios($palette);
        $this->assertGreaterThan(20, $ratios[0]['ratio']); // Black/White contrast should be >21
    }

    public function test_harmony_score_calculation(): void {
        // Test complementary harmony
        $complementary = new Color_Palette([
            'colors' => ['#FF0000', '#00FFFF']
        ]);
        $comp_harmony = $this->analyzer->analyzeHarmony($complementary);

        // Test analogous harmony
        $analogous = new Color_Palette([
            'colors' => ['#FF0000', '#FF1A00', '#FF3300']
        ]);
        $ana_harmony = $this->analyzer->analyzeHarmony($analogous);

        $this->assertGreaterThan(0.5, $comp_harmony['harmony_score']);
        $this->assertGreaterThan(0.5, $ana_harmony['harmony_score']);
    }
}
