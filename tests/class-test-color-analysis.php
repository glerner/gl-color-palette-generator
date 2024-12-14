<?php
/**
 * Tests for Color Analysis functionality
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Color_Analysis;
use WP_UnitTestCase;

/**
 * Test Color Analysis functionality
 */
class Test_Color_Analysis extends WP_UnitTestCase {
    private $analyzer;
    private $test_colors;

    public function setUp(): void {
        parent::setUp();
        $this->analyzer = new Color_Analysis();

        // Test palette with known properties
        $this->test_colors = [
            'primary' => [
                'hex' => '#2C3E50', // Cool, dark blue
                'name' => 'Deep Blue'
            ],
            'secondary' => [
                'hex' => '#E74C3C', // Warm red
                'name' => 'Bright Red'
            ],
            'tertiary' => [
                'hex' => '#3498DB', // Cool blue
                'name' => 'Sky Blue'
            ],
            'accent' => [
                'hex' => '#F1C40F', // Warm yellow
                'name' => 'Sunny Yellow'
            ]
        ];
    }

    /**
     * @test
     */
    public function test_harmony_analysis(): void {
        $harmony = $this->analyzer->analyze_harmony($this->test_colors);

        // Test structure
        $this->assertArrayHasKey('scheme_type', $harmony);
        $this->assertArrayHasKey('balance', $harmony);
        $this->assertArrayHasKey('temperature', $harmony);
        $this->assertArrayHasKey('recommendations', $harmony);

        // Test temperature analysis
        $temp = $harmony['temperature'];
        $this->assertArrayHasKey('dominant', $temp);
        $this->assertArrayHasKey('distribution', $temp);
        $this->assertEquals('mixed', $temp['balance']); // Should be mixed warm/cool

        // Test balance
        $balance = $harmony['balance'];
        $this->assertArrayHasKey('lightness', $balance);
        $this->assertArrayHasKey('saturation', $balance);
        $this->assertIsFloat($balance['lightness']['std_dev']);
        $this->assertIsFloat($balance['saturation']['std_dev']);
    }

    /**
     * @test
     */
    public function test_contrast_levels(): void {
        $harmony = $this->analyzer->analyze_harmony($this->test_colors);
        $contrast = $harmony['contrast_levels'];

        // Test structure
        $this->assertArrayHasKey('pairs', $contrast);
        $this->assertArrayHasKey('average', $contrast);
        $this->assertArrayHasKey('min', $contrast);
        $this->assertArrayHasKey('max', $contrast);

        // Test contrast calculations
        $this->assertGreaterThan(0, $contrast['min']);
        $this->assertLessThanOrEqual(21, $contrast['max']); // Max possible contrast is 21
        $this->assertGreaterThan($contrast['min'], $contrast['max']);
    }

    /**
     * @test
     */
    public function test_monochromatic_detection(): void {
        $mono_colors = [
            ['hex' => '#000000'],
            ['hex' => '#333333'],
            ['hex' => '#666666'],
            ['hex' => '#999999']
        ];

        $harmony = $this->analyzer->analyze_harmony($mono_colors);
        $this->assertEquals('monochromatic', $harmony['scheme_type']);
    }

    /**
     * @test
     */
    public function test_complementary_detection(): void {
        $complementary_colors = [
            ['hex' => '#FF0000'], // Red
            ['hex' => '#00FFFF']  // Cyan
        ];

        $harmony = $this->analyzer->analyze_harmony($complementary_colors);
        $this->assertEquals('complementary', $harmony['scheme_type']);
    }

    /**
     * @test
     */
    public function test_temperature_analysis(): void {
        $harmony = $this->analyzer->analyze_harmony($this->test_colors);
        $temp = $harmony['temperature'];

        // Test warm colors are detected
        $this->assertArrayHasKey('warm', $temp['distribution']);

        // Test cool colors are detected
        $this->assertArrayHasKey('cool', $temp['distribution']);

        // Test mixed temperature balance
        $this->assertEquals('mixed', $temp['balance']);
    }

    public function test_analyze_color_contrast() {
        $color1 = '#FFFFFF';  // White
        $color2 = '#000000';  // Black
        
        $contrast = $this->analyzer->analyze_color_contrast($color1, $color2);
        $this->assertEquals(21, $contrast);
    }

    public function test_analyze_color_harmony() {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        
        $harmony = $this->analyzer->analyze_color_harmony($colors);
        $this->assertIsArray($harmony);
        $this->assertArrayHasKey('complementary', $harmony);
        $this->assertArrayHasKey('analogous', $harmony);
    }
} 
