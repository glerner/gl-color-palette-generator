<?php
/**
 * Color Palette History Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_History;

class Test_Color_Palette_History extends Unit_Test_Case {
	private $history;

	public function setUp(): void {
		$this->history = $this->createMock( Color_Palette_History::class );
	}

	/**
	 * Test that record_version creates a new version
	 */
	public function test_record_version_creates_version(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'type'    => 'minor',
			'author'  => 'test_user',
			'changes' => array( 'Added new color' ),
		);

		$expected = array(
			'version_id'   => 2,
			'version_info' => array(
				'number'     => '1.1',
				'type'       => 'minor',
				'created_at' => '2024-12-08T19:04:25-07:00',
			),
			'diff'         => array(
				'added'    => array( '#00FF00' ),
				'removed'  => array(),
				'modified' => array(),
			),
			'metadata'     => array(
				'author'  => 'test_user',
				'comment' => 'Added new color',
			),
		);

		$this->history
			->expects( $this->once() )
			->method( 'record_version' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->history->record_version( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'version_id', $result );
		$this->assertArrayHasKey( 'version_info', $result );
		$this->assertArrayHasKey( 'diff', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * Test that get_history retrieves version history
	 */
	public function test_get_history_retrieves_versions(): void {
		// Arrange
		$palette_id = 123;
		$options    = array(
			'limit' => 10,
			'sort'  => array( 'created_at' => 'DESC' ),
		);

		$expected = array(
			'versions'   => array(
				array(
					'id'         => 2,
					'number'     => '1.1',
					'created_at' => '2024-12-08T19:04:25-07:00',
				),
				array(
					'id'         => 1,
					'number'     => '1.0',
					'created_at' => '2024-12-08T18:04:25-07:00',
				),
			),
			'timeline'   => array(
				'2024-12-08 19:04:25' => array(
					'version' => '1.1',
					'changes' => 1,
				),
				'2024-12-08 18:04:25' => array(
					'version' => '1.0',
					'changes' => 3,
				),
			),
			'statistics' => array(
				'total_versions' => 2,
				'total_changes'  => 4,
			),
			'metadata'   => array(
				'retrieved_at'   => '2024-12-08T19:04:25-07:00',
				'filter_applied' => false,
			),
		);

		$this->history
			->expects( $this->once() )
			->method( 'get_history' )
			->with( $palette_id, $options )
			->willReturn( $expected );

		// Act
		$result = $this->history->get_history( $palette_id, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'versions', $result );
		$this->assertArrayHasKey( 'timeline', $result );
		$this->assertArrayHasKey( 'statistics', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * Test that restore_version reverts to a previous version
	 */
	public function test_restore_version_reverts_to_previous(): void {
		// Arrange
		$palette_id = 123;
		$version_id = 1;
		$options    = array(
			'create_backup' => true,
			'validate'      => true,
		);

		$expected = array(
			'success'  => true,
			'restored' => array(
				'version' => '1.0',
				'palette' => array( '#FF0000' ),
			),
			'backup'   => array(
				'created'    => true,
				'version_id' => 3,
			),
			'metadata' => array(
				'restored_at' => '2024-12-08T19:04:25-07:00',
				'restored_by' => 'system',
			),
		);

		$this->history
			->expects( $this->once() )
			->method( 'restore_version' )
			->with( $palette_id, $version_id, $options )
			->willReturn( $expected );

		// Act
		$result = $this->history->restore_version( $palette_id, $version_id, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'restored', $result );
		$this->assertArrayHasKey( 'backup', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['success'] );
	}

	/**
	 * Test that compare_versions shows differences between versions
	 */
	public function test_compare_versions_shows_differences(): void {
		// Arrange
		$palette_id = 123;
		$version_1  = 1;
		$version_2  = 2;
		$options    = array(
			'fields' => array( 'colors' ),
			'format' => 'detailed',
		);

		$expected = array(
			'differences' => array(
				'colors' => array(
					'added'    => array( '#00FF00' ),
					'removed'  => array(),
					'modified' => array(),
				),
			),
			'summary'     => array(
				'total_changes' => 1,
				'change_type'   => 'addition',
			),
			'visualize'   => array(
				'before' => array( '#FF0000' ),
				'after'  => array( '#FF0000', '#00FF00' ),
			),
			'metadata'    => array(
				'compared_at'     => '2024-12-08T19:04:25-07:00',
				'comparison_type' => 'detailed',
			),
		);

		$this->history
			->expects( $this->once() )
			->method( 'compare_versions' )
			->with( $palette_id, $version_1, $version_2, $options )
			->willReturn( $expected );

		// Act
		$result = $this->history->compare_versions( $palette_id, $version_1, $version_2, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'differences', $result );
		$this->assertArrayHasKey( 'summary', $result );
		$this->assertArrayHasKey( 'visualize', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_record_version_throws_exception_for_invalid_palette( $palette ): void {
		$this->history
			->expects( $this->once() )
			->method( 'record_version' )
			->with( $palette, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->history->record_version( $palette, array() );
	}

	/**
	 * @dataProvider invalidVersionProvider
	 */
	public function test_restore_version_throws_exception_for_invalid_version( $version_id ): void {
		$palette_id = 123;

		$this->history
			->expects( $this->once() )
			->method( 'restore_version' )
			->with( $palette_id, $version_id, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->history->restore_version( $palette_id, $version_id, array() );
	}

	public function invalidPaletteProvider(): array {
		return array(
			'empty array'     => array( array() ),
			'missing colors'  => array( array( 'name' => 'Test' ) ),
			'invalid colors'  => array(
				array(
					'name'   => 'Test',
					'colors' => array( 'invalid' ),
				),
			),
			'non-array input' => array( 'invalid' ),
			'null input'      => array( null ),
		);
	}

	public function invalidVersionProvider(): array {
		return array(
			'negative version'    => array( -1 ),
			'zero version'        => array( 0 ),
			'non-numeric version' => array( 'invalid' ),
			'null version'        => array( null ),
			'float version'       => array( 1.5 ),
		);
	}
}
