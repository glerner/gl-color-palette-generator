<?php
/**
 * Palette Manager Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\PaletteManager;

class Test_PaletteManager extends Unit_Test_Case {
	private $manager;

	public function setUp(): void {
		$this->manager = $this->createMock( PaletteManager::class );
	}

	public function test_create_palette_returns_array(): void {
		// Arrange
		$colors   = array( '#FF0000', '#00FF00', '#0000FF' );
		$metadata = array(
			'name'    => 'RGB Primary Colors',
			'created' => '2024-12-08 18:38:25',
		);
		$expected = array(
			'id'       => 'pal_123',
			'colors'   => $colors,
			'metadata' => $metadata,
		);

		$this->manager
			->expects( $this->once() )
			->method( 'create_palette' )
			->with( $colors, $metadata )
			->willReturn( $expected );

		// Act
		$result = $this->manager->create_palette( $colors, $metadata );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_palette_returns_array(): void {
		// Arrange
		$palette_id = 'pal_123';
		$expected   = array(
			'id'       => 'pal_123',
			'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
			'metadata' => array(
				'name'    => 'RGB Primary Colors',
				'created' => '2024-12-08 18:38:25',
			),
		);

		$this->manager
			->expects( $this->once() )
			->method( 'get_palette' )
			->with( $palette_id )
			->willReturn( $expected );

		// Act
		$result = $this->manager->get_palette( $palette_id );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_update_palette_returns_true_on_success(): void {
		// Arrange
		$palette_id = 'pal_123';
		$updates    = array(
			'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
			'metadata' => array( 'name' => 'Updated RGB Colors' ),
		);

		$this->manager
			->expects( $this->once() )
			->method( 'update_palette' )
			->with( $palette_id, $updates )
			->willReturn( true );

		// Act
		$result = $this->manager->update_palette( $palette_id, $updates );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_delete_palette_returns_true_on_success(): void {
		// Arrange
		$palette_id = 'pal_123';

		$this->manager
			->expects( $this->once() )
			->method( 'delete_palette' )
			->with( $palette_id )
			->willReturn( true );

		// Act
		$result = $this->manager->delete_palette( $palette_id );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_list_palettes_returns_array(): void {
		// Arrange
		$filters  = array( 'category' => 'primary' );
		$expected = array(
			array(
				'id'       => 'pal_123',
				'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
				'metadata' => array( 'name' => 'RGB Primary Colors' ),
			),
			array(
				'id'       => 'pal_124',
				'colors'   => array( '#FF0000', '#FFFF00', '#0000FF' ),
				'metadata' => array( 'name' => 'RYB Primary Colors' ),
			),
		);

		$this->manager
			->expects( $this->once() )
			->method( 'list_palettes' )
			->with( $filters )
			->willReturn( $expected );

		// Act
		$result = $this->manager->list_palettes( $filters );

		// Assert
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidColorsProvider
	 */
	public function test_create_palette_throws_exception_for_invalid_colors( $colors ): void {
		$this->manager
			->expects( $this->once() )
			->method( 'create_palette' )
			->with( $colors, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->manager->create_palette( $colors, array() );
	}

	/**
	 * @dataProvider invalidPaletteIdProvider
	 */
	public function test_get_palette_throws_exception_for_invalid_id( $palette_id ): void {
		$this->manager
			->expects( $this->once() )
			->method( 'get_palette' )
			->with( $palette_id )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->manager->get_palette( $palette_id );
	}

	/**
	 * @dataProvider invalidUpdatesProvider
	 */
	public function test_update_palette_throws_exception_for_invalid_updates( $updates ): void {
		$this->manager
			->expects( $this->once() )
			->method( 'update_palette' )
			->with( 'pal_123', $updates )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->manager->update_palette( 'pal_123', $updates );
	}

	public function invalidColorsProvider(): array {
		return array(
			'empty array'        => array( array() ),
			'invalid hex colors' => array( array( '#GG0000', '#00FF00' ) ),
			'mixed formats'      => array( array( '#FF0000', 'rgb(0,255,0)' ) ),
			'non-array input'    => array( '#FF0000' ),
			'null input'         => array( null ),
		);
	}

	public function invalidPaletteIdProvider(): array {
		return array(
			'empty string'   => array( '' ),
			'invalid format' => array( 'invalid_id' ),
			'numeric id'     => array( 42 ),
			'array id'       => array( array() ),
			'null id'        => array( null ),
		);
	}

	public function invalidUpdatesProvider(): array {
		return array(
			'empty array'       => array( array() ),
			'invalid colors'    => array( array( 'colors' => array( 'invalid' ) ) ),
			'non-array input'   => array( 'invalid' ),
			'null input'        => array( null ),
			'invalid structure' => array( array( 'invalid' => null ) ),
		);
	}
}
