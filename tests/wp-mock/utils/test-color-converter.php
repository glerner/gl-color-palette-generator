<?php
/**
 * Test Color Converter Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Utils
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Utils;

use GL_Color_Palette_Generator\Utils\Color_Converter;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;

/**
 * Test class for Color_Converter
 *
 * @covers GL_Color_Palette_Generator\Utils\Color_Converter
 */
class Test_Color_Converter extends WP_Mock_Test_Case {
    private $converter;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->converter = new Color_Converter();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * @dataProvider hex_to_rgb_provider
     */
    public function test_hex_to_rgb(string $hex, array $expected): void {
        $result = $this->converter->hex_to_rgb($hex);
        $this->assertEquals($expected, $result);
    }

    public function hex_to_rgb_provider(): array {
        return [
            'red' => ['#FF0000', ['r' => 255, 'g' => 0, 'b' => 0]],
            'green' => ['#00FF00', ['r' => 0, 'g' => 255, 'b' => 0]],
            'blue' => ['#0000FF', ['r' => 0, 'g' => 0, 'b' => 255]],
            'white' => ['#FFFFFF', ['r' => 255, 'g' => 255, 'b' => 255]],
            'black' => ['#000000', ['r' => 0, 'g' => 0, 'b' => 0]],
            'short red' => ['#F00', ['r' => 255, 'g' => 0, 'b' => 0]],
        ];
    }

    public function test_hex_to_rgb_invalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->hex_to_rgb('invalid');
    }

    /**
     * @dataProvider rgb_to_hex_provider
     */
    public function test_rgb_to_hex(array $rgb, string $expected): void {
        $result = $this->converter->rgb_to_hex($rgb);
        $this->assertEquals($expected, $result);
    }

    public function rgb_to_hex_provider(): array {
        return [
            'red' => [['r' => 255, 'g' => 0, 'b' => 0], '#FF0000'],
            'green' => [['r' => 0, 'g' => 255, 'b' => 0], '#00FF00'],
            'blue' => [['r' => 0, 'g' => 0, 'b' => 255], '#0000FF'],
            'white' => [['r' => 255, 'g' => 255, 'b' => 255], '#FFFFFF'],
            'black' => [['r' => 0, 'g' => 0, 'b' => 0], '#000000'],
        ];
    }

    public function test_rgb_to_hex_invalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->rgb_to_hex(['r' => 256, 'g' => 0, 'b' => 0]);
    }

    /**
     * @dataProvider rgb_to_hsl_provider
     */
    public function test_rgb_to_hsl(array $rgb, array $expected): void {
        $result = $this->converter->rgb_to_hsl($rgb);
        $this->assertEquals($expected, $result);
    }

    public function rgb_to_hsl_provider(): array {
        return [
            'red' => [
                ['r' => 255, 'g' => 0, 'b' => 0],
                ['h' => 0, 's' => 100, 'l' => 50]
            ],
            'green' => [
                ['r' => 0, 'g' => 255, 'b' => 0],
                ['h' => 120, 's' => 100, 'l' => 50]
            ],
            'blue' => [
                ['r' => 0, 'g' => 0, 'b' => 255],
                ['h' => 240, 's' => 100, 'l' => 50]
            ],
            'white' => [
                ['r' => 255, 'g' => 255, 'b' => 255],
                ['h' => 0, 's' => 0, 'l' => 100]
            ],
            'black' => [
                ['r' => 0, 'g' => 0, 'b' => 0],
                ['h' => 0, 's' => 0, 'l' => 0]
            ],
        ];
    }

    /**
     * @dataProvider hsl_to_rgb_provider
     */
    public function test_hsl_to_rgb(array $hsl, array $expected): void {
        $result = $this->converter->hsl_to_rgb($hsl);
        $this->assertEquals($expected, $result);
    }

    public function hsl_to_rgb_provider(): array {
        return [
            'red' => [
                ['h' => 0, 's' => 100, 'l' => 50],
                ['r' => 255, 'g' => 0, 'b' => 0]
            ],
            'green' => [
                ['h' => 120, 's' => 100, 'l' => 50],
                ['r' => 0, 'g' => 255, 'b' => 0]
            ],
            'blue' => [
                ['h' => 240, 's' => 100, 'l' => 50],
                ['r' => 0, 'g' => 0, 'b' => 255]
            ],
            'white' => [
                ['h' => 0, 's' => 0, 'l' => 100],
                ['r' => 255, 'g' => 255, 'b' => 255]
            ],
            'black' => [
                ['h' => 0, 's' => 0, 'l' => 0],
                ['r' => 0, 'g' => 0, 'b' => 0]
            ],
        ];
    }

    /**
     * @dataProvider adjust_brightness_provider
     */
    public function test_adjust_brightness(string $hex, float $factor, string $expected): void {
        $result = $this->converter->adjust_brightness($hex, $factor);
        $this->assertEquals($expected, $result);
    }

    public function adjust_brightness_provider(): array {
        return [
            'lighten red' => ['#FF0000', 0.2, '#FF3333'],
            'darken red' => ['#FF0000', -0.2, '#CC0000'],
            'no change' => ['#FF0000', 0, '#FF0000'],
        ];
    }

    public function test_adjust_brightness_invalid_factor(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->adjust_brightness('#FF0000', 2);
    }

    /**
     * @dataProvider contrast_ratio_provider
     */
    public function test_get_contrast_ratio(string $hex1, string $hex2, float $expected): void {
        $result = $this->converter->get_contrast_ratio($hex1, $hex2);
        $this->assertEqualsWithDelta($expected, $result, 0.01);
    }

    public function contrast_ratio_provider(): array {
        return [
            'black-white' => ['#000000', '#FFFFFF', 21],
            'red-white' => ['#FF0000', '#FFFFFF', 4],
            'same color' => ['#FF0000', '#FF0000', 1],
        ];
    }

    /**
     * @dataProvider complementary_provider
     */
    public function test_get_complementary(string $hex, string $expected): void {
        $result = $this->converter->get_complementary($hex);
        $this->assertEquals($expected, $result);
    }

    public function complementary_provider(): array {
        return [
            'red' => ['#FF0000', '#00FFFF'],
            'green' => ['#00FF00', '#FF00FF'],
            'blue' => ['#0000FF', '#FFFF00'],
        ];
    }

    /**
     * @dataProvider analogous_provider
     */
    public function test_get_analogous(string $hex, int $count, int $angle, array $expected): void {
        $result = $this->converter->get_analogous($hex, $count, $angle);
        $this->assertEquals($expected, $result);
    }

    public function analogous_provider(): array {
        return [
            'red default' => [
                '#FF0000',
                2,
                30,
                ['#FF0000', '#FF4D00']
            ],
            'blue custom' => [
                '#0000FF',
                3,
                45,
                ['#0000FF', '#4D00FF', '#0066FF']
            ],
        ];
    }

    public function test_get_analogous_invalid_count(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->get_analogous('#FF0000', 6);
    }

    public function test_get_analogous_invalid_angle(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->get_analogous('#FF0000', 2, 50);
    }
}
