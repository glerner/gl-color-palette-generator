<?php

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Color_Management\Color_Metrics_Analyzer;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use WP_UnitTestCase;

/**
 * Integration tests for Color Metrics Analyzer
 */
class Test_Color_Metrics_Analyzer_Integration extends WP_UnitTestCase {
    /**
     * @var Color_Metrics_Analyzer
     */
    private $analyzer;

    /**
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Set up the test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->analyzer = new Color_Metrics_Analyzer();
        $this->color_utility = new Color_Utility();
    }

    /**
     * Clean up the test environment
     */
    public function tearDown(): void {
        parent::tearDown();
        $this->analyzer = null;
        $this->color_utility = null;
    }

    /**
     * Test complete color analysis workflow
     */
    public function test_complete_color_analysis_workflow(): void {
        $test_color = '#336699';

        // Perform complete analysis
        $result = $this->analyzer->analyze_color($test_color);

        // Verify all analysis components
        $this->assertIsArray($result);
        $this->assertArrayHasKey('basic_metrics', $result);
        $this->assertArrayHasKey('perceptual_metrics', $result);
        $this->assertArrayHasKey('color_space_values', $result);

        // Verify color space conversions
        $rgb = $this->color_utility->hex_to_rgb($test_color);
        $cmyk = $this->color_utility->rgb_to_cmyk($rgb);
        $lab = $this->color_utility->rgb_to_lab($rgb);

        // Verify print analysis
        $print_result = $this->analyzer->analyze_print_output($test_color);
        $this->assertEquals($cmyk, $print_result['color_profile']['cmyk_values']);

        // Verify screen display analysis
        $screen_result = $this->analyzer->analyze_screen_display($test_color);
        $this->assertTrue($screen_result['color_spaces']['srgb']['in_gamut']);
    }

    /**
     * Test cross-device color consistency
     */
    public function test_cross_device_color_consistency(): void {
        $test_colors = [
            '#FF0000' => 'red',
            '#00FF00' => 'green',
            '#0000FF' => 'blue'
        ];

        foreach ($test_colors as $color => $name) {
            // Analyze color across different devices
            $device_analysis = $this->analyzer->analyze_device_display($color);

            // Verify consistency across devices
            $this->assertEqualsWithDelta(
                $device_analysis['mobile']['color_accuracy'],
                $device_analysis['desktop']['color_accuracy'],
                0.1,
                "Color accuracy should be consistent across devices for $name"
            );
        }
    }

    /**
     * Test print-to-screen color matching
     */
    public function test_print_screen_color_matching(): void {
        $test_color = '#CC3366';

        // Get print and screen analyses
        $print_analysis = $this->analyzer->analyze_print_output($test_color);
        $screen_analysis = $this->analyzer->analyze_screen_display($test_color);

        // Verify color matching capabilities
        $this->assertIsArray($print_analysis['color_profile']);
        $this->assertIsArray($screen_analysis['color_spaces']);

        // Check gamut overlap
        $print_gamut = $print_analysis['color_profile']['gamut_coverage'];
        $screen_gamut = $screen_analysis['color_spaces']['srgb']['coverage_percentage'];

        // Verify reasonable gamut overlap
        $this->assertGreaterThan(
            80,
            min($print_gamut, $screen_gamut),
            'Print and screen gamut coverage should have significant overlap'
        );
    }
}
