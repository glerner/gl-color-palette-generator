<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Formatter;

/**
 * @covers \GL_Color_Palette_Generator\Interfaces\Color_Palette_Formatter
 */
class Test_Color_Palette_Formatter extends Unit_Test_Case {
	private $formatter;

	public function setUp(): void {
		$this->formatter = $this->createMock( Color_Palette_Formatter::class );
	}

	public function test_format_colors_converts_values(): void {
		// Arrange
		$colors  = array( '#FF0000', '#00FF00' );
		$format  = 'rgb';
		$options = array(
			'notation'  => 'object',
			'precision' => 0,
		);

		$expected = array(
			'formatted'   => array(
				array(
					'r' => 255,
					'g' => 0,
					'b' => 0,
				),
				array(
					'r' => 0,
					'g' => 255,
					'b' => 0,
				),
			),
			'original'    => array( '#FF0000', '#00FF00' ),
			'conversions' => array(
				array(
					'from' => 'hex',
					'to'   => 'rgb',
				),
				array( 'success' => true ),
			),
			'metadata'    => array(
				'formatted_at' => '2024-01-20T12:00:00Z',
				'format'       => 'rgb',
			),
		);

		$this->formatter
			->expects( $this->once() )
			->method( 'format_colors' )
			->with( $colors, $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->formatter->format_colors( $colors, $format, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'formatted', $result );
		$this->assertArrayHasKey( 'original', $result );
		$this->assertArrayHasKey( 'conversions', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_format_structure_organizes_data(): void {
		// Arrange
		$palette = array(
			'name'     => 'Test Palette',
			'colors'   => array( '#FF0000', '#00FF00' ),
			'metadata' => array( 'created' => '2024-01-20' ),
		);

		$options = array(
			'structure' => 'nested',
			'fields'    => array( 'name', 'colors' ),
		);

		$expected = array(
			'formatted' => array(
				'palette' => array(
					'name'   => 'Test Palette',
					'colors' => array(
						array( 'hex' => '#FF0000' ),
						array( 'hex' => '#00FF00' ),
					),
				),
			),
			'structure' => array(
				'type'  => 'nested',
				'depth' => 2,
			),
			'mapping'   => array(
				'name'   => 'string',
				'colors' => 'array',
			),
			'metadata'  => array(
				'formatted_at' => '2024-01-20T12:00:00Z',
				'structure'    => 'nested',
			),
		);

		$this->formatter
			->expects( $this->once() )
			->method( 'format_structure' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->formatter->format_structure( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'formatted', $result );
		$this->assertArrayHasKey( 'structure', $result );
		$this->assertArrayHasKey( 'mapping', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_format_metadata_processes_fields(): void {
		// Arrange
		$metadata = array(
			'created'  => '2024-01-20',
			'modified' => '2024-01-21',
			'version'  => 1,
		);

		$options = array(
			'fields'     => array( 'created', 'modified' ),
			'formatting' => array( 'dates' => 'Y-m-d' ),
		);

		$expected = array(
			'formatted'  => array(
				'created'  => '2024-01-20',
				'modified' => '2024-01-21',
			),
			'validation' => array(
				'valid'  => true,
				'errors' => array(),
			),
			'defaults'   => array(
				'applied' => false,
				'fields'  => array(),
			),
			'metadata'   => array(
				'formatted_at' => '2024-01-20T12:00:00Z',
				'fields'       => array( 'created', 'modified' ),
			),
		);

		$this->formatter
			->expects( $this->once() )
			->method( 'format_metadata' )
			->with( $metadata, $options )
			->willReturn( $expected );

		// Act
		$result = $this->formatter->format_metadata( $metadata, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'formatted', $result );
		$this->assertArrayHasKey( 'validation', $result );
		$this->assertArrayHasKey( 'defaults', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_format_output_generates_formatted_data(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$format  = 'json';
		$options = array(
			'structure'  => 'flat',
			'formatting' => array( 'pretty' => true ),
		);

		$expected = array(
			'output'   => '{"name":"Test Palette","colors":["#FF0000","#00FF00"]}',
			'format'   => array(
				'type'     => 'json',
				'encoding' => 'utf-8',
			),
			'applied'  => array(
				'structure'  => 'flat',
				'formatting' => array( 'pretty' => true ),
			),
			'metadata' => array(
				'formatted_at' => '2024-01-20T12:00:00Z',
				'format'       => 'json',
			),
		);

		$this->formatter
			->expects( $this->once() )
			->method( 'format_output' )
			->with( $palette, $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->formatter->format_output( $palette, $format, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertArrayHasKey( 'format', $result );
		$this->assertArrayHasKey( 'applied', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * @dataProvider invalidColorsProvider
	 */
	public function test_format_colors_validates_input( array $colors ): void {
		$format = 'rgb';

		$this->formatter
			->expects( $this->once() )
			->method( 'format_colors' )
			->with( $colors, $format )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->formatter->format_colors( $colors, $format );
	}

	public function invalidColorsProvider(): array {
		return array(
			'empty_colors'  => array( array() ),
			'invalid_hex'   => array( array( '#XYZ' ) ),
			'mixed_formats' => array( array( '#FF0000', 'rgb(0,0,0)' ) ),
			'invalid_type'  => array( array( 'not_a_color' ) ),
		);
	}

	/**
	 * @dataProvider invalidFormatProvider
	 */
	public function test_format_output_validates_format( string $format ): void {
		$palette = array(
			'name'   => 'Test',
			'colors' => array( '#FF0000' ),
		);

		$this->formatter
			->expects( $this->once() )
			->method( 'format_output' )
			->with( $palette, $format )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->formatter->format_output( $palette, $format );
	}

	public function invalidFormatProvider(): array {
		return array(
			'empty_format'   => array( '' ),
			'invalid_format' => array( 'invalid' ),
			'unknown_format' => array( 'binary' ),
			'numeric_format' => array( '123' ),
		);
	}
}
