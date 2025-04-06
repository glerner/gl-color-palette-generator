<?php
/**
 * Color Palette Importer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Importer;

/**
 * Test class for Color_Palette_Importer interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\Color_Palette_Importer
 */
class Test_Color_Palette_Importer extends Unit_Test_Case {
	private $importer;

	public function setUp(): void {
		$this->importer = $this->createMock( Color_Palette_Importer::class );
	}

	public function test_import_from_file_loads_palette(): void {
		// Arrange
		$file_path = '/imports/palette.json';
		$options   = array(
			'format'     => 'json',
			'validation' => array( 'strict' => true ),
		);

		$expected = array(
			'palette'         => array(
				'name'   => 'Imported Palette',
				'colors' => array( '#FF0000', '#00FF00' ),
			),
			'validation'      => array(
				'valid'  => true,
				'errors' => array(),
			),
			'transformations' => array(
				'applied' => array( 'format_colors', 'normalize_names' ),
			),
			'metadata'        => array(
				'imported_at' => '2024-01-20T12:00:00Z',
				'source'      => 'file',
			),
		);

		$this->importer
			->expects( $this->once() )
			->method( 'import_from_file' )
			->with( $file_path, $options )
			->willReturn( $expected );

		// Act
		$result = $this->importer->import_from_file( $file_path, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'palette', $result );
		$this->assertArrayHasKey( 'validation', $result );
		$this->assertArrayHasKey( 'transformations', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_import_from_url_fetches_palette(): void {
		// Arrange
		$url     = 'https://api.example.com/palette';
		$options = array(
			'headers' => array( 'Accept' => 'application/json' ),
			'auth'    => array( 'token' => 'abc123' ),
		);

		$expected = array(
			'palette'    => array(
				'name'   => 'Remote Palette',
				'colors' => array( '#FF0000', '#00FF00' ),
			),
			'source'     => array(
				'url'  => 'https://api.example.com/palette',
				'type' => 'api',
			),
			'validation' => array(
				'valid'  => true,
				'errors' => array(),
			),
			'metadata'   => array(
				'imported_at' => '2024-01-20T12:00:00Z',
				'source'      => 'url',
			),
		);

		$this->importer
			->expects( $this->once() )
			->method( 'import_from_url' )
			->with( $url, $options )
			->willReturn( $expected );

		// Act
		$result = $this->importer->import_from_url( $url, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'palette', $result );
		$this->assertArrayHasKey( 'source', $result );
		$this->assertArrayHasKey( 'validation', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_import_from_tool_extracts_palette(): void {
		// Arrange
		$tool_file = '/imports/design.sketch';
		$tool      = 'sketch';
		$options   = array(
			'version'    => '70',
			'extraction' => array( 'scope' => 'global' ),
		);

		$expected = array(
			'palette'    => array(
				'name'   => 'Sketch Palette',
				'colors' => array( '#FF0000', '#00FF00' ),
			),
			'assets'     => array(
				'swatches' => array(
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
			'validation' => array(
				'valid'  => true,
				'errors' => array(),
			),
			'metadata'   => array(
				'imported_at' => '2024-01-20T12:00:00Z',
				'tool'        => 'sketch',
			),
		);

		$this->importer
			->expects( $this->once() )
			->method( 'import_from_tool' )
			->with( $tool_file, $tool, $options )
			->willReturn( $expected );

		// Act
		$result = $this->importer->import_from_tool( $tool_file, $tool, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'palette', $result );
		$this->assertArrayHasKey( 'assets', $result );
		$this->assertArrayHasKey( 'validation', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	public function test_validate_import_checks_data(): void {
		// Arrange
		$import_data = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$rules = array(
			'schema'      => array( 'required' => array( 'name', 'colors' ) ),
			'constraints' => array( 'min_colors' => 1 ),
		);

		$expected = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(),
			'metadata' => array(
				'validated_at'  => '2024-01-20T12:00:00Z',
				'rules_applied' => array( 'schema', 'constraints' ),
			),
		);

		$this->importer
			->expects( $this->once() )
			->method( 'validate_import' )
			->with( $import_data, $rules )
			->willReturn( $expected );

		// Act
		$result = $this->importer->validate_import( $import_data, $rules );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'valid', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertArrayHasKey( 'warnings', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['valid'] );
	}

	/**
	 * @dataProvider invalidFilePathProvider
	 */
	public function test_import_from_file_validates_path( string $file_path ): void {
		$this->importer
			->expects( $this->once() )
			->method( 'import_from_file' )
			->with( $file_path )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->importer->import_from_file( $file_path );
	}

	public function invalidFilePathProvider(): array {
		return array(
			'empty_path'        => array( '' ),
			'invalid_extension' => array( '/path/file.xyz' ),
			'directory_path'    => array( '/path/directory/' ),
			'nonexistent'       => array( '/path/nonexistent.json' ),
		);
	}

	/**
	 * @dataProvider invalidUrlProvider
	 */
	public function test_import_from_url_validates_url( string $url ): void {
		$this->importer
			->expects( $this->once() )
			->method( 'import_from_url' )
			->with( $url )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->importer->import_from_url( $url );
	}

	public function invalidUrlProvider(): array {
		return array(
			'empty_url'        => array( '' ),
			'invalid_protocol' => array( 'ftp://example.com' ),
			'malformed_url'    => array( 'not_a_url' ),
			'missing_host'     => array( 'https://' ),
		);
	}
}
