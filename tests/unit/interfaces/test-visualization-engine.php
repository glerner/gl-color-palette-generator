<?php
/**
 * Visualization Engine Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\VisualizationEngine;

class Test_VisualizationEngine extends Unit_Test_Case {
	private $engine;

	public function setUp(): void {
		$this->engine = $this->createMock( VisualizationEngine::class );
	}

	public function test_generate_preview_returns_string(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$options  = array(
			'format' => 'svg',
			'size'   => array(
				'width'  => 800,
				'height' => 400,
			),
			'layout' => 'grid',
		);
		$expected = '<svg width="800" height="400">...</svg>';

		$this->engine
			->expects( $this->once() )
			->method( 'generate_preview' )
			->with( $colors, $options )
			->willReturn( $expected );

		// Act
		$result = $this->engine->generate_preview( $colors, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_create_color_wheel_returns_string(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$options  = array(
			'size'              => 400,
			'show_labels'       => true,
			'highlight_primary' => true,
		);
		$expected = '<svg width="400" height="400">...</svg>';

		$this->engine
			->expects( $this->once() )
			->method( 'create_color_wheel' )
			->with( $colors, $options )
			->willReturn( $expected );

		// Act
		$result = $this->engine->create_color_wheel( $colors, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_harmonics_visualization_returns_string(): void {
		// Arrange
		$base_color   = '#FF0000';
		$harmony_type = 'complementary';
		$options      = array(
			'show_connections' => true,
			'include_labels'   => true,
		);
		$expected     = '<svg width="600" height="400">...</svg>';

		$this->engine
			->expects( $this->once() )
			->method( 'generate_harmonics_visualization' )
			->with( $base_color, $harmony_type, $options )
			->willReturn( $expected );

		// Act
		$result = $this->engine->generate_harmonics_visualization( $base_color, $harmony_type, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_create_palette_comparison_returns_string(): void {
		// Arrange
		$palettes = array(
			array( '#FF0000', '#00FF00', '#0000FF' ),
			array( '#FF00FF', '#FFFF00', '#00FFFF' ),
		);
		$options  = array(
			'layout'       => 'horizontal',
			'show_metrics' => true,
		);
		$expected = '<div class="palette-comparison">...</div>';

		$this->engine
			->expects( $this->once() )
			->method( 'create_palette_comparison' )
			->with( $palettes, $options )
			->willReturn( $expected );

		// Act
		$result = $this->engine->create_palette_comparison( $palettes, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_export_visualization_returns_string(): void {
		// Arrange
		$visualization = '<svg>...</svg>';
		$format        = 'png';
		$options       = array(
			'quality'    => 90,
			'background' => 'transparent',
		);
		$expected      = 'data:image/png;base64,...';

		$this->engine
			->expects( $this->once() )
			->method( 'export_visualization' )
			->with( $visualization, $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->engine->export_visualization( $visualization, $format, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidColorsProvider
	 */
	public function test_generate_preview_throws_exception_for_invalid_colors( $colors ): void {
		$this->engine
			->expects( $this->once() )
			->method( 'generate_preview' )
			->with( $colors, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->engine->generate_preview( $colors, array() );
	}

	/**
	 * @dataProvider invalidHarmonyTypeProvider
	 */
	public function test_generate_harmonics_visualization_throws_exception_for_invalid_harmony_type( $harmony_type ): void {
		$this->engine
			->expects( $this->once() )
			->method( 'generate_harmonics_visualization' )
			->with( '#FF0000', $harmony_type, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->engine->generate_harmonics_visualization( '#FF0000', $harmony_type, array() );
	}

	/**
	 * @dataProvider invalidVisualizationProvider
	 */
	public function test_export_visualization_throws_exception_for_invalid_visualization( $visualization ): void {
		$this->engine
			->expects( $this->once() )
			->method( 'export_visualization' )
			->with( $visualization, 'png', array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->engine->export_visualization( $visualization, 'png', array() );
	}

	public function invalidColorsProvider(): array {
		return array(
			'empty array'        => array( array() ),
			'invalid hex colors' => array( array( '#GG0000', '#00FF00' ) ),
			'mixed formats'      => array( array( '#FF0000', 'rgb(0,255,0)' ) ),
			'non-array input'    => array( '#FF0000' ),
			'null input'         => array( null ),
		);
	}

	public function invalidHarmonyTypeProvider(): array {
		return array(
			'empty string' => array( '' ),
			'invalid type' => array( 'invalid' ),
			'numeric type' => array( 42 ),
			'array type'   => array( array() ),
			'null type'    => array( null ),
		);
	}

	public function invalidVisualizationProvider(): array {
		return array(
			'empty string'     => array( '' ),
			'invalid svg'      => array( '<invalid>' ),
			'non-string input' => array( 42 ),
			'array input'      => array( array() ),
			'null input'       => array( null ),
		);
	}
}
