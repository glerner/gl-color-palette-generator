<?php
/**
 * Performance Monitor Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\PerformanceMonitor;

/**
 * @covers \GL_Color_Palette_Generator\Interfaces\PerformanceMonitor
 */
class Test_PerformanceMonitor extends Unit_Test_Case {
	private $monitor;

	public function setUp(): void {
		$this->monitor = $this->createMock( PerformanceMonitor::class );
	}

	public function test_start_monitoring_returns_true_on_success(): void {
		// Arrange
		$context  = 'color_conversion';
		$metadata = array( 'operation' => 'rgb_to_hex' );

		$this->monitor
			->expects( $this->once() )
			->method( 'start_monitoring' )
			->with( $context, $metadata )
			->willReturn( true );

		// Act
		$result = $this->monitor->start_monitoring( $context, $metadata );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_stop_monitoring_returns_metrics_array(): void {
		// Arrange
		$context  = 'color_conversion';
		$expected = array(
			'duration_ms' => 150,
			'memory_peak' => 2048576,
			'cpu_usage'   => 0.5,
			'timestamp'   => '2024-12-08 18:30:50',
		);

		$this->monitor
			->expects( $this->once() )
			->method( 'stop_monitoring' )
			->with( $context )
			->willReturn( $expected );

		// Act
		$result = $this->monitor->stop_monitoring( $context );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'duration_ms', $result );
		$this->assertArrayHasKey( 'memory_peak', $result );
		$this->assertArrayHasKey( 'cpu_usage', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_metrics_returns_array(): void {
		// Arrange
		$context   = 'color_conversion';
		$timeframe = '1h';
		$expected  = array(
			'summary' => array(
				'avg_duration_ms' => 145,
				'max_memory_peak' => 3145728,
				'avg_cpu_usage'   => 0.45,
			),
			'samples' => array(
				array(
					'timestamp'   => '2024-12-08 18:29:50',
					'duration_ms' => 140,
					'memory_peak' => 2097152,
				),
				array(
					'timestamp'   => '2024-12-08 18:30:50',
					'duration_ms' => 150,
					'memory_peak' => 3145728,
				),
			),
		);

		$this->monitor
			->expects( $this->once() )
			->method( 'get_metrics' )
			->with( $context, $timeframe )
			->willReturn( $expected );

		// Act
		$result = $this->monitor->get_metrics( $context, $timeframe );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'summary', $result );
		$this->assertArrayHasKey( 'samples', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_clear_metrics_returns_true_on_success(): void {
		// Arrange
		$context = 'color_conversion';

		$this->monitor
			->expects( $this->once() )
			->method( 'clear_metrics' )
			->with( $context )
			->willReturn( true );

		// Act
		$result = $this->monitor->clear_metrics( $context );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_performance_report_returns_array(): void {
		// Arrange
		$timeframe = '24h';
		$filters   = array( 'min_duration' => 100 );
		$expected  = array(
			'overall_health'  => 'good',
			'metrics_summary' => array(
				'total_operations'  => 1000,
				'avg_response_time' => 145,
				'error_rate'        => 0.01,
			),
			'bottlenecks'     => array(
				array(
					'context'      => 'color_conversion',
					'avg_duration' => 200,
					'occurrence'   => 50,
				),
			),
			'recommendations' => array(
				'Consider caching frequently converted colors',
				'Optimize RGB to LAB conversion algorithm',
			),
		);

		$this->monitor
			->expects( $this->once() )
			->method( 'get_performance_report' )
			->with( $timeframe, $filters )
			->willReturn( $expected );

		// Act
		$result = $this->monitor->get_performance_report( $timeframe, $filters );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'overall_health', $result );
		$this->assertArrayHasKey( 'metrics_summary', $result );
		$this->assertArrayHasKey( 'bottlenecks', $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidContextProvider
	 */
	public function test_start_monitoring_throws_exception_for_invalid_context( $context ): void {
		$this->monitor
			->expects( $this->once() )
			->method( 'start_monitoring' )
			->with( $context, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->monitor->start_monitoring( $context, array() );
	}

	/**
	 * @dataProvider invalidTimeframeProvider
	 */
	public function test_get_metrics_throws_exception_for_invalid_timeframe( $timeframe ): void {
		$this->monitor
			->expects( $this->once() )
			->method( 'get_metrics' )
			->with( 'test_context', $timeframe )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->monitor->get_metrics( 'test_context', $timeframe );
	}

	public function invalidContextProvider(): array {
		return array(
			'empty string'       => array( '' ),
			'invalid characters' => array( 'invalid@context' ),
			'too long'           => array( str_repeat( 'a', 256 ) ),
			'array context'      => array( array() ),
			'object context'     => array( new \stdClass() ),
		);
	}

	public function invalidTimeframeProvider(): array {
		return array(
			'empty string'   => array( '' ),
			'invalid format' => array( '1x' ),
			'negative value' => array( '-1h' ),
			'too large'      => array( '999999h' ),
			'invalid unit'   => array( '24x' ),
		);
	}
}
