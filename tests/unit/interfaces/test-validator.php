<?php
/**
 * Validator Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Validator;

class Test_Validator extends Unit_Test_Case {
	private $validator;

	public function setUp(): void {
		$this->validator = $this->createMock( Validator::class );
	}

	public function test_validate_returns_true_for_valid_input(): void {
		// Arrange
		$input = array(
			'color' => '#FF0000',
			'name'  => 'Vibrant Red',
		);
		$rules = array(
			'color' => array(
				'type'     => 'hex_color',
				'required' => true,
			),
			'name'  => array(
				'type'       => 'string',
				'min_length' => 3,
			),
		);

		$this->validator
			->expects( $this->once() )
			->method( 'validate' )
			->with( $input, $rules )
			->willReturn( true );

		// Act
		$result = $this->validator->validate( $input, $rules );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_errors_returns_array(): void {
		// Arrange
		$expected = array(
			'color' => array( 'Invalid hex color format' ),
			'name'  => array( 'Name must be at least 3 characters long' ),
		);

		$this->validator
			->expects( $this->once() )
			->method( 'get_errors' )
			->willReturn( $expected );

		// Act
		$result = $this->validator->get_errors();

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'color', $result );
		$this->assertArrayHasKey( 'name', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_add_rule_returns_true_on_success(): void {
		// Arrange
		$name          = 'hex_color';
		$callback      = function ( $value ) {
			return preg_match( '/^#[A-Fa-f0-9]{6}$/', $value );
		};
		$error_message = 'Invalid hex color format';

		$this->validator
			->expects( $this->once() )
			->method( 'add_rule' )
			->with( $name, $callback, $error_message )
			->willReturn( true );

		// Act
		$result = $this->validator->add_rule( $name, $callback, $error_message );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_has_errors_returns_boolean(): void {
		// Arrange
		$this->validator
			->expects( $this->once() )
			->method( 'has_errors' )
			->willReturn( true );

		// Act
		$result = $this->validator->has_errors();

		// Assert
		$this->assertTrue( $result );
	}

	public function test_clear_errors_returns_true_on_success(): void {
		// Arrange
		$this->validator
			->expects( $this->once() )
			->method( 'clear_errors' )
			->willReturn( true );

		// Act
		$result = $this->validator->clear_errors();

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider invalidInputProvider
	 */
	public function test_validate_throws_exception_for_invalid_input( $input ): void {
		$this->validator
			->expects( $this->once() )
			->method( 'validate' )
			->with( $input, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->validator->validate( $input, array() );
	}

	/**
	 * @dataProvider invalidRulesProvider
	 */
	public function test_validate_throws_exception_for_invalid_rules( $rules ): void {
		$this->validator
			->expects( $this->once() )
			->method( 'validate' )
			->with( array(), $rules )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->validator->validate( array(), $rules );
	}

	public function invalidInputProvider(): array {
		return array(
			'null input'     => array( null ),
			'string input'   => array( 'invalid' ),
			'integer input'  => array( 42 ),
			'boolean input'  => array( true ),
			'resource input' => array( fopen( 'php://memory', 'r' ) ),
		);
	}

	public function invalidRulesProvider(): array {
		return array(
			'null rules'          => array( null ),
			'string rules'        => array( 'invalid' ),
			'integer rules'       => array( 42 ),
			'boolean rules'       => array( true ),
			'invalid rule format' => array( array( 'invalid_rule' ) ),
		);
	}
}
