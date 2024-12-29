<?php
/**
 * Test Color Palette Generator
 *
 * @package GL_Color_Palette_Generator
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_UnitTestCase;
use WP_Error;

class Test_Color_Palette_Generator extends WP_UnitTestCase {
    private $generator;

    public function setUp(): void {
        parent::setUp();
        $this->generator = new Color_Palette_Generator();
    }

    public function test_generate_from_prompt_with_valid_input() {
        $context = [
            'business_type' => 'Modern tech startup focused on AI solutions',
            'target_audience' => 'Tech-savvy professionals aged 25-45',
            'desired_mood' => 'Professional, innovative, and trustworthy'
        ];

        $result = $this->generator->generate_from_prompt($context);
        
        $this->assertInstanceOf(Color_Palette::class, $result);
        $this->assertCount(4, $result->get_colors());
        
        // Check if palette description was saved
        $description = get_option('gl_cpg_last_palette_description');
        $this->assertNotEmpty($description);
        $this->assertArrayHasKey('palette_story', $description);
        $this->assertArrayHasKey('colors', $description);
    }

    public function test_generate_from_prompt_with_missing_context() {
        $context = [];
        $result = $this->generator->generate_from_prompt($context);
        
        // Should still work but use fallback generation
        $this->assertInstanceOf(Color_Palette::class, $result);
        $this->assertCount(4, $result->get_colors());
    }

    public function test_verify_wcag_compliance() {
        $palette = new Color_Palette([
            'primary' => Color_Constants::COLOR_PRIMARY_BLUE[0],
            'secondary' => Color_Constants::COLOR_PRIMARY_RED[0],
            'tertiary' => Color_Constants::COLOR_PRIMARY_GREEN[0],
            'accent' => Color_Constants::COLOR_PRIMARY_YELLOW[0]
        ]);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('verify_wcag_compliance');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator, $palette);
        $this->assertIsBool($result);
    }

    public function test_adjust_for_wcag_compliance() {
        $palette = new Color_Palette([
            'primary' => '#000000',    // Pure black
            'secondary' => '#FFFFFF',   // Pure white
            'tertiary' => '#808080',   // Mid gray
            'accent' => '#FFD700'      // Gold
        ]);

        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('adjust_for_wcag_compliance');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator, $palette);
        $this->assertInstanceOf(Color_Palette::class, $result);

        // Check if colors were adjusted
        $colors = $result->get_colors();
        foreach ($colors as $color) {
            $contrast_with_light = $this->generator->get_color_utility()->get_contrast_ratio(
                $color,
                Color_Constants::COLOR_OFF_WHITE
            );
            $contrast_with_dark = $this->generator->get_color_utility()->get_contrast_ratio(
                $color,
                Color_Constants::COLOR_NEAR_BLACK
            );
            
            $this->assertTrue(
                $contrast_with_light >= Color_Constants::WCAG_CONTRAST_MIN ||
                $contrast_with_dark >= Color_Constants::WCAG_CONTRAST_MIN
            );
        }
    }
}
