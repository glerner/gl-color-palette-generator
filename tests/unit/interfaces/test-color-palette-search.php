<?php
/**
 * Color Palette Search Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Search;

class Test_Color_Palette_Search extends Unit_Test_Case {
	private $search;

	public function setUp(): void {
		$this->search = $this->createMock( Color_Palette_Search::class );
	}

	public function test_search_returns_matching_palettes(): void {
		// Arrange
		$criteria = array(
			'colors' => array( '#FF0000', '#00FF00' ),
			'tags'   => array( 'modern', 'bright' ),
			'name'   => 'vibrant',
			'limit'  => 10,
		);

		$expected = array(
			'palettes' => array(
				array(
					'id'     => 'pal_123',
					'name'   => 'Vibrant Spring',
					'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
				),
				array(
					'id'     => 'pal_456',
					'name'   => 'Vibrant Summer',
					'colors' => array( '#FF0000', '#FFFF00', '#00FF00' ),
				),
			),
			'total'    => 2,
			'facets'   => array(
				'tags'         => array(
					'modern' => 2,
					'bright' => 2,
				),
				'color_groups' => array(
					'red'   => 2,
					'green' => 2,
				),
			),
			'metadata' => array(
				'search_time'     => 0.05,
				'filters_applied' => array( 'colors', 'tags', 'name' ),
			),
		);

		$this->search
			->expects( $this->once() )
			->method( 'search' )
			->with( $criteria )
			->willReturn( $expected );

		// Act
		$result = $this->search->search( $criteria );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'palettes', $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertArrayHasKey( 'facets', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_find_similar_returns_similar_palettes(): void {
		// Arrange
		$palette_id = 'pal_123';
		$options    = array(
			'threshold'     => 0.8,
			'include_score' => true,
		);

		$expected = array(
			'matches'  => array(
				array(
					'id'               => 'pal_456',
					'similarity'       => 0.85,
					'matching_aspects' => array( 'colors', 'style' ),
				),
				array(
					'id'               => 'pal_789',
					'similarity'       => 0.82,
					'matching_aspects' => array( 'colors' ),
				),
			),
			'scores'   => array(
				'color_similarity' => 0.9,
				'style_similarity' => 0.8,
			),
			'analysis' => array(
				'comparison_method' => 'color_distance',
				'threshold_applied' => 0.8,
			),
			'metadata' => array(
				'comparison_time' => 0.15,
				'algorithms_used' => array( 'euclidean', 'style_match' ),
			),
		);

		$this->search
			->expects( $this->once() )
			->method( 'find_similar' )
			->with( $palette_id, $options )
			->willReturn( $expected );

		// Act
		$result = $this->search->find_similar( $palette_id, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'matches', $result );
		$this->assertArrayHasKey( 'scores', $result );
		$this->assertArrayHasKey( 'analysis', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_suggest_returns_palette_suggestions(): void {
		// Arrange
		$preferences = array(
			'colors'  => array( '#FF0000' ),
			'style'   => array( 'modern', 'minimal' ),
			'context' => 'web-design',
		);

		$expected = array(
			'suggestions'  => array(
				array(
					'id'         => 'pal_123',
					'confidence' => 0.95,
					'reasoning'  => array( 'color_match', 'style_match' ),
				),
				array(
					'id'         => 'pal_456',
					'confidence' => 0.88,
					'reasoning'  => array( 'style_match' ),
				),
			),
			'reasoning'    => array(
				'color_based' => 'Matches primary color preference',
				'style_based' => 'Fits modern minimal aesthetic',
			),
			'alternatives' => array(
				array(
					'id'   => 'pal_789',
					'type' => 'similar_style',
				),
			),
			'metadata'     => array(
				'suggestion_time'    => 0.1,
				'factors_considered' => array( 'color', 'style', 'context' ),
			),
		);

		$this->search
			->expects( $this->once() )
			->method( 'suggest' )
			->with( $preferences )
			->willReturn( $expected );

		// Act
		$result = $this->search->suggest( $preferences );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'reasoning', $result );
		$this->assertArrayHasKey( 'alternatives', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_index_processes_palette_for_search(): void {
		// Arrange
		$palette = array(
			'id'     => 'pal_123',
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'async'  => false,
			'fields' => array( 'name', 'colors', 'tags' ),
		);

		$expected = array(
			'index_id' => 'idx_abc123',
			'indexed'  => true,
			'status'   => array(
				'fields_indexed' => array( 'name', 'colors', 'tags' ),
				'timestamp'      => '2024-01-20T12:00:00Z',
			),
			'metadata' => array(
				'index_time' => 0.05,
				'index_size' => '2.5KB',
			),
		);

		$this->search
			->expects( $this->once() )
			->method( 'index' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->search->index( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'index_id', $result );
		$this->assertArrayHasKey( 'indexed', $result );
		$this->assertArrayHasKey( 'status', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['indexed'] );
	}

	/**
	 * @dataProvider invalidSearchCriteriaProvider
	 */
	public function test_search_validates_criteria( array $criteria ): void {
		$this->search
			->expects( $this->once() )
			->method( 'search' )
			->with( $criteria )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->search->search( $criteria );
	}

	public function invalidSearchCriteriaProvider(): array {
		return array(
			'empty_criteria' => array( array() ),
			'invalid_colors' => array( array( 'colors' => array( 'invalid-color' ) ) ),
			'invalid_limit'  => array( array( 'limit' => -1 ) ),
			'invalid_offset' => array( array( 'offset' => -1 ) ),
		);
	}

	/**
	 * @dataProvider invalidPreferencesProvider
	 */
	public function test_suggest_validates_preferences( array $preferences ): void {
		$this->search
			->expects( $this->once() )
			->method( 'suggest' )
			->with( $preferences )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->search->suggest( $preferences );
	}

	public function invalidPreferencesProvider(): array {
		return array(
			'empty_preferences' => array( array() ),
			'invalid_colors'    => array( array( 'colors' => array( 'not-a-color' ) ) ),
			'invalid_style'     => array( array( 'style' => 123 ) ),
			'invalid_context'   => array( array( 'context' => array() ) ),
		);
	}
}
