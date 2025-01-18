<?php
/**
 * Tests for Color_Validator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Core;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Validation\Color_Validator;
use GL_Color_Palette_Generator\Core\Logger;

/**
 * Class Test_Color_Validator
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Validation\Color_Validator
 */
class Test_Color_Validator extends Unit_Test_Case {
    /**
     * Validator instance
     *
     * @var Color_Validator
     */
    private $validator;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        $logger = $this->createMock(Logger::class);
        $this->validator = new Color_Validator($logger);
    }

    /**
     * Test hex color validation
     *
     * @covers ::is_valid
     * @dataProvider provide_hex_colors
     */
    public function test_hex_validation($color, $expected) {
        $this->assertEquals($expected, $this->validator->is_valid($color, 'hex'));
    }

    /**
     * Data provider for hex validation
     */
    public function provide_hex_colors() {
        return [
            ['#FF0000', true],
            ['#ff0000', true],
            ['#F00', true],
            ['#f00', true],
            ['FF0000', true],
            ['#XYZ', false],
            ['#FF00', false],
            ['#FF000000', false],
        ];
    }

    /**
     * Test RGB color validation
     *
     * @covers ::is_valid
     * @dataProvider provide_rgb_colors
     */
    public function test_rgb_validation($color, $expected) {
        $this->assertEquals($expected, $this->validator->is_valid($color, 'rgb'));
    }

    /**
     * Data provider for RGB validation
     */
    public function provide_rgb_colors() {
        return [
            ['rgb(255, 0, 0)', true],
            ['rgb(0, 255, 0)', true],
            ['rgb(0,0,0)', true],
            ['rgb(300, 0, 0)', true], // Note: This is technically valid syntax but not a valid color
            ['rgb(255,255,255)', true],
            ['rgb(255 0 0)', false],
            ['rgb(255, 0)', false],
            ['rgb(255, 0, 0, 0.5)', false],
        ];
    }

    /**
     * Test RGBA color validation
     *
     * @covers ::is_valid
     * @dataProvider provide_rgba_colors
     */
    public function test_rgba_validation($color, $expected) {
        $this->assertEquals($expected, $this->validator->is_valid($color, 'rgba'));
    }

    /**
     * Data provider for RGBA validation
     */
    public function provide_rgba_colors() {
        return [
            ['rgba(255, 0, 0, 1)', true],
            ['rgba(0, 255, 0, 0.5)', true],
            ['rgba(0,0,0,0)', true],
            ['rgba(255,255,255,0.5)', true],
            ['rgba(255 0 0 1)', false],
            ['rgba(255, 0, 0)', false],
            ['rgba(255, 0, 0, 2)', true], // Note: This is technically valid syntax but not a valid color
        ];
    }

    /**
     * Test color format detection
     *
     * @covers ::get_format
     * @dataProvider provide_color_formats
     */
    public function test_get_format($color, $expected_format) {
        $this->assertEquals($expected_format, $this->validator->get_format($color));
    }

    /**
     * Data provider for format detection
     */
    public function provide_color_formats() {
        return [
            ['#FF0000', 'hex'],
            ['rgb(255, 0, 0)', 'rgb'],
            ['rgba(255, 0, 0, 1)', 'rgba'],
            ['hsl(0, 100%, 50%)', 'hsl'],
            ['hsla(0, 100%, 50%, 1)', 'hsla'],
            ['invalid-color', null],
        ];
    }

    /**
     * Test hex color conversion
     *
     * @covers ::to_hex
     * @dataProvider provide_color_conversions
     */
    public function test_to_hex($color, $expected_hex) {
        $this->assertEquals($expected_hex, $this->validator->to_hex($color));
    }

    /**
     * Data provider for color conversions
     */
    public function provide_color_conversions() {
        return [
            ['#ff0000', '#FF0000'],
            ['#f00', '#FF0000'],
            ['rgb(255, 0, 0)', '#FF0000'],
            ['rgba(255, 0, 0, 1)', '#FF0000'],
            ['hsl(0, 100%, 50%)', '#FF0000'],
            ['hsla(0, 100%, 50%, 1)', '#FF0000'],
            ['invalid-color', null],
        ];
    }

    /**
     * Test RGB to hex conversion edge cases
     *
     * @covers ::rgb_to_hex
     */
    public function test_rgb_to_hex_edge_cases() {
        $this->assertEquals('#000000', $this->validator->to_hex('rgb(0, 0, 0)'));
        $this->assertEquals('#FFFFFF', $this->validator->to_hex('rgb(255, 255, 255)'));
        $this->assertEquals('#010101', $this->validator->to_hex('rgb(1, 1, 1)'));
    }

    /**
     * Test HSL to hex conversion edge cases
     *
     * @covers ::hsl_to_hex
     */
    public function test_hsl_to_hex_edge_cases() {
        $this->assertEquals('#000000', $this->validator->to_hex('hsl(0, 0%, 0%)'));
        $this->assertEquals('#FFFFFF', $this->validator->to_hex('hsl(0, 0%, 100%)'));
        $this->assertEquals('#808080', $this->validator->to_hex('hsl(0, 0%, 50%)'));
    }
}
