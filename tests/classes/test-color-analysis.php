<?php

namespace GLColorPalette\Tests;

use GLColorPalette\Color_Analysis;
use GLColorPalette\Color_Palette;
use WP_UnitTestCase;

class Test_Color_Analysis extends WP_UnitTestCase {
    private Color_Analysis $analysis;

    public function setUp(): void {
        parent::setUp();
        $this->analysis = new Color_Analysis();
    }

    /**
     * @test
     * @testdox Analyzes contrast ratios correctly
     */
    public function it_analyzes_contrast_ratios(): void {
        $palette = new Color_Palette(['#FFFFFF', '#000000', '#FF0000']);
        $results = $this->analysis->analyze($palette, ['contrast']);

        $this->assertArrayHasKey('contrast', $results);
        $this->assertArrayHasKey('ratios', $results['contrast']);

        // White and black should have highest contrast (21:1)
        $highest_contrast = $results['contrast']['best_text_combinations'][0];
        $this->assertEquals(21, round($highest_contrast['ratio']));
        $this->assertTrue($highest_contrast['passes_aaa_normal']);
    }

    /**
     * @test
     * @testdox Detects color harmony patterns correctly
     */
    public function it_detects_harmony_patterns(): void {
        // Test complementary colors
        $complementary = new Color_Palette(['#FF0000', '#00FFFF']);
        $results = $this->analysis->analyze($complementary, ['harmony']);
        $this->assertContains('complementary', $results['harmony']['harmony_type']['patterns']);

        // Test analogous colors
        $analogous = new Color_Palette(['#FF0000', '#FF3300', '#FF6600']);
        $results = $this->analysis->analyze($analogous, ['harmony']);
        $this->assertContains('analogous', $results['harmony']['harmony_type']['patterns']);

        // Test triadic colors
        $triadic = new Color_Palette(['#FF0000', '#00FF00', '#0000FF']);
        $results = $this->analysis->analyze($triadic, ['harmony']);
        $this->assertContains('triadic', $results['harmony']['harmony_type']['patterns']);
    }

    /**
     * @test
     * @testdox Analyzes accessibility compliance correctly
     */
    public function it_analyzes_accessibility(): void {
        $palette = new Color_Palette(['#FFFFFF', '#777777', '#000000']);
        $results = $this->analysis->analyze($palette, ['accessibility']);

        $this->assertArrayHasKey('wcag_compliance', $results['accessibility']);
        $this->assertArrayHasKey('color_blindness', $results['accessibility']);
        $this->assertArrayHasKey('recommendations', $results['accessibility']);

        // Test WCAG compliance
        $wcag = $results['accessibility']['wcag_compliance'];
        $this->assertNotEmpty($wcag['AA']['normal']);
        $this->assertNotEmpty($wcag['AAA']['normal']);
    }

    /**
     * @test
     * @testdox Simulates color blindness correctly
     */
    public function it_simulates_color_blindness(): void {
        $palette = new Color_Palette(['#FF0000']); // Pure red
        $results = $this->analysis->analyze($palette, ['accessibility']);

        $simulation = $results['accessibility']['color_blindness'][0];

        // Protanopia should see red differently
        $this->assertNotEquals(
            $simulation['color'],
            sprintf(
                '#%02x%02x%02x',
                ...$simulation['protanopia']
            )
        );
    }

    /**
     * @test
     * @testdox Calculates color distribution correctly
     */
    public function it_analyzes_distribution(): void {
        $palette = new Color_Palette(['#FF0000', '#00FF00', '#0000FF']);
        $results = $this->analysis->analyze($palette, ['harmony']);

        $distribution = $results['harmony']['distribution'];

        // Check distribution ranges
        $this->assertArrayHasKey('hue', $distribution);
        $this->assertArrayHasKey('saturation', $distribution);
        $this->assertArrayHasKey('lightness', $distribution);

        // Primary colors should have maximum saturation
        $this->assertEquals(100, $distribution['saturation']['range']['max']);
    }

    /**
     * @test
     * @testdox Generates appropriate recommendations
     */
    public function it_generates_recommendations(): void {
        // Test low contrast palette
        $low_contrast = new Color_Palette(['#777777', '#888888', '#999999']);
        $results = $this->analysis->analyze($low_contrast);

        $this->assertNotEmpty($results['accessibility']['recommendations']);
        $this->assertStringContainsString(
            'contrast',
            $results['accessibility']['recommendations'][0]['type']
        );

        // Test accessible palette
        $accessible = new Color_Palette(['#FFFFFF', '#000000']);
        $results = $this->analysis->analyze($accessible);

        // Should not recommend contrast improvements
        $recommendations = array_filter(
            $results['accessibility']['recommendations'],
            fn($rec) => $rec['type'] === 'contrast'
        );
        $this->assertEmpty($recommendations);
    }
} 
