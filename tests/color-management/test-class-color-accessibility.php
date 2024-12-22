<?php
/**
 * Tests for Color_Accessibility class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Accessibility;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Color_Management\Color_Metrics;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;
use WP_UnitTestCase;
use Mockery;

/**
 * Class Test_Color_Accessibility
 */
class Test_Color_Accessibility extends WP_UnitTestCase {
    /**
     * Test instance
     *
     * @var Color_Accessibility
     */
    private $instance;

    /**
     * Mock color utility
     *
     * @var Color_Utility|Mockery\MockInterface
     */
    private $color_util_mock;

    /**
     * Mock color metrics
     *
     * @var Color_Metrics|Mockery\MockInterface
     */
    private $color_metrics_mock;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();

        // Create mocks
        $this->color_util_mock = Mockery::mock('GL_Color_Palette_Generator\Color_Management\Color_Utility');
        $this->color_metrics_mock = Mockery::mock('GL_Color_Palette_Generator\Color_Management\Color_Metrics');

        // Create instance
        $this->instance = new Color_Accessibility();
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test get_contrast_ratio method
     */
    public function test_get_contrast_ratio() {
        $pairs = [
            ['#000000', '#ffffff', 21],
            ['#000000', '#000000', 1],
            ['#ffffff', '#ffffff', 1]
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

    /**
     * Test meets_wcag_contrast method
     */
    public function test_meets_wcag_contrast() {
        $test_cases = [
            // AA level, normal text
            ['#000000', '#ffffff', 'AA', 'normal', true],
            ['#777777', '#ffffff', 'AA', 'normal', false],

            // AA level, large text
            ['#666666', '#ffffff', 'AA', 'large', true],
            ['#888888', '#ffffff', 'AA', 'large', false],

            // AAA level
            ['#000000', '#ffffff', 'AAA', 'normal', true],
            ['#555555', '#ffffff', 'AAA', 'normal', false]
        ];

        foreach ($test_cases as [$fg, $bg, $level, $size, $expected]) {
            $result = $this->instance->meets_wcag_contrast($fg, $bg, $level, $size);
            $this->assertIsBool($result);
            $this->assertEquals($expected, $result);
        }

        // Test invalid color
        $result = $this->instance->meets_wcag_contrast('invalid', '#000000');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test is_readable method
     */
    public function test_is_readable() {
        $test_cases = [
            ['#000000', '#ffffff', [], true],
            ['#777777', '#888888', [], false],
            ['#000000', '#ffffff', ['level' => 'AAA'], true],
            ['#000000', '#cccccc', ['level' => 'AAA'], false]
        ];

        foreach ($test_cases as [$color, $bg, $options, $expected]) {
            $result = $this->instance->is_readable($color, $bg, $options);
            $this->assertIsBool($result);
            $this->assertEquals($expected, $result);
        }

        // Test invalid color
        $result = $this->instance->is_readable('invalid', '#000000');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test get_accessible_combinations method
     */
    public function test_get_accessible_combinations() {
        $base_color = '#000000';
        $result = $this->instance->get_accessible_combinations($base_color);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        foreach ($result as $combination) {
            $this->assertArrayHasKey('color', $combination);
            $this->assertArrayHasKey('contrast_ratio', $combination);
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $combination['color']);
            $this->assertGreaterThanOrEqual(Color_Constants::WCAG_CONTRAST_MIN, $combination['contrast_ratio']);
        }

        // Test invalid color
        $this->assertInstanceOf(WP_Error::class, $this->instance->get_accessible_combinations('invalid'));
    }

    /**
     * Test check_colorblind_friendly method
     */
    public function test_check_colorblind_friendly() {
        $colors = ['#ff0000', '#00ff00', '#0000ff'];
        $result = $this->instance->check_colorblind_friendly($colors);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('protanopia', $result);
        $this->assertArrayHasKey('deuteranopia', $result);
        $this->assertArrayHasKey('tritanopia', $result);

        foreach ($result as $type => $is_friendly) {
            $this->assertIsBool($is_friendly);
        }

        // Test invalid color
        $result = $this->instance->check_colorblind_friendly(['invalid']);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test simulate_colorblind_vision method
     */
    public function test_simulate_colorblind_vision() {
        $colors = ['#ff0000', '#00ff00', '#0000ff'];
        $types = ['protanopia', 'deuteranopia', 'tritanopia'];

        foreach ($types as $type) {
            $result = $this->instance->simulate_colorblind_vision($colors, $type);
            $this->assertIsArray($result);
            $this->assertCount(count($colors), $result);

            foreach ($result as $color) {
                $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $color);
            }
        }

        // Test invalid type
        $result = $this->instance->simulate_colorblind_vision($colors, 'invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test get_luminance method
     */
    public function test_get_luminance() {
        $colors = [
            '#000000' => 0,
            '#ffffff' => 1,
            '#808080' => 0.21586
        ];

        foreach ($colors as $color => $expected) {
            $result = $this->instance->get_luminance($color);
            $this->assertIsFloat($result);
            $this->assertEqualsWithDelta($expected, $result, 0.001);
        }

        // Test invalid color
        $result = $this->instance->get_luminance('invalid');
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test suggest_accessible_alternatives method
     */
    public function test_suggest_accessible_alternatives() {
        $color = '#ff0000';
        $background = '#ffffff';
        $result = $this->instance->suggest_accessible_alternatives($color, $background);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        foreach ($result as $alternative) {
            $this->assertArrayHasKey('color', $alternative);
            $this->assertArrayHasKey('contrast_ratio', $alternative);
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $alternative['color']);
            $this->assertGreaterThanOrEqual(Color_Constants::WCAG_CONTRAST_MIN, $alternative['contrast_ratio']);
        }

        // Test invalid color
        $this->assertInstanceOf(WP_Error::class, $this->instance->suggest_accessible_alternatives('invalid', $background));
    }

    /**
     * Test are_colors_distinguishable method
     */
    public function test_are_colors_distinguishable() {
        $test_cases = [
            [['#ff0000', '#00ff00', '#0000ff'], [], true],
            [['#ff0000', '#ff0001', '#ff0002'], [], false],
            [['#ff0000', '#00ff00', '#0000ff'], ['check_colorblind' => true], true],
            [['#ff0000', '#ff8000'], ['check_colorblind' => true], false]
        ];

        foreach ($test_cases as [$colors, $options, $expected]) {
            $result = $this->instance->are_colors_distinguishable($colors, $options);
            $this->assertIsBool($result);
            $this->assertEquals($expected, $result);
        }

        // Test invalid color
        $result = $this->instance->are_colors_distinguishable(['invalid']);
        $this->assertInstanceOf(WP_Error::class, $result);
    }

    /**
     * Test contrast ratios meet WCAG requirements
     */
    public function test_contrast_ratios(): void {
        $color1 = '#ffffff';  // White
        $color2 = '#000000';  // Black

        $this->color_metrics_mock->shouldReceive('get_relative_luminance')
            ->with($color1)
            ->andReturn(1.0);
        $this->color_metrics_mock->shouldReceive('get_relative_luminance')
            ->with($color2)
            ->andReturn(0.0);

        $contrast = $this->instance->calculate_contrast_ratio($color1, $color2);

        // Verify contrast is within recommended range
        $this->assertGreaterThanOrEqual(Color_Constants::WCAG_CONTRAST_MIN, $contrast);
        $this->assertLessThanOrEqual(Color_Constants::CONTRAST_MAX, $contrast);

        // Test WCAG compliance
        $this->assertTrue($this->instance->meets_wcag_aa($contrast));
        $this->assertTrue($this->instance->meets_wcag_aaa($contrast));
    }

    /**
     * Test shade contrast relationships
     */
    public function test_shade_contrast_relationships(): void {
        $base_color = '#4a90e2';  // Base blue color
        $light_shade = '#a8c9f2';  // Light variation
        $dark_shade = '#1a4c8f';   // Dark variation

        $this->color_metrics_mock->shouldReceive('get_relative_luminance')
            ->with($base_color)
            ->andReturn(0.5);
        $this->color_metrics_mock->shouldReceive('get_relative_luminance')
            ->with($light_shade)
            ->andReturn(Color_Constants::LIGHT_MODE_THRESHOLD);
        $this->color_metrics_mock->shouldReceive('get_relative_luminance')
            ->with($dark_shade)
            ->andReturn(Color_Constants::DARK_MODE_THRESHOLD);

        // Test contrast between light and dark shades
        $contrast = $this->instance->calculate_contrast_ratio($light_shade, $dark_shade);

        // Verify contrast meets WCAG requirements
        $this->assertGreaterThanOrEqual(Color_Constants::WCAG_CONTRAST_MIN, $contrast);

        // Verify luminance relationships
        $this->assertGreaterThan(
            $this->color_metrics_mock->get_relative_luminance($dark_shade),
            $this->color_metrics_mock->get_relative_luminance($light_shade)
        );
    }
}
