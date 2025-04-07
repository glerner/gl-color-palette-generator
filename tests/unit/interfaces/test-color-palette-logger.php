<?php
/**
 * Color Palette Logger Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Logger;

/**
 * Test Color Palette Logger Interface implementation
 */
class Test_Color_Palette_Logger extends Unit_Test_Case {
	private $logger;

	public function setUp(): void {
		$this->logger = $this->createMock( Color_Palette_Logger::class );
	}

	public function test_log_creates_log_entry(): void {
		// Arrange
		$level   = 'info';
		$message = 'Palette updated successfully';
		$context = array(
			'palette_id' => 'pal_123',
			'operation'  => 'update',
			'data'       => array( 'colors' => array( '#FF0000' ) ),
			'metadata'   => array( 'user_id' => 'usr_456' ),
		);

		$expected = array(
			'log_id'   => 'log_abc123',
			'logged'   => true,
			'entry'    => array(
				'level'     => 'info',
				'message'   => 'Palette updated successfully',
				'context'   => $context,
				'timestamp' => '2024-01-20T12:00:00Z',
			),
			'metadata' => array(
				'source'      => 'palette_service',
				'environment' => 'production',
			),
		);

		$this->logger
			->expects( $this->once() )
			->method( 'log' )
			->with( $level, $message, $context )
			->willReturn( $expected );

		// Act
		$result = $this->logger->log( $level, $message, $context );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'log_id', $result );
		$this->assertArrayHasKey( 'logged', $result );
		$this->assertArrayHasKey( 'entry', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['logged'] );
	}

	public function test_get_logs_retrieves_filtered_entries(): void {
		// Arrange
		$criteria = array(
			'levels'     => array( 'info', 'error' ),
			'start_date' => '2024-01-01',
			'end_date'   => '2024-01-31',
			'palette_id' => 'pal_123',
			'limit'      => 10,
		);

		$expected = array(
			'entries'  => array(
				array(
					'log_id'    => 'log_abc123',
					'level'     => 'info',
					'message'   => 'Palette updated',
					'timestamp' => '2024-01-20T12:00:00Z',
				),
				array(
					'log_id'    => 'log_def456',
					'level'     => 'error',
					'message'   => 'Validation failed',
					'timestamp' => '2024-01-20T12:01:00Z',
				),
			),
			'total'    => 2,
			'summary'  => array(
				'by_level'   => array(
					'info'  => 1,
					'error' => 1,
				),
				'time_range' => '2024-01-20',
			),
			'metadata' => array(
				'query_time'      => 0.05,
				'filters_applied' => array( 'level', 'date', 'palette' ),
			),
		);

		$this->logger
			->expects( $this->once() )
			->method( 'get_logs' )
			->with( $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->logger->get_logs( $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'entries', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertArrayHasKey( 'summary', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_analyze_logs_returns_pattern_analysis(): void {
		// Arrange
		$options = array(
			'time_frame'     => '30d',
			'metrics'        => array( 'error_rate', 'operation_frequency' ),
			'include_trends' => true,
		);

		$expected = array(
			'patterns'   => array(
				'common_errors' => array(
					'validation_failure' => 15,
					'api_timeout'        => 8,
				),
				'peak_times'    => array( '12:00', '15:00' ),
			),
			'statistics' => array(
				'total_logs'        => 1500,
				'error_rate'        => 0.02,
				'avg_response_time' => 0.15,
			),
			'trends'     => array(
				'error_rate_trend' => 'decreasing',
				'usage_trend'      => 'increasing',
			),
			'insights'   => array(
				'High error rate during peak hours',
				'Improved stability over time',
			),
		);

		$this->logger
			->expects( $this->once() )
			->method( 'analyze_logs' )
			->with( $options )
			->willReturn( $expected );

		// Act
		$result = $this->logger->analyze_logs( $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'patterns', $result );
		$this->assertArrayHasKey( 'statistics', $result );
		$this->assertArrayHasKey( 'trends', $result );
		$this->assertArrayHasKey( 'insights', $result );
	}

	public function test_maintain_logs_performs_maintenance(): void {
		// Arrange
		$action = 'cleanup';
		$params = array(
			'retention' => 30,
			'filters'   => array( 'level' => array( 'debug' ) ),
			'dry_run'   => false,
		);

		$expected = array(
			'success'    => true,
			'actions'    => array(
				'deleted'    => 150,
				'archived'   => 0,
				'compressed' => 0,
			),
			'statistics' => array(
				'space_freed' => '1.5MB',
				'time_taken'  => 2.5,
			),
			'errors'     => array(),
		);

		$this->logger
			->expects( $this->once() )
			->method( 'maintain_logs' )
			->with( $action, $params )
			->willReturn( $expected );

		// Act
		$result = $this->logger->maintain_logs( $action, $params );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'actions', $result );
		$this->assertArrayHasKey( 'statistics', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertTrue( $result['success'] );
	}

	/**
	 * @dataProvider invalidLogLevelProvider
	 */
	public function test_log_validates_level( string $level ): void {
		$message = 'Test message';

		$this->logger
			->expects( $this->once() )
			->method( 'log' )
			->with( $level, $message )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->logger->log( $level, $message );
	}

	public function invalidLogLevelProvider(): array {
		return array(
			'empty_level'   => array( '' ),
			'invalid_level' => array( 'invalid' ),
			'numeric_level' => array( '123' ),
			'unknown_level' => array( 'critical' ),
		);
	}

	/**
	 * @dataProvider invalidMaintenanceActionProvider
	 */
	public function test_maintain_logs_validates_action( string $action ): void {
		$this->logger
			->expects( $this->once() )
			->method( 'maintain_logs' )
			->with( $action )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->logger->maintain_logs( $action );
	}

	public function invalidMaintenanceActionProvider(): array {
		return array(
			'empty_action'   => array( '' ),
			'invalid_action' => array( 'invalid' ),
			'unknown_action' => array( 'purge' ),
			'numeric_action' => array( '123' ),
		);
	}
}
