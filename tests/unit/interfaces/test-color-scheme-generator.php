<?php
/**
 * Color Scheme Generator Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\ColorSchemeGenerator;

/**
 * Test class for ColorSchemeGenerator interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\ColorSchemeGenerator
 */
class Test_Color_Scheme_Generator extends Unit_Test_Case {
	private $generator;

	public function setUp(): void {
		$this->generator = $this->createMock( ColorSchemeGenerator::class );
	}

	public function test_generate_scheme_returns_complete_scheme(): void {
		// Arrange
		$criteria = array(
			'base_color'    => '#FF0000',
			'style'         => 'modern',
			'purpose'       => 'web',
			'accessibility' => true,
		);

		$expected = array(
			'primary'   => array(
				'base'  => '#FF0000',
				'light' => '#FF3333',
				'dark'  => '#CC0000',
			),
			'secondary' => array(
				'base'  => '#00FF00',
				'light' => '#33FF33',
				'dark'  => '#00CC00',
			),
			'accent'    => array( '#0000FF', '#FFFF00' ),
			'neutral'   => array(
				'100' => '#FFFFFF',
				'200' => '#F5F5F5',
				'900' => '#000000',
			),
			'semantic'  => array(
				'success' => '#00CC00',
				'warning' => '#FFCC00',
				'error'   => '#CC0000',
				'info'    => '#0066CC',
			),
			'metadata'  => array(
				'generation_date' => '2024-01-20',
				'algorithm'       => 'v2.0',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_scheme' )
			->with( $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_scheme( $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'primary', $result );
		$this->assertArrayHasKey( 'secondary', $result );
		$this->assertArrayHasKey( 'accent', $result );
		$this->assertArrayHasKey( 'neutral', $result );
		$this->assertArrayHasKey( 'semantic', $result );
	}

	public function test_get_color_variations_returns_valid_variations(): void {
		// Arrange
		$base_color = '#FF0000';
		$role       = 'primary';
		$options    = array(
			'steps'       => 5,
			'value_range' => array( 20, 80 ),
		);

		$expected = array(
			'colors'   => array(
				'100' => '#FF3333',
				'300' => '#FF0000',
				'500' => '#CC0000',
				'700' => '#990000',
				'900' => '#660000',
			),
			'metadata' => array(
				'base_color'       => '#FF0000',
				'variation_method' => 'value_steps',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'get_color_variations' )
			->with( $base_color, $role, $options )
			->willReturn( $expected );

		// Act
		$result = $this->generator->get_color_variations( $base_color, $role, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertCount( 5, $result['colors'] );
	}

	public function test_generate_semantic_colors_returns_valid_assignments(): void {
		// Arrange
		$base_scheme = array(
			'primary'   => '#FF0000',
			'secondary' => '#00FF00',
		);
		$options     = array(
			'use_defaults'    => true,
			'ensure_contrast' => true,
		);

		$expected = array(
			'colors'    => array(
				'success' => '#00CC00',
				'warning' => '#FFCC00',
				'error'   => '#CC0000',
				'info'    => '#0066CC',
			),
			'rationale' => array(
				'success' => 'Derived from secondary color',
				'error'   => 'Derived from primary color',
			),
			'contrast'  => array(
				'success' => array(
					'ratio'  => 4.5,
					'passes' => true,
				),
				'error'   => array(
					'ratio'  => 4.8,
					'passes' => true,
				),
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_semantic_colors' )
			->with( $base_scheme, $options )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_semantic_colors( $base_scheme, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertArrayHasKey( 'rationale', $result );
		$this->assertArrayHasKey( 'contrast', $result );
	}

	public function test_analyze_scheme_returns_comprehensive_analysis(): void {
		// Arrange
		$scheme = array(
			'primary'   => '#FF0000',
			'secondary' => '#00FF00',
			'accent'    => '#0000FF',
		);

		$expected = array(
			'is_valid'      => true,
			'accessibility' => array(
				'wcag_aa_compliance' => true,
				'contrast_scores'    => array( '4.5', '5.2' ),
			),
			'harmony'       => array(
				'score' => 0.85,
				'type'  => 'triadic',
			),
			'coverage'      => array(
				'color_space' => 0.75,
				'gamut'       => 'sRGB',
			),
			'improvements'  => array(
				'Consider adding neutral colors',
				'Increase contrast in secondary colors',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'analyze_scheme' )
			->with( $scheme )
			->willReturn( $expected );

		// Act
		$result = $this->generator->analyze_scheme( $scheme );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'is_valid', $result );
		$this->assertArrayHasKey( 'accessibility', $result );
		$this->assertArrayHasKey( 'harmony', $result );
		$this->assertArrayHasKey( 'improvements', $result );
	}

	/**
	 * @dataProvider invalidCriteriaProvider
	 */
	public function test_generate_scheme_validates_criteria( array $criteria ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'generate_scheme' )
			->with( $criteria )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_scheme( $criteria );
	}

	public function invalidCriteriaProvider(): array {
		return array(
			'invalid_color'   => array( array( 'base_color' => 'not-a-color' ) ),
			'invalid_style'   => array( array( 'style' => 123 ) ),
			'empty_criteria'  => array( array() ),
			'invalid_purpose' => array( array( 'purpose' => 'invalid' ) ),
		);
	}

	/**
	 * @dataProvider invalidSchemeProvider
	 */
	public function test_analyze_scheme_handles_invalid_schemes( array $scheme ): void {
		$expected = array(
			'is_valid'      => false,
			'accessibility' => array(),
			'harmony'       => array(),
			'coverage'      => array(),
			'improvements'  => array( 'Invalid color scheme structure' ),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'analyze_scheme' )
			->with( $scheme )
			->willReturn( $expected );

		$result = $this->generator->analyze_scheme( $scheme );
		$this->assertFalse( $result['is_valid'] );
		$this->assertNotEmpty( $result['improvements'] );
	}

	public function invalidSchemeProvider(): array {
		return array(
			'empty_scheme'      => array( array() ),
			'missing_primary'   => array( array( 'secondary' => '#00FF00' ) ),
			'invalid_colors'    => array( array( 'primary' => 'invalid' ) ),
			'incomplete_scheme' => array( array( 'primary' => '#FF0000' ) ),
		);
	}
}
