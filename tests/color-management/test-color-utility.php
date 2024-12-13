<?php
/**
 * Test Color Utility Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use PHPUnit\Framework\TestCase;

/**
 * Class Test_Color_Utility
 */
class Test_Color_Utility extends TestCase {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private $color_util;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->color_util = new Color_Utility();
    }

    /**
     * Test color difference calculation for similar colors
     */
    public function test_similar_colors_have_small_difference() {
        $color1 = '#ff0000';  // Red
        $color2 = '#ff0505';  // Very slightly different red

        $difference = $this->color_util->get_color_difference($color1, $color2);

        $this->assertLessThan(
            10,
            $difference,
            'Similar colors should have a small difference value'
        );
    }

    /**
     * Test color difference calculation for distinct colors
     */
    public function test_distinct_colors_have_large_difference() {
        $color1 = '#ff0000';  // Red
        $color2 = '#00ff00';  // Green

        $difference = $this->color_util->get_color_difference($color1, $color2);

        $this->assertGreaterThan(
            50,
            $difference,
            'Distinct colors should have a large difference value'
        );
    }

    /**
     * Test color distinctiveness check for similar colors
     */
    public function test_similar_colors_are_not_distinct() {
        $colors = [
            '#ff0000',  // Red
            '#ff0505',  // Very similar red
            '#ff0a0a',  // Similar red
            '#ff0f0f',  // Similar red
            '#ff1414',  // Similar red
        ];

        $this->assertFalse(
            $this->color_util->are_colors_distinct($colors),
            'Similar colors should not be considered distinct'
        );
    }

    /**
     * Test color distinctiveness check for distinct colors
     */
    public function test_distinct_colors_are_distinct() {
        $colors = [
            '#ff0000',  // Red
            '#00ff00',  // Green
            '#0000ff',  // Blue
            '#ffff00',  // Yellow
            '#ff00ff',  // Magenta
        ];

        $this->assertTrue(
            $this->color_util->are_colors_distinct($colors),
            'Different colors should be considered distinct'
        );
    }

    /**
     * Test distinctiveness score for similar colors
     */
    public function test_similar_colors_have_low_distinctiveness_score() {
        $colors = [
            '#ff0000',
            '#ff0505',
            '#ff0a0a',
            '#ff0f0f',
            '#ff1414',
        ];

        $score = $this->color_util->get_distinctiveness_score($colors);

        $this->assertLessThan(
            30,
            $score,
            'Similar colors should have a low distinctiveness score'
        );
    }

    /**
     * Test distinctiveness score for distinct colors
     */
    public function test_distinct_colors_have_high_distinctiveness_score() {
        $colors = [
            '#ff0000',  // Red
            '#00ff00',  // Green
            '#0000ff',  // Blue
            '#ffff00',  // Yellow
            '#ff00ff',  // Magenta
        ];

        $score = $this->color_util->get_distinctiveness_score($colors);

        $this->assertGreaterThan(
            70,
            $score,
            'Different colors should have a high distinctiveness score'
        );
    }

    /**
     * Test hex to Lab conversion maintains consistency
     */
    public function test_hex_to_lab_conversion_consistency() {
        $reflection = new \ReflectionClass($this->color_util);
        $method = $reflection->getMethod('hex_to_lab');
        $method->setAccessible(true);

        $color = '#ff0000';
        $lab1 = $method->invoke($this->color_util, $color);
        $lab2 = $method->invoke($this->color_util, $color);

        $this->assertEquals(
            $lab1,
            $lab2,
            'Same color should convert to same Lab values'
        );
    }

    /**
     * Test color difference is symmetric
     */
    public function test_color_difference_is_symmetric() {
        $color1 = '#ff0000';
        $color2 = '#00ff00';

        $diff1 = $this->color_util->get_color_difference($color1, $color2);
        $diff2 = $this->color_util->get_color_difference($color2, $color1);

        $this->assertEquals(
            $diff1,
            $diff2,
            'Color difference should be the same regardless of order'
        );
    }
}
