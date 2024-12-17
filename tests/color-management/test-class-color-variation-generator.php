<?php
/**
 * Tests for Color_Variation_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Variation_Generator;
use GL_Color_Palette_Generator\Color_Management\AccessibilityChecker;
use WP_UnitTestCase;
use Mockery;

/**
 * Class Test_Color_Variation_Generator
 */
class Test_Color_Variation_Generator extends WP_UnitTestCase {
    /**
     * Test instance
     *
     * @var Color_Variation_Generator
     */
    private $instance;

    /**
     * Mock accessibility checker
     *
     * @var AccessibilityChecker|Mockery\MockInterface
     */
    private $accessibility_mock;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->accessibility_mock = Mockery::mock(AccessibilityChecker::class);
        $this->instance = new Color_Variation_Generator($this->accessibility_mock);
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test generate_tints_and_shades with default options
     */
    public function test_generate_tints_and_shades_default(): void {
        $base_color = '#FF0000';
        
        $this->accessibility_mock
            ->shouldReceive('meets_contrast_requirements')
            ->andReturn(true)
            ->byDefault();

        $result = $this->instance->generate_tints_and_shades($base_color);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertEquals($base_color, $result['original']);
        
        $variations = $result['variations'];
        $this->assertArrayHasKey('lighter', $variations);
        $this->assertArrayHasKey('light', $variations);
        $this->assertArrayHasKey('dark', $variations);
        $this->assertArrayHasKey('darker', $variations);
        $this->assertArrayHasKey('base', $variations);
    }

    /**
     * Test generate_tints_and_shades with custom steps
     */
    public function test_generate_tints_and_shades_custom_steps(): void {
        $base_color = '#00FF00';
        $custom_steps = [
            'subtle' => [10, 30, 5],
            'strong' => [-10, -30, -5]
        ];
        
        $this->accessibility_mock
            ->shouldReceive('meets_contrast_requirements')
            ->andReturn(true)
            ->byDefault();

        $result = $this->instance->generate_tints_and_shades($base_color, [
            'custom_steps' => $custom_steps
        ]);

        $variations = $result['variations'];
        $this->assertArrayHasKey('subtle', $variations);
        $this->assertArrayHasKey('strong', $variations);
    }

    /**
     * Test generate_tints_and_shades without base color
     */
    public function test_generate_tints_and_shades_no_base(): void {
        $base_color = '#0000FF';
        
        $this->accessibility_mock
            ->shouldReceive('meets_contrast_requirements')
            ->andReturn(true)
            ->byDefault();

        $result = $this->instance->generate_tints_and_shades($base_color, [
            'include_base' => false
        ]);

        $this->assertArrayNotHasKey('base', $result['variations']);
    }

    /**
     * Test generate_tints_and_shades with failing contrast
     */
    public function test_generate_tints_and_shades_contrast_fail(): void {
        $base_color = '#808080';
        
        $this->accessibility_mock
            ->shouldReceive('meets_contrast_requirements')
            ->andReturn(false)
            ->times(5) // First 5 attempts fail
            ->andReturn(true)
            ->byDefault();

        $result = $this->instance->generate_tints_and_shades($base_color);
        
        // Should still generate variations but might skip some that don't meet contrast
        $this->assertIsArray($result['variations']);
    }

    /**
     * Test meets_contrast_requirements
     */
    public function test_meets_contrast_requirements(): void {
        $color = '#FFFFFF';
        
        $this->accessibility_mock
            ->shouldReceive('meets_contrast_requirements')
            ->with($color, Mockery::type('array'))
            ->andReturn(true)
            ->once();

        $result = $this->instance->meets_contrast_requirements($color);
        $this->assertTrue($result);
    }

    /**
     * Test invalid color input
     */
    public function test_invalid_color_input(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->instance->generate_tints_and_shades('invalid-color');
    }

    /**
     * Test color brightness adjustment
     */
    public function test_adjust_brightness(): void {
        $base_color = '#FF0000';
        
        $this->accessibility_mock
            ->shouldReceive('meets_contrast_requirements')
            ->andReturn(true)
            ->byDefault();

        $result = $this->instance->generate_tints_and_shades($base_color, [
            'custom_steps' => [
                'test' => [50, 50, 5] // Single step of 50% lighter
            ]
        ]);

        $variations = $result['variations'];
        $this->assertArrayHasKey('test', $variations);
        
        // Convert hex to RGB and check if brightness was increased
        $hex = ltrim($variations['test'], '#');
        list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
        $this->assertGreaterThan(255, $r + $g + $b); // Sum should be higher than original red (255,0,0)
    }
}
