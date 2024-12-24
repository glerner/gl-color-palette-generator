<?php
/**
 * Integration tests for the Color Metrics Analyzer
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Test_Case_Integration;
use GL_Color_Palette_Generator\Color_Management\Color_Metrics_Analyzer;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Color_Management\Color_Calculator;

/**
 * Test Color Metrics Analyzer integration
 */
class Test_Color_Metrics_Analyzer_Integration extends Test_Case_Integration {
    /**
     * Test color contrast calculation
     */
    public function test_calculate_contrast() {
        $utility = new Color_Utility();
        $calculator = new Color_Calculator();
        $analyzer = new Color_Metrics_Analyzer($utility, $calculator);
        $contrast = $analyzer->calculate_contrast('#FFFFFF', '#000000');
        $this->assertEquals(21, $contrast);
    }

    /**
     * Test color accessibility compliance
     */
    public function test_check_accessibility() {
        $utility = new Color_Utility();
        $calculator = new Color_Calculator();
        $analyzer = new Color_Metrics_Analyzer($utility, $calculator);
        $result = $analyzer->check_accessibility(['#FFFFFF', '#000000']);
        $this->assertArrayHasKey('overall_score', $result);
        $this->assertArrayHasKey('combinations', $result);
    }

    /**
     * Test color harmony analysis
     */
    public function test_analyze_harmony() {
        $utility = new Color_Utility();
        $calculator = new Color_Calculator();
        $analyzer = new Color_Metrics_Analyzer($utility, $calculator);
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $harmony = $analyzer->analyze_harmony($colors);
        $this->assertIsArray($harmony);
        $this->assertArrayHasKey('complementary', $harmony);
    }
}
