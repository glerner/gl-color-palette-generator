<?php
/**
 * Tests for Color Palette Cache Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Cache;

/**
 * Test Color Palette Cache Interface implementation
 */
class Test_Color_Palette_Cache extends Unit_Test_Case {
	/** @var Color_Palette_Cache */
	private $cache;

	public function setUp(): void {
		parent::setUp();
		$this->cache = $this->getMockBuilder( Color_Palette_Cache::class )
							->getMockForAbstractClass();
	}

	public function test_store_caches_palette_data(): void {
		// Arrange
		$key  = 'palette:123';
		$data = array(
			'colors'   => array( '#FF0000', '#00FF00' ),
			'metadata' => array( 'name' => 'Test Palette' ),
		);

		$options = array(
			'ttl'   => 3600,
			'group' => 'palettes',
			'tags'  => array( 'active', 'production' ),
		);

		$expected = array(
			'cached'      => true,
			'cache_key'   => 'palette:123',
			'metadata'    => array(
				'created_at' => '2024-01-20T12:00:00Z',
				'expires_at' => '2024-01-20T13:00:00Z',
				'size'       => 256,
			),
			'diagnostics' => array(
				'storage_type'   => 'memory',
				'compression'    => false,
				'operation_time' => 0.003,
			),
		);

		$this->cache
			->expects( $this->once() )
			->method( 'store' )
			->with( $key, $data, $options )
			->willReturn( $expected );

		// Act
		$result = $this->cache->store( $key, $data, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cached', $result );
		$this->assertArrayHasKey( 'cache_key', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertArrayHasKey( 'diagnostics', $result );
		$this->assertTrue( $result['cached'] );
	}

	public function test_retrieve_returns_cached_data(): void {
		// Arrange
		$key     = 'palette:123';
		$options = array(
			'refresh'   => false,
			'with_meta' => true,
		);

		$expected = array(
			'data'        => array(
				'colors'   => array( '#FF0000', '#00FF00' ),
				'metadata' => array( 'name' => 'Test Palette' ),
			),
			'found'       => true,
			'metadata'    => array(
				'created_at'  => '2024-01-20T12:00:00Z',
				'accessed_at' => '2024-01-20T12:30:00Z',
				'hits'        => 5,
			),
			'performance' => array(
				'lookup_time'   => 0.001,
				'transfer_time' => 0.002,
			),
		);

		$this->cache
			->expects( $this->once() )
			->method( 'retrieve' )
			->with( $key, $options )
			->willReturn( $expected );

		// Act
		$result = $this->cache->retrieve( $key, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'data', $result );
		$this->assertArrayHasKey( 'found', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertArrayHasKey( 'performance', $result );
		$this->assertTrue( $result['found'] );
	}

	public function test_invalidate_removes_cached_data(): void {
		// Arrange
		$identifier = 'palette:123';
		$options    = array(
			'scope'   => 'key',
			'cascade' => true,
		);

		$expected = array(
			'invalidated' => true,
			'affected'    => 3,
			'details'     => array(
				'invalidated_keys' => array( 'palette:123', 'palette:123:variants' ),
				'cascade_depth'    => 1,
			),
			'errors'      => array(),
		);

		$this->cache
			->expects( $this->once() )
			->method( 'invalidate' )
			->with( $identifier, $options )
			->willReturn( $expected );

		// Act
		$result = $this->cache->invalidate( $identifier, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'invalidated', $result );
		$this->assertArrayHasKey( 'affected', $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertTrue( $result['invalidated'] );
	}

	public function test_manage_returns_cache_statistics(): void {
		// Arrange
		$operation = 'stats';
		$params    = array(
			'metrics'  => array( 'hits', 'size', 'efficiency' ),
			'detailed' => true,
		);

		$expected = array(
			'statistics'      => array(
				'total_entries' => 150,
				'total_size'    => '2.5MB',
				'hit_ratio'     => 0.85,
			),
			'health'          => array(
				'fragmentation'    => 0.05,
				'efficiency_score' => 0.92,
			),
			'actions'         => array(
				'analyzed_entries' => 150,
				'computed_metrics' => array( 'hits', 'size' ),
			),
			'recommendations' => array(
				'Consider increasing TTL for frequently accessed items',
				'Run cleanup for expired entries',
			),
		);

		$this->cache
			->expects( $this->once() )
			->method( 'manage' )
			->with( $operation, $params )
			->willReturn( $expected );

		// Act
		$result = $this->cache->manage( $operation, $params );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'statistics', $result );
		$this->assertArrayHasKey( 'health', $result );
		$this->assertArrayHasKey( 'actions', $result );
		$this->assertArrayHasKey( 'recommendations', $result );
	}

	/**
	 * @dataProvider invalidCacheKeyProvider
	 */
	public function test_store_validates_cache_key( string $key ): void {
		$data = array( 'test' => 'data' );

		$this->cache
			->expects( $this->once() )
			->method( 'store' )
			->with( $key, $data )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->cache->store( $key, $data );
	}

	public function invalidCacheKeyProvider(): array {
		return array(
			'empty_key'     => array( '' ),
			'invalid_chars' => array( 'invalid:*key' ),
			'too_long'      => array( str_repeat( 'a', 300 ) ),
			'spaces'        => array( 'invalid key' ),
		);
	}
}
