<?php
namespace GL_Color_Palette_Generator\Tests;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Color_Management\Color_Analysis;

class Test_Color_Analysis extends TestCase {
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
}
