<?php
/**
 * Error Handler Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\ErrorHandler;

class Test_ErrorHandler extends Unit_Test_Case {
	private $handler;

	public function setUp(): void {
		$this->handler = $this->createMock( ErrorHandler::class );
	}

	public function test_handle_error_returns_true_on_success(): void {
		// Arrange
		$error   = new \Exception( 'Test error' );
		$context = array(
			'function' => 'test_function',
			'line'     => 42,
		);

		$this->handler
			->expects( $this->once() )
			->method( 'handle_error' )
			->with( $error, $context )
			->willReturn( true );

		// Act
		$result = $this->handler->handle_error( $error, $context );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_error_details_returns_array(): void {
		// Arrange
		$error    = new \Exception( 'Test error' );
		$expected = array(
			'message'  => 'Test error',
			'code'     => 0,
			'file'     => __FILE__,
			'line'     => __LINE__ - 4,
			'trace'    => $error->getTrace(),
			'severity' => 'error',
		);

		$this->handler
			->expects( $this->once() )
			->method( 'get_error_details' )
			->with( $error )
			->willReturn( $expected );

		// Act
		$result = $this->handler->get_error_details( $error );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'message', $result );
		$this->assertArrayHasKey( 'code', $result );
		$this->assertArrayHasKey( 'file', $result );
		$this->assertArrayHasKey( 'line', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_log_error_returns_true_on_success(): void {
		// Arrange
		$error    = new \Exception( 'Test error' );
		$severity = 'error';
		$context  = array( 'function' => 'test_function' );

		$this->handler
			->expects( $this->once() )
			->method( 'log_error' )
			->with( $error, $severity, $context )
			->willReturn( true );

		// Act
		$result = $this->handler->log_error( $error, $severity, $context );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_last_error_returns_array(): void {
		// Arrange
		$expected = array(
			'message'   => 'Last test error',
			'code'      => 500,
			'timestamp' => '2024-12-08 18:26:54',
			'context'   => array( 'function' => 'test_function' ),
		);

		$this->handler
			->expects( $this->once() )
			->method( 'get_last_error' )
			->willReturn( $expected );

		// Act
		$result = $this->handler->get_last_error();

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'message', $result );
		$this->assertArrayHasKey( 'code', $result );
		$this->assertArrayHasKey( 'timestamp', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_clear_errors_returns_true_on_success(): void {
		// Arrange
		$this->handler
			->expects( $this->once() )
			->method( 'clear_errors' )
			->willReturn( true );

		// Act
		$result = $this->handler->clear_errors();

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider invalidErrorProvider
	 */
	public function test_handle_error_throws_exception_for_invalid_error( $error ): void {
		$this->handler
			->expects( $this->once() )
			->method( 'handle_error' )
			->with( $error, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->handler->handle_error( $error, array() );
	}

	/**
	 * @dataProvider invalidSeverityProvider
	 */
	public function test_log_error_throws_exception_for_invalid_severity( $severity ): void {
		$error = new \Exception( 'Test error' );

		$this->handler
			->expects( $this->once() )
			->method( 'log_error' )
			->with( $error, $severity, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->handler->log_error( $error, $severity, array() );
	}

	public function invalidErrorProvider(): array {
		return array(
			'null error'    => array( null ),
			'string error'  => array( 'error' ),
			'array error'   => array( array( 'error' ) ),
			'integer error' => array( 42 ),
			'boolean error' => array( true ),
		);
	}

	public function invalidSeverityProvider(): array {
		return array(
			'empty string'     => array( '' ),
			'invalid severity' => array( 'invalid' ),
			'numeric severity' => array( 42 ),
			'array severity'   => array( array() ),
			'object severity'  => array( new \stdClass() ),
		);
	}
}
