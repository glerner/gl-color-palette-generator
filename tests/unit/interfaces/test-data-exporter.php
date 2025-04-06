<?php
/**
 * Data Exporter Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\DataExporter;

class Test_DataExporter extends Unit_Test_Case {
	private $exporter;

	public function setUp(): void {
		$this->exporter = $this->createMock( DataExporter::class );
	}

	public function test_export_data_returns_string(): void {
		// Arrange
		$data     = array(
			'palettes' => array(
				array(
					'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
					'name'   => 'RGB Primary Colors',
				),
			),
			'metadata' => array(
				'created' => '2024-12-08 18:38:25',
				'version' => '1.0',
			),
		);
		$format   = 'json';
		$options  = array( 'pretty_print' => true );
		$expected = json_encode( $data, JSON_PRETTY_PRINT );

		$this->exporter
			->expects( $this->once() )
			->method( 'export_data' )
			->with( $data, $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->export_data( $data, $format, $options );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_supported_formats_returns_array(): void {
		// Arrange
		$expected = array(
			'json' => array(
				'extension' => '.json',
				'mime_type' => 'application/json',
			),
			'xml'  => array(
				'extension' => '.xml',
				'mime_type' => 'application/xml',
			),
			'csv'  => array(
				'extension' => '.csv',
				'mime_type' => 'text/csv',
			),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'get_supported_formats' )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->get_supported_formats();

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'json', $result );
		$this->assertArrayHasKey( 'xml', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_validate_data_returns_true_for_valid_data(): void {
		// Arrange
		$data = array(
			'colors'   => array( '#FF0000', '#00FF00' ),
			'metadata' => array( 'name' => 'Test Palette' ),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'validate_data' )
			->with( $data )
			->willReturn( true );

		// Act
		$result = $this->exporter->validate_data( $data );

		// Assert
		$this->assertTrue( $result );
	}

	public function test_get_export_options_returns_array(): void {
		// Arrange
		$format   = 'json';
		$expected = array(
			'pretty_print'     => array(
				'type'        => 'boolean',
				'default'     => true,
				'description' => 'Format output with indentation',
			),
			'include_metadata' => array(
				'type'        => 'boolean',
				'default'     => true,
				'description' => 'Include metadata in export',
			),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'get_export_options' )
			->with( $format )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->get_export_options( $format );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'pretty_print', $result );
		$this->assertArrayHasKey( 'include_metadata', $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidDataProvider
	 */
	public function test_export_data_throws_exception_for_invalid_data( $data ): void {
		$this->exporter
			->expects( $this->once() )
			->method( 'export_data' )
			->with( $data, 'json', array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->export_data( $data, 'json', array() );
	}

	/**
	 * @dataProvider invalidFormatProvider
	 */
	public function test_export_data_throws_exception_for_invalid_format( $format ): void {
		$data = array( 'test' => 'data' );

		$this->exporter
			->expects( $this->once() )
			->method( 'export_data' )
			->with( $data, $format, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->export_data( $data, $format, array() );
	}

	/**
	 * @dataProvider invalidOptionsProvider
	 */
	public function test_export_data_throws_exception_for_invalid_options( $options ): void {
		$data = array( 'test' => 'data' );

		$this->exporter
			->expects( $this->once() )
			->method( 'export_data' )
			->with( $data, 'json', $options )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->export_data( $data, 'json', $options );
	}

	public function invalidDataProvider(): array {
		return array(
			'empty array'       => array( array() ),
			'invalid structure' => array( array( 'invalid' => null ) ),
			'non-array input'   => array( 'invalid' ),
			'null input'        => array( null ),
			'numeric input'     => array( 42 ),
		);
	}

	public function invalidFormatProvider(): array {
		return array(
			'empty string'       => array( '' ),
			'unsupported format' => array( 'invalid' ),
			'numeric format'     => array( 42 ),
			'array format'       => array( array() ),
			'null format'        => array( null ),
		);
	}

	public function invalidOptionsProvider(): array {
		return array(
			'invalid option type' => array( array( 'pretty_print' => 'invalid' ) ),
			'unknown option'      => array( array( 'invalid_option' => true ) ),
			'non-array options'   => array( 'invalid' ),
			'null options'        => array( null ),
			'numeric options'     => array( 42 ),
		);
	}
}
