<?php
/**
 * Color Palette Storage Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Interfaces\Color_Palette_Storage;
use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;

/**
 * Test class for Color_Palette_Storage interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\Color_Palette_Storage
 */
class Test_Color_Palette_Storage extends Unit_Test_Case {
	private $storage;

	public function setUp(): void {
		$this->storage = $this->createMock( (Color_Palette_Storage_Interface)::class );
	}

	public function test_store_in_db_saves_palette(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'table'      => 'palettes',
			'indexes'    => array( 'name' ),
			'versioning' => true,
		);

		$expected = array(
			'id'       => 123,
			'success'  => true,
			'version'  => array(
				'number'     => 1,
				'created_at' => '2024-01-20T12:00:00Z',
			),
			'metadata' => array(
				'stored_at'    => '2024-01-20T12:00:00Z',
				'storage_type' => 'mysql',
			),
		);

		$this->storage
			->expects( $this->once() )
			->method( 'store_in_db' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->storage->store_in_db( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'version', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['success'] );
	}

	public function test_retrieve_from_db_loads_palette(): void {
		// Arrange
		$id      = 123;
		$options = array(
			'fields'  => array( 'name', 'colors' ),
			'version' => 'latest',
		);

		$expected = array(
			'palette'  => array(
				'name'   => 'Test Palette',
				'colors' => array( '#FF0000', '#00FF00' ),
			),
			'version'  => array(
				'number'    => 1,
				'is_latest' => true,
			),
			'related'  => array(
				'tags' => array( 'modern', 'bright' ),
			),
			'metadata' => array(
				'retrieved_at' => '2024-01-20T12:00:00Z',
				'source'       => 'primary_db',
			),
		);

		$this->storage
			->expects( $this->once() )
			->method( 'retrieve_from_db' )
			->with( $id, $options )
			->willReturn( $expected );

		// Act
		$result = $this->storage->retrieve_from_db( $id, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'palette', $result );
		$this->assertArrayHasKey( 'version', $result );
		$this->assertArrayHasKey( 'related', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_store_in_cache_caches_palette(): void {
		// Arrange
		$key     = 'palette:123';
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'ttl'   => 3600,
			'group' => 'palettes',
			'tags'  => array( 'active' ),
		);

		$expected = array(
			'cached'   => true,
			'key'      => 'palette:123',
			'info'     => array(
				'expires_at' => '2024-01-20T13:00:00Z',
				'size'       => 256,
			),
			'metadata' => array(
				'cached_at'    => '2024-01-20T12:00:00Z',
				'cache_driver' => 'redis',
			),
		);

		$this->storage
			->expects( $this->once() )
			->method( 'store_in_cache' )
			->with( $key, $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->storage->store_in_cache( $key, $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cached', $result );
		$this->assertArrayHasKey( 'key', $result );
		$this->assertArrayHasKey( 'info', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['cached'] );
	}

	public function test_retrieve_from_cache_loads_cached_palette(): void {
		// Arrange
		$key     = 'palette:123';
		$options = array(
			'refresh'  => false,
			'metadata' => true,
		);

		$expected = array(
			'palette'  => array(
				'name'   => 'Test Palette',
				'colors' => array( '#FF0000', '#00FF00' ),
			),
			'hit'      => true,
			'source'   => array(
				'type'   => 'cache',
				'driver' => 'redis',
			),
			'metadata' => array(
				'cached_at'     => '2024-01-20T12:00:00Z',
				'ttl_remaining' => 3000,
			),
		);

		$this->storage
			->expects( $this->once() )
			->method( 'retrieve_from_cache' )
			->with( $key, $options )
			->willReturn( $expected );

		// Act
		$result = $this->storage->retrieve_from_cache( $key, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'palette', $result );
		$this->assertArrayHasKey( 'hit', $result );
		$this->assertArrayHasKey( 'source', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['hit'] );
	}

	/**
	 * @dataProvider invalidPaletteDataProvider
	 */
	public function test_store_in_db_validates_palette( array $palette ): void {
		$this->storage
			->expects( $this->once() )
			->method( 'store_in_db' )
			->with( $palette )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->storage->store_in_db( $palette );
	}

	public function invalidPaletteDataProvider(): array {
		return array(
			'empty_palette'  => array( array() ),
			'missing_name'   => array( array( 'colors' => array( '#FF0000' ) ) ),
			'missing_colors' => array( array( 'name' => 'Test' ) ),
			'invalid_colors' => array(
				array(
					'name'   => 'Test',
					'colors' => 'not-array',
				),
			),
		);
	}

	/**
	 * @dataProvider invalidCacheKeyProvider
	 */
	public function test_store_in_cache_validates_key( string $key ): void {
		$palette = array(
			'name'   => 'Test',
			'colors' => array( '#FF0000' ),
		);

		$this->storage
			->expects( $this->once() )
			->method( 'store_in_cache' )
			->with( $key, $palette )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->storage->store_in_cache( $key, $palette );
	}

	public function invalidCacheKeyProvider(): array {
		return array(
			'empty_key'     => array( '' ),
			'invalid_chars' => array( 'palette:*123' ),
			'too_long'      => array( str_repeat( 'a', 256 ) ),
			'spaces'        => array( 'palette 123' ),
		);
	}
}
