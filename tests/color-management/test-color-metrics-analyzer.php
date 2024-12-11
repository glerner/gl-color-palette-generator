<?php

namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Color_Management\Color_Metrics_Analyzer;
use WP_UnitTestCase;

class Test_Color_Metrics_Analyzer extends WP_UnitTestCase {
    private $analyzer;

    public function setUp(): void {
        parent::setUp();
        $this->analyzer = new Color_Metrics_Analyzer();
    }

    public function test_analyze_readability() {
        $color = '#FF0000';
        $result = $this->analyzer->analyze_readability($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(16, $result); // Test for 16px font size
        $this->assertArrayHasKey('#FFFFFF', $result[16]); // Test for white background
        $this->assertTrue($result[16]['#FFFFFF']['meets_wcag_aa']);
    }

    public function test_interface_recommendations() {
        $color = '#0066CC';
        $result = $this->analyzer->get_interface_recommendations($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary_actions', $result);
        $this->assertArrayHasKey('suitable_for_buttons', $result['primary_actions']);
        $this->assertTrue($result['primary_actions']['suitable_for_buttons']['suitable']);
    }

    public function test_print_output_analysis() {
        $color = '#336699';
        $result = $this->analyzer->analyze_print_output($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('color_profile', $result);
        $this->assertArrayHasKey('cmyk_values', $result['color_profile']);
        $this->assertLessThanOrEqual(400, $result['color_profile']['ink_coverage']);
    }

    public function test_screen_display_analysis() {
        $color = '#FF5500';
        $result = $this->analyzer->analyze_screen_display($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('color_spaces', $result);
        $this->assertArrayHasKey('srgb', $result['color_spaces']);
        $this->assertArrayHasKey('in_gamut', $result['color_spaces']['srgb']);
    }

    public function test_color_space_compatibility() {
        $color = '#00FF00';
        $result = $this->analyzer->analyze_color_space_compatibility($color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('srgb_coverage', $result);
        $this->assertTrue($result['srgb_coverage']['in_gamut']);
    }

    public function test_metameric_index() {
        $color = '#808080';
        $lab = ['l' => 50, 'a' => 0, 'b' => 0];
        $result = $this->analyzer->calculate_metameric_index($lab);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('daylight', $result);
        $this->assertArrayHasKey('overall_index', $result);
        $this->assertIsFloat($result['overall_index']);
    }

    /**
     * Test device display analysis
     */
    public function test_device_display_analysis() {
        $test_colors = [
            '#000000' => ['amoled' => true, 'power_efficiency' => 'high'],
            '#FFFFFF' => ['amoled' => false, 'power_efficiency' => 'low'],
            '#808080' => ['amoled' => false, 'power_efficiency' => 'medium']
        ];

        foreach ($test_colors as $color => $expected) {
            $result = $this->analyzer->analyze_device_display($color);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('mobile', $result);
            $this->assertEquals($expected['amoled'],
                              $result['mobile']['amoled_optimization']['recommended']);
            $this->assertEquals($expected['power_efficiency'],
                              $result['mobile']['power_efficiency']['rating']);
        }
    }

    /**
     * Test print production analysis
     */
    public function test_print_production_analysis() {
        $test_colors = [
            '#FF0000' => ['ink_density' => 'high', 'drying_time' => 'extended'],
            '#000000' => ['ink_density' => 'very_high', 'drying_time' => 'maximum'],
            '#FFFF00' => ['ink_density' => 'low', 'drying_time' => 'minimal']
        ];

        foreach ($test_colors as $color => $expected) {
            $result = $this->analyzer->analyze_print_production($color);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('press_conditions', $result);
            $this->assertEquals($expected['ink_density'],
                              $result['press_conditions']['density_classification']);
            $this->assertEquals($expected['drying_time'],
                              $result['press_conditions']['drying_time']['category']);
        }
    }

    /**
     * Test edge cases and boundary conditions
     */
    public function test_edge_cases() {
        $edge_cases = [
            '#000000' => 'darkest',
            '#FFFFFF' => 'lightest',
            '#FF0000' => 'pure_red',
            '#00FF00' => 'pure_green',
            '#0000FF' => 'pure_blue'
        ];

        foreach ($edge_cases as $color => $case) {
            $result = $this->analyzer->analyze_color($color);
            $this->assertIsArray($result);
            $this->assertNotEmpty($result);
            $this->assertArrayHasKey('basic_metrics', $result);
        }
    }

    /**
     * Test color combination generation
     */
    public function test_color_combinations() {
        $test_colors = [
            '#FF0000' => ['complementary' => '#00FFFF'],
            '#00FF00' => ['complementary' => '#FF00FF'],
            '#0000FF' => ['complementary' => '#FFFF00']
        ];

        foreach ($test_colors as $color => $expected) {
            $combinations = $this->analyzer->get_color_combinations($color);

            $this->assertIsArray($combinations);
            $this->assertArrayHasKey('complementary', $combinations);
            $this->assertEquals(
                strtoupper($expected['complementary']),
                strtoupper($combinations['complementary']['complementary'])
            );
        }
    }

    /**
     * Test accessibility scoring
     */
    public function test_accessibility_scoring() {
        $test_cases = [
            '#000000' => ['meets_wcag_aa' => true],  // Black
            '#FFFFFF' => ['meets_wcag_aa' => true],  // White
            '#808080' => ['meets_wcag_aa' => false]  // Medium gray
        ];

        foreach ($test_cases as $color => $expected) {
            $score = $this->analyzer->calculate_accessibility_score($color);

            $this->assertIsArray($score);
            $this->assertArrayHasKey('detailed_scores', $score);
            $this->assertEquals(
                $expected['meets_wcag_aa'],
                $score['detailed_scores']['contrast_ratio']['meets_wcag_aa']
            );
        }
    }

    /**
     * Test color naming conventions
     */
    public function test_color_naming() {
        $test_cases = [
            '#FF0000' => ['common_name' => 'red'],
            '#00FF00' => ['common_name' => 'green'],
            '#0000FF' => ['common_name' => 'blue']
        ];

        foreach ($test_cases as $color => $expected) {
            $names = $this->analyzer->get_standardized_color_name($color);

            $this->assertIsArray($names);
            $this->assertArrayHasKey('common_name', $names);
            $this->assertEquals(
                $expected['common_name'],
                strtolower($names['common_name'])
            );
        }
    }
} 
