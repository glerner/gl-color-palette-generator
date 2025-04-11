<?php
/**
 * Tests for Color Palette Analyzer Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Analyzer;

/**
 * Test Color Palette Analyzer Interface implementation
 */
class Test_Color_Palette_Analyzer extends Unit_Test_Case {
	/** @var Color_Palette_Analyzer */
	private $analyzer;

	public function setUp(): void {
		parent::setUp();
		$this->analyzer = $this->getMockBuilder( Color_Palette_Analyzer::class )
								->getMockForAbstractClass();
	}

	public function test_analyze_harmony_evaluates_relationships(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
		);

		$options = array(
			'schemes'    => array( 'complementary', 'triadic' ),
			'thresholds' => array( 'harmony' => 0.8 ),
		);

		$expected = array(
			'relationships' => array(
				'complementary' => array(
					'score' => 0.9,
					'pairs' => array( array( '#FF0000', '#00FF00' ) ),
				),
				'triadic'       => array(
					'score'  => 0.95,
					'groups' => array( array( '#FF0000', '#00FF00', '#0000FF' ) ),
				),
			),
			'scores'        => array(
				'overall' => 0.92,
				'schemes' => array(
					'complementary' => 0.9,
					'triadic'       => 0.95,
				),
			),
			'suggestions'   => array(
				array(
					'type'    => 'adjustment',
					'message' => 'Slight adjustment to green could improve harmony',
				),
			),
			'metadata'      => array(
				'analyzed_at'      => '2024-01-20T12:00:00Z',
				'schemes_analyzed' => array( 'complementary', 'triadic' ),
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_harmony' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->analyze_harmony( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'relationships', $result );
		$this->assertArrayHasKey( 'scores', $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_analyze_contrast_evaluates_ratios(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FFFFFF', '#000000' ),
		);

		$options = array(
			'ratios'    => array( 'min' => 4.5 ),
			'standards' => array( 'wcag' => 'AA' ),
		);

		$expected = array(
			'ratios'      => array(
				array(
					'colors' => array( '#FFFFFF', '#000000' ),
					'ratio'  => 21,
				),
				'summary' => array(
					'min' => 21,
					'max' => 21,
					'avg' => 21,
				),
			),
			'compliance'  => array(
				'wcag_aa'  => true,
				'wcag_aaa' => true,
			),
			'suggestions' => array(),
			'metadata'    => array(
				'analyzed_at' => '2024-01-20T12:00:00Z',
				'standards'   => array( 'WCAG 2.1 AA' ),
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_contrast' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->analyze_contrast( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'ratios', $result );
		$this->assertArrayHasKey( 'compliance', $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_analyze_distribution_evaluates_balance(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
		);

		$options = array(
			'metrics' => array( 'hue', 'saturation', 'lightness' ),
			'ranges'  => array( 'saturation' => array( 0, 100 ) ),
		);

		$expected = array(
			'metrics'     => array(
				'hue'        => array(
					'spread'   => 120,
					'variance' => 60,
				),
				'saturation' => array(
					'avg'   => 100,
					'range' => 0,
				),
				'lightness'  => array(
					'avg'   => 50,
					'range' => 0,
				),
			),
			'balance'     => array(
				'hue'        => 'well_distributed',
				'saturation' => 'uniform',
				'lightness'  => 'uniform',
			),
			'suggestions' => array(
				array(
					'type'    => 'variety',
					'message' => 'Consider adding lighter tones',
				),
			),
			'metadata'    => array(
				'analyzed_at' => '2024-01-20T12:00:00Z',
				'metrics'     => array( 'hue', 'saturation', 'lightness' ),
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_distribution' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->analyze_distribution( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'metrics', $result );
		$this->assertArrayHasKey( 'balance', $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_analyze_psychology_evaluates_meanings(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#0000FF' ),
		);

		$options = array(
			'associations' => array( 'general', 'cultural' ),
			'context'      => array( 'web', 'business' ),
		);

		$expected = array(
			'meanings'    => array(
				'#FF0000' => array( 'energy', 'passion', 'urgency' ),
				'#0000FF' => array( 'trust', 'stability', 'professionalism' ),
			),
			'emotions'    => array(
				'primary'   => array( 'excitement', 'confidence' ),
				'secondary' => array( 'trust', 'reliability' ),
			),
			'suggestions' => array(
				array(
					'type'    => 'context',
					'message' => 'Well-suited for business applications',
				),
			),
			'metadata'    => array(
				'analyzed_at' => '2024-01-20T12:00:00Z',
				'context'     => array( 'web', 'business' ),
			),
		);

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_psychology' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->analyzer->analyze_psychology( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meanings', $result );
		$this->assertArrayHasKey( 'emotions', $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_analyze_harmony_validates_palette( array $palette ): void {
		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_harmony' )
			->with( $palette )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->analyze_harmony( $palette );
	}

	public function invalidPaletteProvider(): array {
		return array(
			'empty_palette'  => array( array() ),
			'missing_colors' => array( array( 'name' => 'Test' ) ),
			'invalid_colors' => array( array( 'colors' => array( 'not-a-color' ) ) ),
			'single_color'   => array( array( 'colors' => array( '#FF0000' ) ) ),
		);
	}

	/**
	 * @dataProvider invalidContrastOptionsProvider
	 */
	public function test_analyze_contrast_validates_options( array $options ): void {
		$palette = array( 'colors' => array( '#FF0000', '#FFFFFF' ) );

		$this->analyzer
			->expects( $this->once() )
			->method( 'analyze_contrast' )
			->with( $palette, $options )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analyzer->analyze_contrast( $palette, $options );
	}

	public function invalidContrastOptionsProvider(): array {
		return array(
			'invalid_ratio'    => array( array( 'ratios' => array( 'min' => -1 ) ) ),
			'invalid_standard' => array( array( 'standards' => array( 'invalid' ) ) ),
			'invalid_type'     => array( array( 'ratios' => 'not-array' ) ),
			'empty_options'    => array( array() ),
		);
	}
}
