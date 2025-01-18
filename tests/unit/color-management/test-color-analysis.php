<?php
namespace GL_Color_Palette_Generator\Tests\Unit\Color_Management;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Analysis;

class Test_Color_Analysis extends Unit_Test_Case {
    private Color_Analysis $analyzer;

    protected function set_up() {
        parent::set_up();
        $this->analyzer = new Color_Analysis();
    }

    public function test_analyze_contrast_ratio() {
        $test_cases = [
            // White background, black text (maximum contrast)
            [
                'background' => '#FFFFFF',
                'text' => '#000000',
                'expected' => 21,
            ],
            // White background, white text (minimum contrast)
            [
                'background' => '#FFFFFF',
                'text' => '#FFFFFF',
                'expected' => 1,
            ],
            // Real-world example
            [
                'background' => '#2C3E50',
                'text' => '#ECF0F1',
                'expected' => 13.03,
            ],
        ];

        foreach ($test_cases as $case) {
            $ratio = $this->analyzer->get_contrast_ratio($case['background'], $case['text']);
            $this->assertEqualsWithDelta(
                $case['expected'],
                $ratio,
                0.1,
                "Contrast ratio calculation failed for {$case['background']} and {$case['text']}"
            );
        }
    }

    public function test_analyze_color_harmony() {
        $palette = ['#2C3E50', '#E74C3C', '#ECF0F1', '#3498DB', '#2ECC71'];

        $harmony = $this->analyzer->analyze_harmony($palette);

        $this->assertArrayHasKey('contrast_scores', $harmony);
        $this->assertArrayHasKey('harmony_score', $harmony);
        $this->assertArrayHasKey('accessibility_score', $harmony);

        // Harmony score should be between 0 and 1
        $this->assertGreaterThanOrEqual(0, $harmony['harmony_score']);
        $this->assertLessThanOrEqual(1, $harmony['harmony_score']);
    }

    public function test_analyze_accessibility() {
        $palette = ['#2C3E50', '#ECF0F1'];

        $accessibility = $this->analyzer->analyze_accessibility($palette[0], $palette[1]);

        $this->assertArrayHasKey('wcag_aa_normal', $accessibility);
        $this->assertArrayHasKey('wcag_aa_large', $accessibility);
        $this->assertArrayHasKey('wcag_aaa_normal', $accessibility);
        $this->assertArrayHasKey('wcag_aaa_large', $accessibility);

        // Test against WCAG 2.1 criteria
        $this->assertTrue($accessibility['wcag_aa_normal']);  // Should pass AA for normal text
        $this->assertTrue($accessibility['wcag_aa_large']);   // Should pass AA for large text
    }

    public function test_invalid_color_input() {
        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->get_contrast_ratio('invalid', '#000000');
    }

    /**
     * Tests from Color_Analyzer interface
     */
    public function test_analyze_harmony(): void {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $result = $this->analyzer->analyze_harmony($colors);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('harmony_type', $result);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('suggestions', $result);
        
        // Verify harmony type is valid
        $this->assertContains(
            $result['harmony_type'],
            ['complementary', 'analogous', 'triadic', 'tetradic', 'monochromatic']
        );

        // Verify score is between 0 and 1
        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(1, $result['score']);
    }

    public function test_analyze_color_properties(): void {
        $color = '#FF5733';
        $result = $this->analyzer->analyze_color_properties($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hue', $result);
        $this->assertArrayHasKey('saturation', $result);
        $this->assertArrayHasKey('brightness', $result);
        $this->assertArrayHasKey('temperature', $result);

        // Verify property ranges
        $this->assertGreaterThanOrEqual(0, $result['hue']);
        $this->assertLessThanOrEqual(360, $result['hue']);
        $this->assertGreaterThanOrEqual(0, $result['saturation']);
        $this->assertLessThanOrEqual(100, $result['saturation']);
        $this->assertGreaterThanOrEqual(0, $result['brightness']);
        $this->assertLessThanOrEqual(100, $result['brightness']);
    }

    public function test_get_color_relationships(): void {
        $color = '#FF0000';  // Pure red
        $result = $this->analyzer->get_color_relationships($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('complementary', $result);
        $this->assertArrayHasKey('analogous', $result);
        $this->assertArrayHasKey('triadic', $result);

        // Verify complementary is valid hex
        $this->assertMatchesRegularExpression('/^#[A-Fa-f0-9]{6}$/', $result['complementary']);

        // Verify analogous colors are valid
        $this->assertIsArray($result['analogous']);
        $this->assertCount(2, $result['analogous']);
        foreach ($result['analogous'] as $color) {
            $this->assertMatchesRegularExpression('/^#[A-Fa-f0-9]{6}$/', $color);
        }

        // Verify triadic colors are valid
        $this->assertIsArray($result['triadic']);
        $this->assertCount(2, $result['triadic']);
        foreach ($result['triadic'] as $color) {
            $this->assertMatchesRegularExpression('/^#[A-Fa-f0-9]{6}$/', $color);
        }
    }
}
