<?php
/**
 * Tests for Color_Metrics class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Metrics;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * Class Test_Color_Metrics
 */
class Test_Color_Metrics extends TestCase implements Color_Constants {
    /**
     * Test instance
     *
     * @var Color_Metrics
     */
    private $instance;

    /**
     * Mock color utility
     *
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();

        $this->color_utility = Mockery::mock(Color_Utility::class);
        $this->instance = new Color_Metrics($this->color_utility);
    }

    /**
     * Clean up test environment
     */
    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test calculate_color_difference method
     */
    public function test_calculate_color_difference() {
        $color1 = '#ff0000';
        $color2 = '#00ff00';

        $result = $this->instance->calculate_color_difference($color1, $color2);
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);

        // Test invalid color
        $result = $this->instance->calculate_color_difference('invalid', $color2);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_brightness method
     */
    public function test_calculate_brightness() {
        $colors = [
            '#000000' => 0,
            '#ffffff' => 1,
            '#ff0000' => 0.2126,
            '#00ff00' => 0.7152,
            '#0000ff' => 0.0722
        ];

        foreach ($colors as $color => $expected) {
            $result = $this->instance->calculate_brightness($color);
            $this->assertIsFloat($result);
            $this->assertEqualsWithDelta($expected, $result, 0.01);
        }

        // Test invalid color
        $result = $this->instance->calculate_brightness('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_saturation method
     */
    public function test_calculate_saturation() {
        $colors = [
            '#ff0000' => 1,
            '#808080' => 0,
            '#800000' => 1
        ];

        foreach ($colors as $color => $expected) {
            $result = $this->instance->calculate_saturation($color);
            $this->assertIsFloat($result);
            $this->assertEqualsWithDelta($expected, $result, 0.01);
        }

        // Test invalid color
        $result = $this->instance->calculate_saturation('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_temperature method
     */
    public function test_calculate_temperature() {
        $colors = [
            '#ff0000' => 'warm',
            '#0000ff' => 'cool'
        ];

        foreach ($colors as $color => $type) {
            $result = $this->instance->calculate_temperature($color);
            $this->assertIsFloat($result);

            if ($type === 'warm') {
                $this->assertGreaterThan(5000, $result);
            } else {
                $this->assertLessThan(5000, $result);
            }
        }

        // Test invalid color
        $result = $this->instance->calculate_temperature('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_harmony_score method
     */
    public function test_calculate_harmony_score() {
        $harmonious_colors = ['#ff0000', '#00ff00', '#0000ff'];
        $clashing_colors = ['#ff0000', '#ff0100', '#ff0200'];

        $harmonious_score = $this->instance->calculate_harmony_score($harmonious_colors);
        $clashing_score = $this->instance->calculate_harmony_score($clashing_colors);

        $this->assertIsFloat($harmonious_score);
        $this->assertIsFloat($clashing_score);
        $this->assertGreaterThan($clashing_score, $harmonious_score);

        // Test invalid input
        $result = $this->instance->calculate_harmony_score(['#ff0000']);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_complexity method
     */
    public function test_calculate_complexity() {
        $colors = [
            '#000000' => 'simple',
            '#ff00ff' => 'complex'
        ];

        foreach ($colors as $color => $type) {
            $result = $this->instance->calculate_complexity($color);
            $this->assertIsFloat($result);
            $this->assertGreaterThanOrEqual(0, $result);
            $this->assertLessThanOrEqual(1, $result);

            if ($type === 'complex') {
                $this->assertGreaterThan(0.5, $result);
            } else {
                $this->assertLessThan(0.5, $result);
            }
        }

        // Test invalid color
        $result = $this->instance->calculate_complexity('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_dominance method
     */
    public function test_calculate_dominance() {
        $palette = ['#ff0000', '#00ff00', '#0000ff'];

        foreach ($palette as $color) {
            $result = $this->instance->calculate_dominance($color, $palette);
            $this->assertIsFloat($result);
            $this->assertGreaterThanOrEqual(0, $result);
            $this->assertLessThanOrEqual(1, $result);
        }

        // Test color not in palette
        $result = $this->instance->calculate_dominance('#ffffff', $palette);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_balance method
     */
    public function test_calculate_balance() {
        $colors = ['#ff0000', '#00ff00', '#0000ff'];

        $result = $this->instance->calculate_balance($colors);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('rgb_balance', $result);
        $this->assertArrayHasKey('hsl_balance', $result);

        // Test RGB balance
        $this->assertArrayHasKey('r', $result['rgb_balance']);
        $this->assertArrayHasKey('g', $result['rgb_balance']);
        $this->assertArrayHasKey('b', $result['rgb_balance']);

        // Test HSL balance
        $this->assertArrayHasKey('h', $result['hsl_balance']);
        $this->assertArrayHasKey('s', $result['hsl_balance']);
        $this->assertArrayHasKey('l', $result['hsl_balance']);

        // Test invalid color
        $result = $this->instance->calculate_balance(['invalid']);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_weight method
     */
    public function test_calculate_weight() {
        $colors = [
            '#000000' => 'heavy',
            '#ffffff' => 'light'
        ];

        foreach ($colors as $color => $type) {
            $result = $this->instance->calculate_weight($color);
            $this->assertIsFloat($result);
            $this->assertGreaterThanOrEqual(0, $result);
            $this->assertLessThanOrEqual(1, $result);

            if ($type === 'heavy') {
                $this->assertGreaterThan(0.5, $result);
            } else {
                $this->assertLessThan(0.5, $result);
            }
        }

        // Test invalid color
        $result = $this->instance->calculate_weight('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test calculate_energy method
     */
    public function test_calculate_energy() {
        $colors = [
            '#ff0000' => 'high',
            '#808080' => 'low'
        ];

        foreach ($colors as $color => $type) {
            $result = $this->instance->calculate_energy($color);
            $this->assertIsFloat($result);
            $this->assertGreaterThanOrEqual(0, $result);
            $this->assertLessThanOrEqual(1, $result);

            if ($type === 'high') {
                $this->assertGreaterThan(0.5, $result);
            } else {
                $this->assertLessThan(0.5, $result);
            }
        }

        // Test invalid color
        $result = $this->instance->calculate_energy('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test get_contrast_ratio method
     */
    public function test_get_contrast_ratio() {
        $pairs = [
            ['#000000', '#ffffff', 21],
            ['#000000', '#000000', 1]
        ];

        foreach ($pairs as [$color1, $color2, $expected]) {
            $result = $this->instance->get_contrast_ratio($color1, $color2);
            $this->assertIsFloat($result);
            $this->assertEqualsWithDelta($expected, $result, 0.1);
        }

        // Test invalid color
        $result = $this->instance->get_contrast_ratio('invalid', '#000000');
        $this->assertInstanceOf(WP_Error::class, $result);
    }
}
