<?php
/**
 * Cultural Analyzer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\CulturalAnalyzer;

class Test_CulturalAnalyzer extends Unit_Test_Case {
	private $analyzer;

	public function setUp(): void {
		$this->analyzer = $this->createMock( CulturalAnalyzer::class );
	}

	public function test_analyze_cultural_significance_returns_array(): void {
		// Arrange
		$color    = '#FF0000';
		$culture  = 'chinese';
		$expected = array(
			'significance'          => 'luck and prosperity',
			'contexts'              => array(
				'celebrations',
				'traditional ceremonies',
				'wedding decorations',
			),
			'associations'          => array(
				'positive' => array( 'fortune', 'joy', 'vitality' ),
				'negative' => array( 'aggression' ),
			),
			'usage_recommendations' => array(
				'ideal_for' => array( 'festive designs', 'branding' ),
				'avoid_in'  => array( 'mourning contexts' ),
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_cultural_significance' )
			->with( $color, $culture )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->analyze_cultural_significance( $color, $culture );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'significance', $result );
		$this->assertArrayHasKey( 'contexts', $result );
		$this->assertArrayHasKey( 'associations', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_cultural_palette_recommendations_returns_array(): void {
		// Arrange
		$culture  = 'japanese';
		$context  = 'traditional';
		$expected = array(
			'recommended_colors' => array(
				array( '#BC002D', 'hinomaru red' ),
				array( '#FFFFFF', 'pure white' ),
				array( '#000000', 'sumi black' ),
			),
			'combinations'       => array(
				'traditional' => array( '#BC002D', '#FFFFFF' ),
				'modern'      => array( '#BC002D', '#000000', '#FFFFFF' ),
			),
			'avoid_colors'       => array(
				'#FF69B4' => 'too playful for traditional context',
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'get_cultural_palette_recommendations' )
			->with( $culture, $context )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->get_cultural_palette_recommendations( $culture, $context );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'recommended_colors', $result );
		$this->assertArrayHasKey( 'combinations', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_validate_cultural_compatibility_returns_array(): void {
		// Arrange
		$palette  = array( '#FF0000', '#FFFFFF', '#000000' );
		$culture  = 'chinese';
		$context  = 'business';
		$expected = array(
			'compatibility_score' => 0.85,
			'strengths'           => array(
				'uses auspicious colors',
				'balanced composition',
			),
			'concerns'            => array(
				'may be too bold for conservative sectors',
			),
			'recommendations'     => array(
				'consider adding gold accents',
				'reduce intensity for formal contexts',
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'validate_cultural_compatibility' )
			->with( $palette, $culture, $context )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->validate_cultural_compatibility( $palette, $culture, $context );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'compatibility_score', $result );
		$this->assertArrayHasKey( 'strengths', $result );
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidColorProvider
	 */
	public function test_analyze_cultural_significance_throws_exception_for_invalid_color( $color ): void {
		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_cultural_significance' )
			->with( $color, 'chinese' )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->analyze_cultural_significance( $color, 'chinese' );
	}

	/**
	 * @dataProvider invalidCultureProvider
	 */
	public function test_get_cultural_palette_recommendations_throws_exception_for_invalid_culture( $culture ): void {
		$this->analyzer
			->expects( $this->once() )
			->method( 'get_cultural_palette_recommendations' )
			->with( $culture, 'traditional' )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->get_cultural_palette_recommendations( $culture, 'traditional' );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_validate_cultural_compatibility_throws_exception_for_invalid_palette( $palette ): void {
		$this->analyzer
			->expects( $this->once() )
			->method( 'validate_cultural_compatibility' )
			->with( $palette, 'chinese', 'business' )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->validate_cultural_compatibility( $palette, 'chinese', 'business' );
	}

	public function invalidColorProvider(): array {
		return array(
			'empty string'   => array( '' ),
			'invalid hex'    => array( '#GG0000' ),
			'no hash'        => array( 'FF0000' ),
			'rgb format'     => array( 'rgb(255,0,0)' ),
			'invalid length' => array( '#F00' ),
		);
	}

	public function invalidCultureProvider(): array {
		return array(
			'empty string'    => array( '' ),
			'numeric culture' => array( 42 ),
			'array culture'   => array( array() ),
			'invalid name'    => array( 'invalid_culture' ),
			'too long'        => array( str_repeat( 'a', 256 ) ),
		);
	}

	public function invalidPaletteProvider(): array {
		return array(
			'empty array'     => array( array() ),
			'invalid colors'  => array( array( '#GG0000', '#FF0000' ) ),
			'mixed formats'   => array( array( '#FF0000', 'rgb(0,255,0)' ) ),
			'non-array input' => array( '#FF0000' ),
			'null input'      => array( null ),
		);
	}
}
