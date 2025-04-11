<?php
/**
 * Palette Generator Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\PaletteGenerator;

/**
 * @covers \GL_Color_Palette_Generator\Interfaces\PaletteGenerator
 */
class Test_PaletteGenerator extends Unit_Test_Case {
	private $generator;

	public function setUp(): void {
		$this->generator = $this->createMock( PaletteGenerator::class );
	}

	public function test_generate_palette_returns_valid_structure(): void {
		// Arrange
		$criteria = array(
			'base_color'   => '#FF0000',
			'harmony_type' => 'complementary',
			'color_count'  => 5,
		);

		$expected = array(
			'colors'        => array( '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF' ),
			'color_names'   => array( 'Red', 'Green', 'Blue', 'Yellow', 'Magenta' ),
			'harmony_type'  => 'complementary',
			'relationships' => array(
				'primary'    => '#FF0000',
				'complement' => '#00FF00',
			),
			'metadata'      => array(
				'generation_time'   => '2024-01-20 12:00:00',
				'algorithm_version' => '1.0',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_palette' )
			->with( $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_palette( $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertArrayHasKey( 'color_names', $result );
		$this->assertArrayHasKey( 'harmony_type', $result );
		$this->assertCount( 5, $result['colors'] );
	}

	public function test_validate_palette_returns_validation_results(): void {
		// Arrange
		$palette = array( '#FF0000', '#00FF00', '#0000FF' );
		$rules   = array(
			'check_contrast' => true,
			'accessibility'  => 'AA',
		);

		$expected = array(
			'is_valid'      => true,
			'issues'        => array(),
			'suggestions'   => array( 'Consider adding a neutral color' ),
			'harmony_score' => 0.85,
		);

		$this->generator
			->expects( $this->once() )
			->method( 'validate_palette' )
			->with( $palette, $rules )
			->willReturn( $expected );

		// Act
		$result = $this->generator->validate_palette( $palette, $rules );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'is_valid', $result );
		$this->assertArrayHasKey( 'issues', $result );
		$this->assertArrayHasKey( 'harmony_score', $result );
		$this->assertIsBool( $result['is_valid'] );
		$this->assertIsFloat( $result['harmony_score'] );
	}

	public function test_generate_variations_returns_valid_variations(): void {
		// Arrange
		$palette = array( '#FF0000', '#00FF00', '#0000FF' );
		$options = array(
			'variation_type'  => 'lighter',
			'variation_count' => 3,
		);

		$expected = array(
			'variations'    => array(
				array( '#FF3333', '#33FF33', '#3333FF' ),
				array( '#FF6666', '#66FF66', '#6666FF' ),
				array( '#FF9999', '#99FF99', '#9999FF' ),
			),
			'relationships' => array( 'lighter_shade', 'preserves_harmony' ),
			'scores'        => array( 0.9, 0.85, 0.8 ),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_variations' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_variations( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'variations', $result );
		$this->assertArrayHasKey( 'relationships', $result );
		$this->assertArrayHasKey( 'scores', $result );
		$this->assertCount( 3, $result['variations'] );
	}

	public function test_get_palette_statistics_returns_comprehensive_analysis(): void {
		// Arrange
		$palette  = array( '#FF0000', '#00FF00', '#0000FF' );
		$expected = array(
			'color_distribution'   => array(
				'hues'        => array(
					'red'   => 33.33,
					'green' => 33.33,
					'blue'  => 33.33,
				),
				'saturations' => array( 'high' => 100 ),
			),
			'harmony_analysis'     => array(
				'type'          => 'triadic',
				'balance_score' => 0.95,
			),
			'contrast_metrics'     => array(
				'min_ratio' => 2.5,
				'max_ratio' => 4.5,
			),
			'accessibility_stats'  => array(
				'wcag_aa_pass_rate'  => 0.8,
				'wcag_aaa_pass_rate' => 0.6,
			),
			'psychological_impact' => array(
				'energy'  => 'high',
				'balance' => 'good',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'get_palette_statistics' )
			->with( $palette )
			->willReturn( $expected );

		// Act
		$result = $this->generator->get_palette_statistics( $palette );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'color_distribution', $result );
		$this->assertArrayHasKey( 'harmony_analysis', $result );
		$this->assertArrayHasKey( 'contrast_metrics', $result );
		$this->assertArrayHasKey( 'psychological_impact', $result );
	}

	/**
	 * @dataProvider invalidCriteriaProvider
	 */
	public function test_generate_palette_throws_exception_for_invalid_criteria( array $criteria ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'generate_palette' )
			->with( $criteria )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_palette( $criteria );
	}

	public function invalidCriteriaProvider(): array {
		return array(
			'invalid_base_color' => array( array( 'base_color' => 'not-a-color' ) ),
			'invalid_count'      => array( array( 'color_count' => -1 ) ),
			'invalid_harmony'    => array( array( 'harmony_type' => 'invalid' ) ),
			'empty_criteria'     => array( array() ),
		);
	}
}
