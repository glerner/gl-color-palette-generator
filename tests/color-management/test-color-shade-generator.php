<?php

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Variation_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use PHPUnit\Framework\TestCase;

/**
 * Test Color_Variation_Generator functionality
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */
class Test_Color_Shade_Generator extends TestCase {
    private $generator;
    private $color_utility;

    public function setUp(): void {
        $this->color_utility = new Color_Utility();
        $this->generator = new Color_Variation_Generator($this->color_utility);
    }

    /**
     * Test generation of tints and shades
     *
     * @dataProvider colorProvider
     */
    public function test_generate_tints_and_shades($color, $expected_count) {
        $variations = $this->generator->generate_tints_and_shades($color);
        
        // Check we have all expected variations
        $this->assertCount($expected_count, $variations);
        $this->assertArrayHasKey('lighter', $variations);
        $this->assertArrayHasKey('light', $variations);
        $this->assertArrayHasKey('base', $variations);
        $this->assertArrayHasKey('dark', $variations);
        $this->assertArrayHasKey('darker', $variations);

        // Check proper spacing between variations
        $lightness_values = array_map(
            fn($color) => $this->color_utility->rgb_to_hsl(
                $this->color_utility->hex_to_rgb($color)
            )['l'],
            $variations
        );

        $this->assertGreaterThan(
            Color_Constants::COLOR_METRICS['lightness']['spacing_min'],
            $lightness_values['lighter'] - $lightness_values['light']
        );
        $this->assertGreaterThan(
            Color_Constants::COLOR_METRICS['lightness']['spacing_min'],
            $lightness_values['light'] - $lightness_values['base']
        );
        $this->assertGreaterThan(
            Color_Constants::COLOR_METRICS['lightness']['spacing_min'],
            $lightness_values['base'] - $lightness_values['dark']
        );
        $this->assertGreaterThan(
            Color_Constants::COLOR_METRICS['lightness']['spacing_min'],
            $lightness_values['dark'] - $lightness_values['darker']
        );
    }

    /**
     * Test contrast color generation
     *
     * @dataProvider contrastColorProvider
     */
    public function test_generate_contrast_color($background, $is_dark_mode, $expected_type) {
        $contrast = $this->generator->generate_contrast_color($background, $is_dark_mode);
        
        // Check proper contrast ratio
        $this->assertTrue($this->generator->check_contrast($background, $contrast));
        
        // Check expected type (off-white or near-black)
        $lightness = $this->color_utility->rgb_to_hsl(
            $this->color_utility->hex_to_rgb($contrast)
        )['l'];

        if ($expected_type === 'off-white') {
            $this->assertGreaterThanOrEqual(Color_Constants::COLOR_METRICS['lightness']['off_white_min'], $lightness);
            $this->assertLessThanOrEqual(Color_Constants::COLOR_METRICS['lightness']['off_white_max'], $lightness);
        } else {
            $this->assertGreaterThanOrEqual(Color_Constants::COLOR_METRICS['lightness']['near_black_min'], $lightness);
            $this->assertLessThanOrEqual(Color_Constants::COLOR_METRICS['lightness']['near_black_max'], $lightness);
        }
    }

    /**
     * Test contrasting variation generation
     *
     * @dataProvider contrastingVariationProvider
     */
    public function test_generate_contrasting_variation($base, $prefer_lighter) {
        $contrast = $this->generator->generate_contrasting_variation($base, $prefer_lighter);
        
        // Check proper contrast ratio
        $this->assertTrue($this->generator->check_contrast($base, $contrast));
        
        // Check direction of contrast
        $base_lightness = $this->color_utility->rgb_to_hsl(
            $this->color_utility->hex_to_rgb($base)
        )['l'];
        $contrast_lightness = $this->color_utility->rgb_to_hsl(
            $this->color_utility->hex_to_rgb($contrast)
        )['l'];

        if ($prefer_lighter) {
            $this->assertGreaterThan($base_lightness, $contrast_lightness);
        } else {
            $this->assertLessThan($base_lightness, $contrast_lightness);
        }
    }

    /**
     * Test invalid color handling
     */
    public function test_invalid_color_handling() {
        $this->assertEmpty($this->generator->generate_tints_and_shades('invalid'));
        $this->assertEmpty($this->generator->generate_tints_and_shades('#12')); // Too short
        $this->assertEmpty($this->generator->generate_tints_and_shades('#1234567')); // Too long
        $this->assertEmpty($this->generator->generate_tints_and_shades('#GHIJKL')); // Invalid chars
    }

    /**
     * Data provider for tints and shades test
     */
    public function colorProvider(): array {
        return [
            ['#FF0000', 5], // Pure red
            ['#00FF00', 5], // Pure green
            ['#0000FF', 5], // Pure blue
            ['#808080', 5], // Mid gray
            ['#FFFFFF', 5], // White
            ['#000000', 5], // Black
        ];
    }

    /**
     * Data provider for contrast color test
     */
    public function contrastColorProvider(): array {
        return [
            ['#FFFFFF', false, 'near-black'],
            ['#000000', true, 'off-white'],
            ['#FF0000', false, 'off-white'],
            ['#00FF00', true, 'near-black'],
            ['#0000FF', false, 'off-white'],
        ];
    }

    /**
     * Data provider for contrasting variation test
     */
    public function contrastingVariationProvider(): array {
        return [
            ['#FF0000', true],
            ['#00FF00', false],
            ['#0000FF', true],
            ['#808080', false],
        ];
    }
}
