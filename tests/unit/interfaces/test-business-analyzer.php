<?php
/**
 * Business Analyzer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\BusinessAnalyzer;

/**
 * Test class for BusinessAnalyzer interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\BusinessAnalyzer
 */
class Test_BusinessAnalyzer extends Unit_Test_Case {
	private $analyzer;

	public function setUp(): void {
		$this->analyzer = $this->createMock( BusinessAnalyzer::class );
	}

	public function test_analyze_brand_compatibility_returns_valid_array(): void {
		// Arrange
		$palette    = array( '#FF0000', '#00FF00', '#0000FF' );
		$brand_info = array(
			'industry'        => 'technology',
			'target_audience' => 'professionals',
			'brand_values'    => array( 'innovation', 'trust', 'reliability' ),
		);
		$expected   = array(
			'compatibility_score' => 0.85,
			'strengths'           => array(
				'aligns with tech industry standards',
				'professional appearance',
				'conveys innovation',
			),
			'weaknesses'          => array(
				'may be too bold for conservative clients',
			),
			'recommendations'     => array(
				'consider adding a neutral color',
				'reduce saturation for better balance',
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_brand_compatibility' )
			->with( $palette, $brand_info )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->analyze_brand_compatibility( $palette, $brand_info );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'compatibility_score', $result );
		$this->assertArrayHasKey( 'strengths', $result );
		$this->assertArrayHasKey( 'weaknesses', $result );
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_industry_guidelines_returns_valid_array(): void {
		// Arrange
		$industry = 'technology';
		$expected = array(
			'recommended_colors'     => array(
				'primary'   => array( '#0066CC', '#00AA88' ),
				'secondary' => array( '#FF6600', '#FFCC00' ),
				'accent'    => array( '#FF3366', '#9933CC' ),
			),
			'color_ratios'           => array(
				'primary'   => 0.6,
				'secondary' => 0.3,
				'accent'    => 0.1,
			),
			'avoid_colors'           => array( '#FF0000', '#FF00FF' ),
			'special_considerations' => array(
				'ensure high contrast for readability',
				'consider color blindness accessibility',
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'get_industry_guidelines' )
			->with( $industry )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->get_industry_guidelines( $industry );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'recommended_colors', $result );
		$this->assertArrayHasKey( 'color_ratios', $result );
		$this->assertArrayHasKey( 'avoid_colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_usage_guidelines_returns_valid_array(): void {
		// Arrange
		$palette  = array( '#FF0000', '#00FF00', '#0000FF' );
		$context  = array(
			'platform'  => 'web',
			'use_cases' => array( 'branding', 'marketing' ),
		);
		$expected = array(
			'primary_color_usage' => array(
				'main_brand_color' => '#FF0000',
				'recommended_uses' => array( 'logo', 'headers', 'buttons' ),
				'usage_ratio'      => '60%',
			),
			'secondary_colors'    => array(
				array(
					'color'            => '#00FF00',
					'recommended_uses' => array( 'accents', 'icons' ),
					'usage_ratio'      => '30%',
				),
				array(
					'color'            => '#0000FF',
					'recommended_uses' => array( 'highlights', 'links' ),
					'usage_ratio'      => '10%',
				),
			),
			'combinations'        => array(
				'recommended' => array(
					array( '#FF0000', '#00FF00' ),
					array( '#FF0000', '#0000FF' ),
				),
				'avoid'       => array(
					array( '#00FF00', '#0000FF' ),
				),
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'generate_usage_guidelines' )
			->with( $palette, $context )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->generate_usage_guidelines( $palette, $context );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'primary_color_usage', $result );
		$this->assertArrayHasKey( 'secondary_colors', $result );
		$this->assertArrayHasKey( 'combinations', $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_analyze_brand_compatibility_throws_exception_for_invalid_palette( array $palette ): void {
		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_brand_compatibility' )
			->with( $palette, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->analyze_brand_compatibility( $palette, array() );
	}

	/**
	 * @dataProvider invalidIndustryProvider
	 */
	public function test_get_industry_guidelines_throws_exception_for_invalid_industry( string $industry ): void {
		$this->analyzer
			->expects( $this->once() )
			->method( 'get_industry_guidelines' )
			->with( $industry )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->get_industry_guidelines( $industry );
	}

	public function invalidPaletteProvider(): array {
		return array(
			'empty array'      => array( array() ),
			'invalid colors'   => array( array( 'not-a-color', '#FF0000' ) ),
			'too many colors'  => array( array_fill( 0, 11, '#FF0000' ) ),
			'duplicate colors' => array( array( '#FF0000', '#FF0000' ) ),
			'mixed formats'    => array( array( '#FF0000', 'rgb(0,255,0)' ) ),
		);
	}

	public function invalidIndustryProvider(): array {
		return array(
			'empty string'       => array( '' ),
			'invalid industry'   => array( 'not-a-real-industry' ),
			'numeric value'      => array( '123' ),
			'special characters' => array( 'tech@industry' ),
			'too long'           => array( str_repeat( 'a', 256 ) ),
		);
	}
}
