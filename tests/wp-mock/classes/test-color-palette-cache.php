<?php
/**
 * Color Palette Cache Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Classes
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Classes;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Classes\Color_Palette_Cache;
use GL_Color_Palette_Generator\Classes\Color_Palette;
use Brain\Monkey\Functions;


/**
 * Tests for the Color Palette Cache class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Classes
 */
class Test_Color_Palette_Cache extends WP_Mock_Test_Case {
	protected Color_Palette_Cache $cache;
	protected Color_Palette $test_palette;

	public function setUp(): void {
		parent::setUp();
		\Brain\Monkey\setUp();

		$this->cache        = new Color_Palette_Cache();
		$this->test_palette = new Color_Palette(
			array(
				'name'     => 'Test Palette',
				'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
				'metadata' => array( 'type' => 'test' ),
			)
		);
	}

	public function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	public function test_set_palette(): void {
		Functions\expect( 'wp_cache_set' )
			->once()
			->andReturn( true );

		$result = $this->cache->set( 'test_key', $this->test_palette );
		$this->assertTrue( $result );
	}

	public function test_get_palette(): void {
		Functions\expect( 'wp_cache_get' )
			->once()
			->andReturn(
				array(
					'name'     => 'Test Palette',
					'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
					'metadata' => array( 'type' => 'test' ),
				)
			);

		$palette = $this->cache->get( 'test_key' );

		$this->assertInstanceOf( Color_Palette::class, $palette );
		$this->assertEquals( 'Test Palette', $palette->getName() );
		$this->assertCount( 3, $palette->getColors() );
	}

	public function test_get_nonexistent_palette(): void {
		Functions\expect( 'wp_cache_get' )
			->once()
			->andReturn( false );

		$palette = $this->cache->get( 'nonexistent' );
		$this->assertNull( $palette );
	}

	public function test_delete_palette(): void {
		Functions\expect( 'wp_cache_delete' )
			->once()
			->andReturn( true );

		$result = $this->cache->delete( 'test_key' );
		$this->assertTrue( $result );
	}

	public function test_has_palette(): void {
		Functions\expect( 'wp_cache_get' )
			->once()
			->andReturn( array( 'some' => 'data' ) );

		$result = $this->cache->has( 'test_key' );
		$this->assertTrue( $result );
	}

	public function test_clear_cache(): void {
		Functions\expect( 'wp_cache_flush' )
			->once()
			->andReturn( true );

		$result = $this->cache->clear();
		$this->assertTrue( $result );
	}

	public function test_get_stats(): void {
		Functions\expect( 'wp_cache_get' )
			->times( 2 )
			->andReturn( array( 'some' => 'data' ), false );

		// Generate some stats
		$this->cache->has( 'exists' );
		$this->cache->has( 'nonexistent' );

		$stats = $this->cache->getStats();

		$this->assertArrayHasKey( 'hits', $stats );
		$this->assertArrayHasKey( 'misses', $stats );
		$this->assertArrayHasKey( 'writes', $stats );
		$this->assertArrayHasKey( 'hit_ratio', $stats );
		$this->assertArrayHasKey( 'total_operations', $stats );
	}

	public function test_cache_key_sanitization(): void {
		Functions\expect( 'wp_cache_set' )
			->once()
			->with( 'test_key_123', \Mockery::any(), \Mockery::any(), \Mockery::any() )
			->andReturn( true );

		$result = $this->cache->set( 'Test_Key_123!@#', $this->test_palette );
		$this->assertTrue( $result );
	}

	public function test_cache_ttl(): void {
		Functions\expect( 'wp_cache_set' )
			->once()
			->with( \Mockery::any(), \Mockery::any(), \Mockery::any(), 7200 )
			->andReturn( true );

		$result = $this->cache->set( 'test_key', $this->test_palette, 7200 );
		$this->assertTrue( $result );
	}

	public function test_stats_calculation(): void {
		Functions\expect( 'wp_cache_get' )
			->times( 4 )
			->andReturnValues(
				array(
					array( 'some' => 'data' ),  // hit
					false,               // miss
					array( 'some' => 'data' ),  // hit
					array( 'some' => 'data' ),   // hit
				)
			);

		// Generate some stats
		$this->cache->has( 'key1' );
		$this->cache->has( 'key2' );
		$this->cache->has( 'key3' );
		$this->cache->has( 'key4' );

		$stats = $this->cache->getStats();

		$this->assertEquals( 3, $stats['hits'] );
		$this->assertEquals( 1, $stats['misses'] );
		$this->assertEquals( 0.75, $stats['hit_ratio'] );
	}
}
