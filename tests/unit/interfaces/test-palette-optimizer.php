<?php
/**
 * Palette Optimizer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\PaletteOptimizer;

class Test_PaletteOptimizer extends Unit_Test_Case {
	private $optimizer;

	public function setUp(): void {
		$this->optimizer = $this->createMock( PaletteOptimizer::class );
	}

	public function test_optimize_palette_returns_array(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$criteria = array(
			'contrast_minimum'     => 4.5,
			'harmony_weight'       => 0.7,
			'accessibility_weight' => 0.3,
		);
		$expected = array(
			'optimized_colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
			'score'            => 0.92,
			'improvements'     => array(
				'contrast'      => 0.15,
				'harmony'       => 0.08,
				'accessibility' => 0.12,
			),
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_palette' )
			->with( $colors, $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->optimize_palette( $colors, $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'optimized_colors', $result );
		$this->assertArrayHasKey( 'score', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_optimization_suggestions_returns_array(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$expected = array(
			'suggestions'  => array(
				array(
					'type'              => 'contrast',
					'description'       => 'Increase contrast between red and green',
					'current_value'     => 2.5,
					'target_value'      => 4.5,
					'suggested_changes' => array(
						'#FF0000' => '#FF1111',
						'#00FF00' => '#00EE00',
					),
				),
			),
			'priority'     => 'high',
			'impact_score' => 0.8,
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'get_optimization_suggestions' )
			->with( $colors )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->get_optimization_suggestions( $colors );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'priority', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_apply_optimization_returns_array(): void {
		// Arrange
		$colors            = array( '#FF0000', '#00FF00', '#0000FF' );
		$optimization_type = 'contrast';
		$params            = array( 'target_ratio' => 4.5 );
		$expected          = array(
			'optimized_colors' => array( '#FF1111', '#00EE00', '#0000FF' ),
			'changes'          => array(
				'#FF0000' => '#FF1111',
				'#00FF00' => '#00EE00',
			),
			'improvement'      => 0.15,
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'apply_optimization' )
			->with( $colors, $optimization_type, $params )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->apply_optimization( $colors, $optimization_type, $params );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'optimized_colors', $result );
		$this->assertArrayHasKey( 'changes', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_optimization_score_returns_float(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$criteria = array(
			'contrast_weight'      => 0.5,
			'harmony_weight'       => 0.3,
			'accessibility_weight' => 0.2,
		);
		$expected = 0.85;

		$this->optimizer
			->expects( $this->once() )
			->method( 'get_optimization_score' )
			->with( $colors, $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->get_optimization_score( $colors, $criteria );

		// Assert
		$this->assertIsFloat( $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidColorsProvider
	 */
	public function test_optimize_palette_throws_exception_for_invalid_colors( $colors ): void {
		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_palette' )
			->with( $colors, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->optimizer->optimize_palette( $colors, array() );
	}

	/**
	 * @dataProvider invalidCriteriaProvider
	 */
	public function test_optimize_palette_throws_exception_for_invalid_criteria( $criteria ): void {
		$colors = array( '#FF0000', '#00FF00', '#0000FF' );

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_palette' )
			->with( $colors, $criteria )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->optimizer->optimize_palette( $colors, $criteria );
	}

	/**
	 * @dataProvider invalidOptimizationTypeProvider
	 */
	public function test_apply_optimization_throws_exception_for_invalid_type( $type ): void {
		$colors = array( '#FF0000', '#00FF00', '#0000FF' );

		$this->optimizer
			->expects( $this->once() )
			->method( 'apply_optimization' )
			->with( $colors, $type, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->optimizer->apply_optimization( $colors, $type, array() );
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

	public function invalidCriteriaProvider(): array {
		return array(
			'empty array'               => array( array() ),
			'invalid weights'           => array( array( 'contrast_weight' => 2.0 ) ),
			'missing required criteria' => array( array( 'invalid_key' => 0.5 ) ),
			'non-array input'           => array( 'invalid' ),
			'null input'                => array( null ),
		);
	}

	public function invalidOptimizationTypeProvider(): array {
		return array(
			'empty string' => array( '' ),
			'invalid type' => array( 'invalid_type' ),
			'numeric type' => array( 42 ),
			'array type'   => array( array() ),
			'null type'    => array( null ),
		);
	}
}
