<?php
namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Validator;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_UnitTestCase;

/**
 * Test Color_Validator class
 */
class Test_Color_Validator extends WP_UnitTestCase {
    /** @var Color_Validator */
    private $instance;

    public function set_up() {
        parent::set_up();
        $this->instance = new Color_Validator();
    }

    /**
     * Test validate_colors_for_scheme with valid colors
     */
    public function test_validate_colors_for_scheme_valid() {
        $colors = [
            'primary' => '#ff0000',
            'secondary' => '#00ff00'
        ];

        $result = $this->instance->validate_colors_for_scheme($colors, 'complementary');
        $this->assertTrue($result);
    }

    /**
     * Test validate_colors_for_scheme with invalid scheme
     */
    public function test_validate_colors_for_scheme_invalid_scheme() {
        $colors = [
            'primary' => '#ff0000',
            'secondary' => '#00ff00'
        ];

        $result = $this->instance->validate_colors_for_scheme($colors, 'invalid');
        $this->assertWPError($result);
        $this->assertEquals('invalid_scheme', $result->get_error_code());
    }

    /**
     * Test validate_colors_for_scheme with missing required color
     */
    public function test_validate_colors_for_scheme_missing_required() {
        $colors = [
            'primary' => '#ff0000'
        ];

        $result = $this->instance->validate_colors_for_scheme($colors, 'complementary');
        $this->assertWPError($result);
        $this->assertEquals('missing_color', $result->get_error_code());
    }

    /**
     * Test validate_colors_for_scheme with invalid role
     */
    public function test_validate_colors_for_scheme_invalid_role() {
        $colors = [
            'primary' => '#ff0000',
            'invalid' => '#00ff00'
        ];

        $result = $this->instance->validate_colors_for_scheme($colors, 'complementary');
        $this->assertWPError($result);
        $this->assertEquals('invalid_role', $result->get_error_code());
    }

    /**
     * Test validate_colors_for_scheme with monochromatic scheme
     */
    public function test_validate_colors_for_scheme_monochromatic() {
        $colors = [
            'primary' => '#ff0000'
        ];

        $result = $this->instance->validate_colors_for_scheme($colors, 'monochromatic');
        $this->assertTrue($result);
    }

    /**
     * Test validate_colors_for_scheme with triadic scheme
     */
    public function test_validate_colors_for_scheme_triadic() {
        $colors = [
            'primary' => '#ff0000',
            'secondary' => '#00ff00',
            'tertiary' => '#0000ff'
        ];

        $result = $this->instance->validate_colors_for_scheme($colors, 'triadic');
        $this->assertTrue($result);
    }
}
