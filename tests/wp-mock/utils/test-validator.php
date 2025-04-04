<?php
/**
 * Test Validator Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Utils
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Utils;

use GL_Color_Palette_Generator\Utils\Validator;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;

/**
 * Test class for Validator
 *
 * @covers GL_Color_Palette_Generator\Utils\Validator
 */
class Test_Validator extends WP_Mock_Test_Case {
    private $validator;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->validator = new Validator();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * @dataProvider hex_color_provider
     */
    public function test_is_valid_hex_color(string $color, bool $expected): void {
        $result = $this->validator->is_valid_hex_color($color);
        $this->assertEquals($expected, $result);
    }

    public function hex_color_provider(): array {
        return [
            'valid 6 digit' => ['#FF0000', true],
            'valid 3 digit' => ['#F00', true],
            'valid lowercase' => ['#ff0000', true],
            'no hash' => ['FF0000', false],
            'invalid chars' => ['#GG0000', false],
            'too short' => ['#FF', false],
            'too long' => ['#FF00000', false],
            'invalid format' => ['not-a-color', false],
        ];
    }

    /**
     * @dataProvider rgb_color_provider
     */
    public function test_is_valid_rgb_color(array $color, bool $expected): void {
        $result = $this->validator->is_valid_rgb_color($color);
        $this->assertEquals($expected, $result);
    }

    public function rgb_color_provider(): array {
        return [
            'valid color' => [['r' => 255, 'g' => 0, 'b' => 0], true],
            'missing component' => [['r' => 255, 'g' => 0], false],
            'invalid type' => [['r' => '255', 'g' => 0, 'b' => 0], false],
            'out of range high' => [['r' => 256, 'g' => 0, 'b' => 0], false],
            'out of range low' => [['r' => -1, 'g' => 0, 'b' => 0], false],
            'wrong keys' => [['red' => 255, 'green' => 0, 'blue' => 0], false],
        ];
    }

    /**
     * @dataProvider hsl_color_provider
     */
    public function test_is_valid_hsl_color(array $color, bool $expected): void {
        $result = $this->validator->is_valid_hsl_color($color);
        $this->assertEquals($expected, $result);
    }

    public function hsl_color_provider(): array {
        return [
            'valid color' => [['h' => 0, 's' => 100, 'l' => 50], true],
            'valid decimals' => [['h' => 359.9, 's' => 50.5, 'l' => 25.5], true],
            'missing component' => [['h' => 0, 's' => 100], false],
            'hue too high' => [['h' => 360, 's' => 100, 'l' => 50], false],
            'hue too low' => [['h' => -1, 's' => 100, 'l' => 50], false],
            'saturation too high' => [['h' => 0, 's' => 101, 'l' => 50], false],
            'lightness too low' => [['h' => 0, 's' => 100, 'l' => -1], false],
            'wrong keys' => [['hue' => 0, 'saturation' => 100, 'lightness' => 50], false],
        ];
    }

    /**
     * @dataProvider color_name_provider
     */
    public function test_is_valid_color_name(string $name, bool $expected): void {
        $result = $this->validator->is_valid_color_name($name);
        $this->assertEquals($expected, $result);
    }

    public function color_name_provider(): array {
        return [
            'valid name' => ['Deep Blue', true],
            'valid with numbers' => ['Blue2', true],
            'valid with hyphen' => ['Sky-Blue', true],
            'too short' => ['Ab', false],
            'starts with number' => ['2Blue', false],
            'special chars' => ['Blue@Sky', false],
            'too long' => [str_repeat('a', 33), false],
        ];
    }

    /**
     * @dataProvider tag_provider
     */
    public function test_is_valid_tag(string $tag, bool $expected): void {
        $result = $this->validator->is_valid_tag($tag);
        $this->assertEquals($expected, $result);
    }

    public function tag_provider(): array {
        return [
            'valid tag' => ['summer', true],
            'valid with numbers' => ['summer2023', true],
            'valid with hyphen' => ['summer-colors', true],
            'too short' => ['a', false],
            'special chars' => ['summer@colors', false],
            'spaces' => ['summer colors', false],
            'too long' => [str_repeat('a', 33), false],
        ];
    }

    /**
     * @dataProvider palette_name_provider
     */
    public function test_is_valid_palette_name(string $name, bool $expected): void {
        $result = $this->validator->is_valid_palette_name($name);
        $this->assertEquals($expected, $result);
    }

    public function palette_name_provider(): array {
        return [
            'valid name' => ['Summer Colors', true],
            'valid with numbers' => ['Summer Colors 2023', true],
            'valid with hyphen' => ['Summer-Colors', true],
            'too short' => ['ab', false],
            'special chars' => ['Summer@Colors', false],
            'too long' => [str_repeat('a', 65), false],
        ];
    }

    /**
     * @dataProvider description_provider
     */
    public function test_is_valid_description(string $description, bool $expected): void {
        $result = $this->validator->is_valid_description($description);
        $this->assertEquals($expected, $result);
    }

    public function description_provider(): array {
        return [
            'valid description' => ['A beautiful summer palette', true],
            'empty' => ['', true],
            'max length' => [str_repeat('a', 500), true],
            'too long' => [str_repeat('a', 501), false],
        ];
    }

    /**
     * @dataProvider theme_provider
     */
    public function test_is_valid_theme(string $theme, bool $expected): void {
        $result = $this->validator->is_valid_theme($theme);
        $this->assertEquals($expected, $result);
    }

    public function theme_provider(): array {
        return [
            'valid theme' => ['summer', true],
            'valid with numbers' => ['summer2023', true],
            'valid with hyphen' => ['summer-theme', true],
            'too short' => ['a', false],
            'special chars' => ['summer@theme', false],
            'spaces' => ['summer theme', false],
            'too long' => [str_repeat('a', 33), false],
        ];
    }

    /**
     * @dataProvider provider_provider
     */
    public function test_is_valid_provider(string $provider, bool $expected): void {
        $result = $this->validator->is_valid_provider($provider);
        $this->assertEquals($expected, $result);
    }

    public function provider_provider(): array {
        return [
            'valid provider' => ['openai', true],
            'valid with numbers' => ['openai2', true],
            'valid with hyphen' => ['open-ai', true],
            'too short' => ['a', false],
            'special chars' => ['open@ai', false],
            'spaces' => ['open ai', false],
            'too long' => [str_repeat('a', 33), false],
        ];
    }
}
