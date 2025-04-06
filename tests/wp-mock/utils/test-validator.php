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
	public function test_is_valid_hex_color( string $color, bool $expected ): void {
		$result = $this->validator->is_valid_hex_color( $color );
		$this->assertEquals( $expected, $result );
	}

	public function hex_color_provider(): array {
		return array(
			'valid 6 digit'   => array( '#FF0000', true ),
			'valid 3 digit'   => array( '#F00', true ),
			'valid lowercase' => array( '#ff0000', true ),
			'no hash'         => array( 'FF0000', false ),
			'invalid chars'   => array( '#GG0000', false ),
			'too short'       => array( '#FF', false ),
			'too long'        => array( '#FF00000', false ),
			'invalid format'  => array( 'not-a-color', false ),
		);
	}

	/**
	 * @dataProvider rgb_color_provider
	 */
	public function test_is_valid_rgb_color( array $color, bool $expected ): void {
		$result = $this->validator->is_valid_rgb_color( $color );
		$this->assertEquals( $expected, $result );
	}

	public function rgb_color_provider(): array {
		return array(
			'valid color'       => array(
				array(
					'r' => 255,
					'g' => 0,
					'b' => 0,
				),
				true,
			),
			'missing component' => array(
				array(
					'r' => 255,
					'g' => 0,
				),
				false,
			),
			'invalid type'      => array(
				array(
					'r' => '255',
					'g' => 0,
					'b' => 0,
				),
				false,
			),
			'out of range high' => array(
				array(
					'r' => 256,
					'g' => 0,
					'b' => 0,
				),
				false,
			),
			'out of range low'  => array(
				array(
					'r' => -1,
					'g' => 0,
					'b' => 0,
				),
				false,
			),
			'wrong keys'        => array(
				array(
					'red'   => 255,
					'green' => 0,
					'blue'  => 0,
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider hsl_color_provider
	 */
	public function test_is_valid_hsl_color( array $color, bool $expected ): void {
		$result = $this->validator->is_valid_hsl_color( $color );
		$this->assertEquals( $expected, $result );
	}

	public function hsl_color_provider(): array {
		return array(
			'valid color'         => array(
				array(
					'h' => 0,
					's' => 100,
					'l' => 50,
				),
				true,
			),
			'valid decimals'      => array(
				array(
					'h' => 359.9,
					's' => 50.5,
					'l' => 25.5,
				),
				true,
			),
			'missing component'   => array(
				array(
					'h' => 0,
					's' => 100,
				),
				false,
			),
			'hue too high'        => array(
				array(
					'h' => 360,
					's' => 100,
					'l' => 50,
				),
				false,
			),
			'hue too low'         => array(
				array(
					'h' => -1,
					's' => 100,
					'l' => 50,
				),
				false,
			),
			'saturation too high' => array(
				array(
					'h' => 0,
					's' => 101,
					'l' => 50,
				),
				false,
			),
			'lightness too low'   => array(
				array(
					'h' => 0,
					's' => 100,
					'l' => -1,
				),
				false,
			),
			'wrong keys'          => array(
				array(
					'hue'        => 0,
					'saturation' => 100,
					'lightness'  => 50,
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider color_name_provider
	 */
	public function test_is_valid_color_name( string $name, bool $expected ): void {
		$result = $this->validator->is_valid_color_name( $name );
		$this->assertEquals( $expected, $result );
	}

	public function color_name_provider(): array {
		return array(
			'valid name'         => array( 'Deep Blue', true ),
			'valid with numbers' => array( 'Blue2', true ),
			'valid with hyphen'  => array( 'Sky-Blue', true ),
			'too short'          => array( 'Ab', false ),
			'starts with number' => array( '2Blue', false ),
			'special chars'      => array( 'Blue@Sky', false ),
			'too long'           => array( str_repeat( 'a', 33 ), false ),
		);
	}

	/**
	 * @dataProvider tag_provider
	 */
	public function test_is_valid_tag( string $tag, bool $expected ): void {
		$result = $this->validator->is_valid_tag( $tag );
		$this->assertEquals( $expected, $result );
	}

	public function tag_provider(): array {
		return array(
			'valid tag'          => array( 'summer', true ),
			'valid with numbers' => array( 'summer2023', true ),
			'valid with hyphen'  => array( 'summer-colors', true ),
			'too short'          => array( 'a', false ),
			'special chars'      => array( 'summer@colors', false ),
			'spaces'             => array( 'summer colors', false ),
			'too long'           => array( str_repeat( 'a', 33 ), false ),
		);
	}

	/**
	 * @dataProvider palette_name_provider
	 */
	public function test_is_valid_palette_name( string $name, bool $expected ): void {
		$result = $this->validator->is_valid_palette_name( $name );
		$this->assertEquals( $expected, $result );
	}

	public function palette_name_provider(): array {
		return array(
			'valid name'         => array( 'Summer Colors', true ),
			'valid with numbers' => array( 'Summer Colors 2023', true ),
			'valid with hyphen'  => array( 'Summer-Colors', true ),
			'too short'          => array( 'ab', false ),
			'special chars'      => array( 'Summer@Colors', false ),
			'too long'           => array( str_repeat( 'a', 65 ), false ),
		);
	}

	/**
	 * @dataProvider description_provider
	 */
	public function test_is_valid_description( string $description, bool $expected ): void {
		$result = $this->validator->is_valid_description( $description );
		$this->assertEquals( $expected, $result );
	}

	public function description_provider(): array {
		return array(
			'valid description' => array( 'A beautiful summer palette', true ),
			'empty'             => array( '', true ),
			'max length'        => array( str_repeat( 'a', 500 ), true ),
			'too long'          => array( str_repeat( 'a', 501 ), false ),
		);
	}

	/**
	 * @dataProvider theme_provider
	 */
	public function test_is_valid_theme( string $theme, bool $expected ): void {
		$result = $this->validator->is_valid_theme( $theme );
		$this->assertEquals( $expected, $result );
	}

	public function theme_provider(): array {
		return array(
			'valid theme'        => array( 'summer', true ),
			'valid with numbers' => array( 'summer2023', true ),
			'valid with hyphen'  => array( 'summer-theme', true ),
			'too short'          => array( 'a', false ),
			'special chars'      => array( 'summer@theme', false ),
			'spaces'             => array( 'summer theme', false ),
			'too long'           => array( str_repeat( 'a', 33 ), false ),
		);
	}

	/**
	 * @dataProvider provider_provider
	 */
	public function test_is_valid_provider( string $provider, bool $expected ): void {
		$result = $this->validator->is_valid_provider( $provider );
		$this->assertEquals( $expected, $result );
	}

	public function provider_provider(): array {
		return array(
			'valid provider'     => array( 'openai', true ),
			'valid with numbers' => array( 'openai2', true ),
			'valid with hyphen'  => array( 'open-ai', true ),
			'too short'          => array( 'a', false ),
			'special chars'      => array( 'open@ai', false ),
			'spaces'             => array( 'open ai', false ),
			'too long'           => array( str_repeat( 'a', 33 ), false ),
		);
	}
}
