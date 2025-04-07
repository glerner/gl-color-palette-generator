<?php
/**
 * Error Reporter Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\ErrorReporter;

class Test_ErrorReporter extends Unit_Test_Case {
	private $reporter;

	public function setUp(): void {
		$this->reporter = $this->createMock( ErrorReporter::class );
	}

	public function test_report_error_returns_true_on_success(): void {
		// Arrange
		$error   = new \Exception( 'Test error' );
		$context = array(
			'component' => 'color_converter',
			'function'  => 'rgb_to_hex',
			'input'     => array(
				'r' => 255,
				'g' => 0,
				'b' => 0,
			),
		);

		$this->reporter
			->expects( $this->once() )
			->method( 'report_error' )
			->with( $error, $context )
			->willReturn( true );

		// Act
		$result = $this->reporter->report_error( $error, $context );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_error_summary_returns_array(): void {
		// Arrange
		$timeframe = '24h';
		$expected  = array(
			'total_errors'             => 5,
			'error_types'              => array(
				'InvalidArgumentException' => 3,
				'RuntimeException'         => 2,
			),
			'most_affected_components' => array(
				'color_converter'   => 2,
				'palette_generator' => 3,
			),
			'error_trends'             => array(
				'increasing' => false,
				'peak_time'  => '2024-12-08 18:00:00',
			),
		);

		$this->reporter
			->expects( $this->once() )
			->method( 'get_error_summary' )
			->with( $timeframe )
			->willReturn( $expected );

		// Act
		$result = $this->reporter->get_error_summary( $timeframe );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'total_errors', $result );
		$this->assertArrayHasKey( 'error_types', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_error_details_returns_array(): void {
		// Arrange
		$error_id = 'err_123';
		$expected = array(
			'id'          => 'err_123',
			'type'        => 'InvalidArgumentException',
			'message'     => 'Invalid color format',
			'timestamp'   => '2024-12-08 18:38:25',
			'stack_trace' => array(
				'file'     => 'ColorConverter.php',
				'line'     => 42,
				'function' => 'validate_color',
			),
			'context'     => array(
				'input'     => '#GG0000',
				'component' => 'color_converter',
			),
		);

		$this->reporter
			->expects( $this->once() )
			->method( 'get_error_details' )
			->with( $error_id )
			->willReturn( $expected );

		// Act
		$result = $this->reporter->get_error_details( $error_id );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'type', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_clear_error_history_returns_true_on_success(): void {
		// Arrange
		$timeframe = '7d';

		$this->reporter
			->expects( $this->once() )
			->method( 'clear_error_history' )
			->with( $timeframe )
			->willReturn( true );

		// Act
		$result = $this->reporter->clear_error_history( $timeframe );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider invalidErrorProvider
	 */
	public function test_report_error_throws_exception_for_invalid_error( $error ): void {
		$this->reporter
			->expects( $this->once() )
			->method( 'report_error' )
			->with( $error, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->reporter->report_error( $error, array() );
	}

	/**
	 * @dataProvider invalidContextProvider
	 */
	public function test_report_error_throws_exception_for_invalid_context( $context ): void {
		$error = new \Exception( 'Test error' );

		$this->reporter
			->expects( $this->once() )
			->method( 'report_error' )
			->with( $error, $context )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->reporter->report_error( $error, $context );
	}

	/**
	 * @dataProvider invalidTimeframeProvider
	 */
	public function test_get_error_summary_throws_exception_for_invalid_timeframe( $timeframe ): void {
		$this->reporter
			->expects( $this->once() )
			->method( 'get_error_summary' )
			->with( $timeframe )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->reporter->get_error_summary( $timeframe );
	}

	public function invalidErrorProvider(): array {
		return array(
			'string error'  => array( 'error' ),
			'array error'   => array( array( 'error' ) ),
			'null error'    => array( null ),
			'integer error' => array( 42 ),
			'boolean error' => array( true ),
		);
	}

	public function invalidContextProvider(): array {
		return array(
			'string context'    => array( 'context' ),
			'numeric context'   => array( 42 ),
			'null context'      => array( null ),
			'invalid structure' => array( array( 'invalid' => null ) ),
			'missing required'  => array( array( 'component' => null ) ),
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
