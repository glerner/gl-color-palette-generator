<?php
/**
 * Color Mixer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\ColorMixer;

class Test_ColorMixer extends Unit_Test_Case {
	private $mixer;

	public function setUp(): void {
		$this->mixer = $this->createMock( ColorMixer::class );
	}

	public function test_mix_colors_returns_string(): void {
		// Arrange
		$color1   = '#FF0000';
		$color2   = '#0000FF';
		$ratio    = 0.5;
		$expected = '#800080';

		$this->mixer
			->expects( $this->once() )
			->method( 'mix_colors' )
			->with( $color1, $color2, $ratio )
			->willReturn( $expected );

		// Act
		$result = $this->mixer->mix_colors( $color1, $color2, $ratio );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_blend_colors_returns_array(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$weights  = array( 0.4, 0.3, 0.3 );
		$expected = array(
			'result'     => '#661A66',
			'components' => array(
				'red'   => 102,
				'green' => 26,
				'blue'  => 102,
			),
		);

		$this->mixer
			->expects( $this->once() )
			->method( 'blend_colors' )
			->with( $colors, $weights )
			->willReturn( $expected );

		// Act
		$result = $this->mixer->blend_colors( $colors, $weights );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'result', $result );
		$this->assertArrayHasKey( 'components', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_interpolate_colors_returns_array(): void {
		// Arrange
		$start_color = '#FF0000';
		$end_color   = '#0000FF';
		$steps       = 3;
		$expected    = array(
			'#FF0000',
			'#800080',
			'#0000FF',
		);

		$this->mixer
			->expects( $this->once() )
			->method( 'interpolate_colors' )
			->with( $start_color, $end_color, $steps )
			->willReturn( $expected );

		// Act
		$result = $this->mixer->interpolate_colors( $start_color, $end_color, $steps );

		// Assert
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_gradient_stops_returns_array(): void {
		// Arrange
		$colors      = array( '#FF0000', '#00FF00', '#0000FF' );
		$stop_points = array( 0, 0.5, 1 );
		$expected    = array(
			array(
				'color'    => '#FF0000',
				'position' => 0,
			),
			array(
				'color'    => '#00FF00',
				'position' => 0.5,
			),
			array(
				'color'    => '#0000FF',
				'position' => 1,
			),
		);

		$this->mixer
			->expects( $this->once() )
			->method( 'get_gradient_stops' )
			->with( $colors, $stop_points )
			->willReturn( $expected );

		// Act
		$result = $this->mixer->get_gradient_stops( $colors, $stop_points );

		// Assert
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_adjust_blend_returns_string(): void {
		// Arrange
		$color1      = '#FF0000';
		$color2      = '#0000FF';
		$ratio       = 0.5;
		$adjustments = array(
			'saturation' => 1.2,
			'brightness' => 0.9,
		);
		$expected    = '#990099';

		$this->mixer
			->expects( $this->once() )
			->method( 'adjust_blend' )
			->with( $color1, $color2, $ratio, $adjustments )
			->willReturn( $expected );

		// Act
		$result = $this->mixer->adjust_blend( $color1, $color2, $ratio, $adjustments );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidColorPairProvider
	 */
	public function test_mix_colors_throws_exception_for_invalid_colors( $color1, $color2 ): void {
		$this->mixer
			->expects( $this->once() )
			->method( 'mix_colors' )
			->with( $color1, $color2, 0.5 )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->mixer->mix_colors( $color1, $color2, 0.5 );
	}

	/**
	 * @dataProvider invalidRatioProvider
	 */
	public function test_mix_colors_throws_exception_for_invalid_ratio( $ratio ): void {
		$this->mixer
			->expects( $this->once() )
			->method( 'mix_colors' )
			->with( '#FF0000', '#0000FF', $ratio )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->mixer->mix_colors( '#FF0000', '#0000FF', $ratio );
	}

	/**
	 * @dataProvider invalidColorsAndWeightsProvider
	 */
	public function test_blend_colors_throws_exception_for_invalid_input( $colors, $weights ): void {
		$this->mixer
			->expects( $this->once() )
			->method( 'blend_colors' )
			->with( $colors, $weights )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->mixer->blend_colors( $colors, $weights );
	}

	public function invalidColorPairProvider(): array {
		return array(
			'invalid first color'  => array( 'invalid', '#000000' ),
			'invalid second color' => array( '#FFFFFF', 'invalid' ),
			'both invalid'         => array( 'invalid1', 'invalid2' ),
			'null colors'          => array( null, null ),
			'empty strings'        => array( '', '' ),
		);
	}

	public function invalidRatioProvider(): array {
		return array(
			'negative ratio'       => array( -0.5 ),
			'ratio greater than 1' => array( 1.5 ),
			'non-numeric ratio'    => array( 'invalid' ),
			'null ratio'           => array( null ),
			'zero ratio'           => array( 0 ),
		);
	}

	public function invalidColorsAndWeightsProvider(): array {
		return array(
			'empty arrays'        => array( array(), array() ),
			'mismatched lengths'  => array( array( '#FF0000', '#00FF00' ), array( 0.5 ) ),
			'invalid weights sum' => array( array( '#FF0000', '#00FF00' ), array( 0.3, 0.3 ) ),
			'invalid colors'      => array( array( 'invalid', '#00FF00' ), array( 0.5, 0.5 ) ),
			'null input'          => array( null, null ),
		);
	}
}
