<?php
/**
 * Analytics Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Analytics;

class Test_Analytics extends Unit_Test_Case {
	private $analytics;

	public function setUp(): void {
		$this->analytics = $this->createMock( Analytics::class );
	}

	public function test_track_usage_handles_valid_feature(): void {
		// Arrange
		$feature = 'palette_generation';
		$context = array(
			'colors' => 5,
			'style'  => 'modern',
		);

		$this->analytics
			->expects( $this->once() )
			->method( 'track_usage' )
			->with( $feature, $context );

		// Act & Assert
		$this->analytics->track_usage( $feature, $context );
	}

	public function test_generate_report_returns_valid_structure(): void {
		// Arrange
		$criteria = array(
			'start_date' => '2024-01-01',
			'end_date'   => '2024-01-31',
			'features'   => array( 'palette_generation', 'color_analysis' ),
		);

		$expected = array(
			'usage_stats'    => array(
				'palette_generation' => 150,
				'color_analysis'     => 75,
			),
			'trends'         => array(
				'daily_usage' => array( /* ... */ ),
				'peak_times'  => array( /* ... */ ),
			),
			'popular_colors' => array(
				'#FF0000' => 45,
				'#00FF00' => 32,
			),
			'user_segments'  => array(
				'professional' => 60,
				'hobbyist'     => 40,
			),
		);

		$this->analytics
			->expects( $this->once() )
			->method( 'generate_report' )
			->with( $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->analytics->generate_report( $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'usage_stats', $result );
		$this->assertArrayHasKey( 'trends', $result );
		$this->assertArrayHasKey( 'popular_colors', $result );
		$this->assertArrayHasKey( 'user_segments', $result );
	}

	public function test_get_metrics_returns_current_data(): void {
		// Arrange
		$expected = array(
			'total_palettes'   => 1000,
			'active_users'     => 150,
			'popular_features' => array(
				'palette_generation' => 500,
				'color_analysis'     => 300,
			),
			'color_trends'     => array(
				'blue'  => 'increasing',
				'green' => 'stable',
			),
		);

		$this->analytics
			->expects( $this->once() )
			->method( 'get_metrics' )
			->willReturn( $expected );

		// Act
		$result = $this->analytics->get_metrics();

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'total_palettes', $result );
		$this->assertArrayHasKey( 'active_users', $result );
		$this->assertArrayHasKey( 'popular_features', $result );
		$this->assertArrayHasKey( 'color_trends', $result );
		$this->assertIsInt( $result['total_palettes'] );
		$this->assertIsInt( $result['active_users'] );
	}

	public function test_export_analytics_returns_valid_format(): void {
		// Arrange
		$format   = 'json';
		$options  = array( 'include_trends' => true );
		$expected = '{"data": "sample"}';

		$this->analytics
			->expects( $this->once() )
			->method( 'export_analytics' )
			->with( $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->analytics->export_analytics( $format, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertJson( $result );
	}

	/**
	 * @dataProvider invalidFormatProvider
	 */
	public function test_export_analytics_throws_exception_for_invalid_format( string $format ): void {
		$this->analytics
			->expects( $this->once() )
			->method( 'export_analytics' )
			->with( $format )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analytics->export_analytics( $format );
	}

	public function invalidFormatProvider(): array {
		return array(
			'empty_format'       => array( '' ),
			'invalid_format'     => array( 'invalid' ),
			'unsupported_format' => array( 'xml' ),
		);
	}

	/**
	 * @dataProvider invalidDateRangeProvider
	 */
	public function test_generate_report_validates_date_range( array $criteria ): void {
		$this->analytics
			->expects( $this->once() )
			->method( 'generate_report' )
			->with( $criteria )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->analytics->generate_report( $criteria );
	}

	public function invalidDateRangeProvider(): array {
		return array(
			'future_dates'   => array( array( 'start_date' => '2025-01-01' ) ),
			'invalid_range'  => array(
				array(
					'start_date' => '2024-01-31',
					'end_date'   => '2024-01-01',
				),
			),
			'invalid_format' => array( array( 'start_date' => 'invalid' ) ),
		);
	}
}
