<?php
/**
 * Tests for Color Palette Exporter Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Exporter;

/**
 * Test Color Palette Exporter Interface implementation
 */
class Test_Color_Palette_Exporter extends Unit_Test_Case {
	/** @var Color_Palette_Exporter */
	private $exporter;

	public function setUp(): void {
		parent::setUp();
		$this->exporter = $this->getMockBuilder( Color_Palette_Exporter::class )
								->getMockForAbstractClass();
	}

	/**
	 * Test that export_to_file creates a file with palette data
	 */
	public function test_export_to_file_creates_file(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$format  = 'json';
		$options = array(
			'path'       => '/exports',
			'formatting' => array( 'pretty' => true ),
		);

		$expected = array(
			'file'       => '/exports/test-palette.json',
			'stats'      => array(
				'size'       => 256,
				'created_at' => '2024-12-08T19:04:25-07:00',
			),
			'validation' => array(
				'valid'  => true,
				'format' => 'valid',
			),
			'metadata'   => array(
				'exported_at' => '2024-12-08T19:04:25-07:00',
				'format'      => 'json',
			),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'export_to_file' )
			->with( $palette, $format, $options )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->export_to_file( $palette, $format, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'file', $result );
		$this->assertArrayHasKey( 'stats', $result );
		$this->assertArrayHasKey( 'validation', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertStringEndsWith( '.json', $result['file'] );
		$this->assertTrue( $result['validation']['valid'] );
	}

	/**
	 * Test that export_to_code generates code in specified language
	 */
	public function test_export_to_code_generates_code(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$language = 'scss';
		$options  = array(
			'format'   => 'variables',
			'comments' => true,
		);

		$expected = array(
			'code'          => '$primary: #FF0000;\n$secondary: #00FF00;',
			'variables'     => array(
				'primary'   => '#FF0000',
				'secondary' => '#00FF00',
			),
			'documentation' => array(
				'description' => 'Color variables for Test Palette',
				'usage'       => '@import "palette.scss";',
			),
			'metadata'      => array(
				'generated_at' => '2024-12-08T19:04:25-07:00',
				'language'     => 'scss',
			),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'export_to_code' )
			->with( $palette, $language, $options )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->export_to_code( $palette, $language, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'code', $result );
		$this->assertArrayHasKey( 'variables', $result );
		$this->assertArrayHasKey( 'documentation', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertStringContainsString( '$primary', $result['code'] );
		$this->assertStringContainsString( '$secondary', $result['code'] );
	}

	/**
	 * Test that export_to_tool creates a tool-specific file
	 */
	public function test_export_to_tool_creates_tool_file(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$tool    = 'sketch';
		$options = array(
			'version'  => '70',
			'swatches' => array( 'format' => 'global' ),
		);

		$expected = array(
			'file'          => '/exports/test-palette.sketch',
			'swatches'      => array(
				'global' => array(
					array(
						'name'  => 'Primary',
						'color' => '#FF0000',
					),
					array(
						'name'  => 'Secondary',
						'color' => '#00FF00',
					),
				),
			),
			'compatibility' => array(
				'version'  => '70+',
				'features' => array( 'global_colors' => true ),
			),
			'metadata'      => array(
				'exported_at' => '2024-12-08T19:04:25-07:00',
				'tool'        => 'sketch',
			),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'export_to_tool' )
			->with( $palette, $tool, $options )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->export_to_tool( $palette, $tool, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'file', $result );
		$this->assertArrayHasKey( 'swatches', $result );
		$this->assertArrayHasKey( 'compatibility', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertStringEndsWith( '.sketch', $result['file'] );
	}

	/**
	 * Test that validate_export checks format and structure
	 */
	public function test_validate_export_checks_format(): void {
		// Arrange
		$export = array(
			'format' => 'json',
			'data'   => array( 'colors' => array( '#FF0000' ) ),
		);

		$rules = array(
			'format'    => array( 'allowed' => array( 'json', 'xml' ) ),
			'structure' => array( 'required' => array( 'colors' ) ),
		);

		$expected = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(),
			'metadata' => array(
				'validated_at'  => '2024-12-08T19:04:25-07:00',
				'rules_applied' => array( 'format', 'structure' ),
			),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'validate_export' )
			->with( $export, $rules )
			->willReturn( $expected );

		// Act
		$result = $this->exporter->validate_export( $export, $rules );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'valid', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertArrayHasKey( 'warnings', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['valid'] );
		$this->assertEmpty( $result['errors'] );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_export_to_file_throws_exception_for_invalid_palette( $palette ): void {
		$format = 'json';

		$this->exporter
			->expects( $this->once() )
			->method( 'export_to_file' )
			->with( $palette, $format, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->export_to_file( $palette, $format, array() );
	}

	/**
	 * @dataProvider invalidFormatProvider
	 */
	public function test_export_to_code_throws_exception_for_invalid_format( $format ): void {
		$palette = array(
			'name'   => 'Test',
			'colors' => array( '#FF0000' ),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'export_to_code' )
			->with( $palette, $format, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->export_to_code( $palette, $format, array() );
	}

	/**
	 * @dataProvider invalidToolProvider
	 */
	public function test_export_to_tool_throws_exception_for_invalid_tool( $tool ): void {
		$palette = array(
			'name'   => 'Test',
			'colors' => array( '#FF0000' ),
		);

		$this->exporter
			->expects( $this->once() )
			->method( 'export_to_tool' )
			->with( $palette, $tool, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->export_to_tool( $palette, $tool, array() );
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

	public function invalidFormatProvider(): array {
		return array(
			'empty format'   => array( '' ),
			'invalid format' => array( 'invalid' ),
			'numeric format' => array( '123' ),
			'null format'    => array( null ),
			'special chars'  => array( 'format@!' ),
		);
	}

	public function invalidToolProvider(): array {
		return array(
			'empty tool'    => array( '' ),
			'invalid tool'  => array( 'invalid' ),
			'numeric tool'  => array( '123' ),
			'null tool'     => array( null ),
			'special chars' => array( 'tool@!' ),
		);
	}
}
