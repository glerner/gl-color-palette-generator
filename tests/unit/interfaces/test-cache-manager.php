<?php
/**
 * Cache Manager Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\CacheManager;

class Test_Cache_Manager extends Unit_Test_Case {
	private $manager;

	public function setUp(): void {
		$this->manager = $this->createMock( CacheManager::class );
	}

	public function test_get_returns_cached_value(): void {
		// Arrange
		$key      = 'test_key';
		$expected = array(
			'color' => '#FF0000',
			'name'  => 'Vibrant Red',
		);

		$this->manager
			->expects( $this->once() )
			->method( 'get' )
			->with( $key )
			->willReturn( $expected );

		// Act
		$result = $this->manager->get( $key );

		// Assert
		$this->assertEquals( $expected, $result );
	}

	public function test_set_returns_true_on_success(): void {
		// Arrange
		$key   = 'test_key';
		$value = array(
			'color' => '#FF0000',
			'name'  => 'Vibrant Red',
		);
		$ttl   = 3600;

		$this->manager
			->expects( $this->once() )
			->method( 'set' )
			->with( $key, $value, $ttl )
			->willReturn( true );

		// Act
		$result = $this->manager->set( $key, $value, $ttl );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_delete_returns_true_on_success(): void {
		// Arrange
		$key = 'test_key';

		$this->manager
			->expects( $this->once() )
			->method( 'delete' )
			->with( $key )
			->willReturn( true );

		// Act
		$result = $this->manager->delete( $key );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_has_returns_true_for_existing_key(): void {
		// Arrange
		$key = 'test_key';

		$this->manager
			->expects( $this->once() )
			->method( 'has' )
			->with( $key )
			->willReturn( true );

		// Act
		$result = $this->manager->has( $key );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_clear_returns_true_on_success(): void {
		// Arrange
		$this->manager
			->expects( $this->once() )
			->method( 'clear' )
			->willReturn( true );

		// Act
		$result = $this->manager->clear();

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_multiple_returns_array_of_values(): void {
		// Arrange
		$keys     = array( 'key1', 'key2' );
		$expected = array(
			'key1' => array( 'color' => '#FF0000' ),
			'key2' => array( 'color' => '#00FF00' ),
		);

		$this->manager
			->expects( $this->once() )
			->method( 'get_multiple' )
			->with( $keys )
			->willReturn( $expected );

		// Act
		$result = $this->manager->get_multiple( $keys );

		// Assert
		$this->assertIsArray( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_set_multiple_returns_true_on_success(): void {
		// Arrange
		$values = array(
			'key1' => array( 'color' => '#FF0000' ),
			'key2' => array( 'color' => '#00FF00' ),
		);
		$ttl    = 3600;

		$this->manager
			->expects( $this->once() )
			->method( 'set_multiple' )
			->with( $values, $ttl )
			->willReturn( true );

		// Act
		$result = $this->manager->set_multiple( $values, $ttl );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_delete_multiple_returns_true_on_success(): void {
		// Arrange
		$keys = array( 'key1', 'key2' );

		$this->manager
			->expects( $this->once() )
			->method( 'delete_multiple' )
			->with( $keys )
			->willReturn( true );

		// Act
		$result = $this->manager->delete_multiple( $keys );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider invalidKeyProvider
	 */
	public function test_get_throws_exception_for_invalid_key( $key ): void {
		$this->manager
			->expects( $this->once() )
			->method( 'get' )
			->with( $key )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->manager->get( $key );
	}

	/**
	 * @dataProvider invalidTtlProvider
	 */
	public function test_set_throws_exception_for_invalid_ttl( $ttl ): void {
		$this->manager
			->expects( $this->once() )
			->method( 'set' )
			->with( 'key', 'value', $ttl )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->manager->set( 'key', 'value', $ttl );
	}

	public function invalidKeyProvider(): array {
		return array(
			'empty string' => array( '' ),
			'array key'    => array( array( 'invalid' ) ),
			'object key'   => array( new \stdClass() ),
			'null key'     => array( null ),
			'boolean key'  => array( true ),
		);
	}

	public function invalidTtlProvider(): array {
		return array(
			'negative ttl' => array( -1 ),
			'zero ttl'     => array( 0 ),
			'string ttl'   => array( 'invalid' ),
			'array ttl'    => array( array() ),
			'object ttl'   => array( new \stdClass() ),
		);
	}
}
