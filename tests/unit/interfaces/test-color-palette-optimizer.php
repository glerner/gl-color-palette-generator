<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Optimizer;

class Test_Color_Palette_Optimizer extends Unit_Test_Case {
	private $optimizer;

	public function setUp(): void {
		$this->optimizer = $this->createMock( Color_Palette_Optimizer::class );
	}

	public function test_optimize_accessibility_improves_compliance(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#FFFF00' ),
		);

		$options = array(
			'standards'   => array( 'wcag' => 'AA' ),
			'constraints' => array( 'preserve_hue' => true ),
		);

		$expected = array(
			'optimized'    => array(
				'colors' => array( '#FF0000', '#FFE500' ),
			),
			'improvements' => array(
				'contrast'    => array(
					'before' => 2.5,
					'after'  => 4.6,
				),
				'readability' => array(
					'before' => 'poor',
					'after'  => 'good',
				),
			),
			'compliance'   => array(
				'wcag_aa'  => true,
				'wcag_aaa' => false,
			),
			'metadata'     => array(
				'optimized_at' => '2024-01-20T12:00:00Z',
				'standards'    => array( 'WCAG 2.1 AA' ),
			),
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_accessibility' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->optimize_accessibility( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'optimized', $result );
		$this->assertArrayHasKey( 'improvements', $result );
		$this->assertArrayHasKey( 'compliance', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_optimize_harmony_improves_relationships(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
		);

		$options = array(
			'schemes'    => array( 'triadic' ),
			'thresholds' => array( 'harmony' => 0.9 ),
		);

		$expected = array(
			'optimized'    => array(
				'colors' => array( '#FF0000', '#00FF33', '#0000FF' ),
			),
			'improvements' => array(
				'harmony' => array(
					'before' => 0.85,
					'after'  => 0.95,
				),
				'balance' => array(
					'before' => 'good',
					'after'  => 'excellent',
				),
			),
			'scores'       => array(
				'overall' => 0.95,
				'schemes' => array( 'triadic' => 0.95 ),
			),
			'metadata'     => array(
				'optimized_at' => '2024-01-20T12:00:00Z',
				'schemes'      => array( 'triadic' ),
			),
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_harmony' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->optimize_harmony( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'optimized', $result );
		$this->assertArrayHasKey( 'improvements', $result );
		$this->assertArrayHasKey( 'scores', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_optimize_performance_improves_efficiency(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#FF0001', '#FF0002' ),
		);

		$options = array(
			'targets'     => array( 'size' => 'minimal' ),
			'compression' => array( 'method' => 'quantize' ),
		);

		$expected = array(
			'optimized'    => array(
				'colors' => array( '#FF0000' ),
			),
			'improvements' => array(
				'size'    => array(
					'before' => '768B',
					'after'  => '256B',
				),
				'loading' => array(
					'before' => '100ms',
					'after'  => '50ms',
				),
			),
			'metrics'      => array(
				'compression_ratio' => 3,
				'quality_loss'      => 0.01,
			),
			'metadata'     => array(
				'optimized_at' => '2024-01-20T12:00:00Z',
				'compression'  => 'quantize',
			),
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_performance' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->optimize_performance( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'optimized', $result );
		$this->assertArrayHasKey( 'improvements', $result );
		$this->assertArrayHasKey( 'metrics', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_optimize_display_improves_appearance(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'devices'      => array( 'mobile', 'desktop' ),
			'environments' => array( 'bright', 'dark' ),
		);

		$expected = array(
			'optimized'    => array(
				'colors' => array(
					'default' => array( '#FF0000', '#00FF00' ),
					'dark'    => array( '#CC0000', '#00CC00' ),
				),
			),
			'improvements' => array(
				'visibility'  => array(
					'before' => 'good',
					'after'  => 'excellent',
				),
				'consistency' => array(
					'before' => 'varied',
					'after'  => 'uniform',
				),
			),
			'profiles'     => array(
				'mobile'  => array(
					'gamma'      => 2.2,
					'brightness' => 'auto',
				),
				'desktop' => array(
					'gamma'      => 2.2,
					'brightness' => 'fixed',
				),
			),
			'metadata'     => array(
				'optimized_at' => '2024-01-20T12:00:00Z',
				'devices'      => array( 'mobile', 'desktop' ),
			),
		);

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_display' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->optimizer->optimize_display( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'optimized', $result );
		$this->assertArrayHasKey( 'improvements', $result );
		$this->assertArrayHasKey( 'profiles', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_optimize_accessibility_validates_palette( array $palette ): void {
		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_accessibility' )
			->with( $palette )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->optimizer->optimize_accessibility( $palette );
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
	 * @dataProvider invalidOptimizationOptionsProvider
	 */
	public function test_optimize_harmony_validates_options( array $options ): void {
		$palette = array( 'colors' => array( '#FF0000', '#00FF00' ) );

		$this->optimizer
			->expects( $this->once() )
			->method( 'optimize_harmony' )
			->with( $palette, $options )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->optimizer->optimize_harmony( $palette, $options );
	}

	public function invalidOptimizationOptionsProvider(): array {
		return array(
			'invalid_scheme'    => array( array( 'schemes' => array( 'invalid' ) ) ),
			'invalid_threshold' => array( array( 'thresholds' => array( 'harmony' => 2 ) ) ),
			'invalid_type'      => array( array( 'schemes' => 'not-array' ) ),
			'empty_options'     => array( array() ),
		);
	}
}
