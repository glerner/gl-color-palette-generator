<?php
/**
 * Color Palette Converter Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Converter;

/**
 * Test class for Color_Palette_Converter interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\Color_Palette_Converter
 */
class Test_Color_Palette_Converter_Interface extends Unit_Test_Case {
	private $converter;

	public function setUp(): void {
		$this->converter = $this->createMock( Color_Palette_Converter::class );
	}

	public function test_convert_color_space_transforms_colors(): void {
		// Arrange
		$colors  = array( '#FF0000', '#00FF00' );
		$from    = 'rgb';
		$to      = 'hsl';
		$options = array(
			'precision' => 2,
			'gamut'     => 'clip',
		);

		$expected = array(
			'converted' => array(
				array(
					'h' => 0,
					's' => 100,
					'l' => 50,
				),
				array(
					'h' => 120,
					's' => 100,
					'l' => 50,
				),
			),
			'original'  => array( '#FF0000', '#00FF00' ),
			'mapping'   => array(
				'rgb_to_hsl' => array(
					'algorithm' => 'standard',
					'precision' => 2,
				),
			),
			'metadata'  => array(
				'converted_at' => '2024-01-20T12:00:00Z',
				'color_space'  => 'hsl',
			),
		);

		$this->converter
			->expects( $this->once() )
			->method( 'convert_color_space' )
			->with( $colors, $from, $to, $options )
			->willReturn( $expected );

		// Act
		$result = $this->converter->convert_color_space( $colors, $from, $to, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertArrayHasKey( 'original', $result );
		$this->assertArrayHasKey( 'mapping', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_convert_format_transforms_palette(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$format  = 'css';
		$options = array(
			'structure'  => 'variables',
			'formatting' => array( 'prefix' => '--' ),
		);

		$expected = array(
			'converted' => array(
				'--primary: #FF0000;',
				'--secondary: #00FF00;',
			),
			'original'  => $palette,
			'format'    => array(
				'type'   => 'css',
				'syntax' => 'variables',
			),
			'metadata'  => array(
				'converted_at' => '2024-01-20T12:00:00Z',
				'format'       => 'css',
			),
		);

		$this->converter
			->expects( $this->once() )
			->method( 'convert_format' )
			->with( $palette, $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->converter->convert_format( $palette, $format, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertArrayHasKey( 'original', $result );
		$this->assertArrayHasKey( 'format', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_convert_standard_transforms_compliance(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#FFFFFF' ),
		);

		$standard = 'wcag2.1';
		$options  = array(
			'compliance' => array( 'level' => 'AA' ),
			'mapping'    => array( 'text' => 'foreground' ),
		);

		$expected = array(
			'converted'  => array(
				'colors'          => array( '#FF0000', '#FFFFFF' ),
				'contrast_ratios' => array( '21:1' ),
			),
			'compliance' => array(
				'level'  => 'AA',
				'passed' => true,
			),
			'mapping'    => array(
				'text'       => 'foreground',
				'background' => 'background',
			),
			'metadata'   => array(
				'converted_at' => '2024-01-20T12:00:00Z',
				'standard'     => 'wcag2.1',
			),
		);

		$this->converter
			->expects( $this->once() )
			->method( 'convert_standard' )
			->with( $palette, $standard, $options )
			->willReturn( $expected );

		// Act
		$result = $this->converter->convert_standard( $palette, $standard, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertArrayHasKey( 'compliance', $result );
		$this->assertArrayHasKey( 'mapping', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_convert_system_transforms_palette(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$system  = 'sRGB';
		$options = array(
			'mapping'       => array( 'text' => 'foreground' ),
			'compatibility' => array( 'text' => 'foreground' ),
		);

		$expected = array(
			'converted'     => array(
				'colors'        => array( '#FF0000', '#00FF00' ),
				'compatibility' => array( 'text' => 'foreground' ),
			),
			'compatibility' => array(
				'text' => 'foreground',
			),
			'mapping'       => array(
				'text' => 'foreground',
			),
			'metadata'      => array(
				'converted_at' => '2024-01-20T12:00:00Z',
				'system'       => 'sRGB',
			),
		);

		$this->converter
			->expects( $this->once() )
			->method( 'convert_system' )
			->with( $palette, $system, $options )
			->willReturn( $expected );

		// Act
		$result = $this->converter->convert_system( $palette, $system, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertArrayHasKey( 'compatibility', $result );
		$this->assertArrayHasKey( 'mapping', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_convert_color_transforms_single_color(): void {
		// Arrange
		$color       = '#FF0000';
		$target_type = 'space';
		$target      = 'HSL';
		$options     = array(
			'source'    => 'RGB',
			'precision' => 2,
		);

		$expected = array(
			'color'      => 'hsl(0, 100%, 50%)',
			'original'   => array(
				'value' => '#FF0000',
				'space' => 'RGB',
			),
			'conversion' => array(
				'type'   => 'space',
				'method' => 'rgb_to_hsl',
			),
			'metadata'   => array(
				'converted_at'      => '2024-01-20T12:00:00Z',
				'precision_applied' => 2,
			),
		);

		$this->converter
			->expects( $this->once() )
			->method( 'convert_color' )
			->with( $color, $target_type, $target, $options )
			->willReturn( $expected );

		// Act
		$result = $this->converter->convert_color( $color, $target_type, $target, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'color', $result );
		$this->assertArrayHasKey( 'original', $result );
		$this->assertArrayHasKey( 'conversion', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_validate_conversion_checks_conversion_data(): void {
		// Arrange
		$conversion = array(
			'color'  => 'hsl(0, 100%, 50%)',
			'source' => '#FF0000',
			'space'  => 'HSL',
		);

		$rules = array(
			'ranges' => array(
				'hue'        => array( 0, 360 ),
				'saturation' => array( 0, 100 ),
				'lightness'  => array( 0, 100 ),
			),
		);

		$expected = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(),
			'metadata' => array(
				'validated_at'  => '2024-01-20T12:00:00Z',
				'rules_applied' => array( 'ranges' ),
			),
		);

		$this->converter
			->expects( $this->once() )
			->method( 'validate_conversion' )
			->with( $conversion, $rules )
			->willReturn( $expected );

		// Act
		$result = $this->converter->validate_conversion( $conversion, $rules );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'valid', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertArrayHasKey( 'warnings', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['valid'] );
	}

	/**
	 * @dataProvider invalidColorSpaceProvider
	 */
	public function test_convert_color_space_validates_target_space( string $target_space ): void {
		$palette = array( 'colors' => array( '#FF0000' ) );

		$this->converter
			->expects( $this->once() )
			->method( 'convert_color_space' )
			->with( $palette, $target_space )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->converter->convert_color_space( $palette, $target_space );
	}

	public function invalidColorSpaceProvider(): array {
		return array(
			'empty_space'     => array( '' ),
			'invalid_space'   => array( 'INVALID' ),
			'lowercase_space' => array( 'rgb' ),
			'numeric_space'   => array( '123' ),
		);
	}

	/**
	 * @dataProvider invalidColorFormatProvider
	 */
	public function test_convert_format_validates_target_format( string $target_format ): void {
		$palette = array( 'colors' => array( '#FF0000' ) );

		$this->converter
			->expects( $this->once() )
			->method( 'convert_format' )
			->with( $palette, $target_format )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->converter->convert_format( $palette, $target_format );
	}

	public function invalidColorFormatProvider(): array {
		return array(
			'empty_format'   => array( '' ),
			'invalid_format' => array( 'invalid' ),
			'unknown_format' => array( 'pantone' ),
			'numeric_format' => array( '123' ),
		);
	}
}
