<?php
/**
 * Color Palette Exporter Tests
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Palette;
use GL_Color_Palette_Generator\Color_Palette_Exporter;
use GL_Color_Palette_Generator\Color_Palette_Formatter;

class Test_Color_Palette_Exporter extends Unit_Test_Case {
	protected Color_Palette_Exporter $exporter;
	protected Color_Palette_Formatter $formatter;
	protected Color_Palette $test_palette;
	protected string $temp_dir;

	public function setUp(): void {
		$this->formatter    = new Color_Palette_Formatter();
		$this->exporter     = new Color_Palette_Exporter( $this->formatter );
		$this->test_palette = new Color_Palette(
			array(
				'name'     => 'Test Palette',
				'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
				'metadata' => array(
					'type'       => 'test',
					'created_at' => '2024-03-14T12:00:00Z',
					'version'    => '1.0.0',
				),
			)
		);
		$this->temp_dir     = sys_get_temp_dir();
	}

	public function test_export_json(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'json' );
		$data   = json_decode( $output, true );

		$this->assertIsArray( $data );
		$this->assertEquals( 'Test Palette', $data['name'] );
		$this->assertContains( '#FF0000', $data['colors'] );
		$this->assertArrayHasKey( 'metadata', $data );
	}

	public function test_export_css(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'css' );

		$this->assertStringContainsString( ':root {', $output );
		$this->assertStringContainsString( '--color-0: #FF0000', $output );
		$this->assertStringContainsString( '/* Test Palette */', $output );
	}

	public function test_export_scss(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'scss' );

		$this->assertStringContainsString( '$color-0: #FF0000', $output );
		$this->assertStringContainsString( '// Test Palette', $output );
	}

	public function test_export_less(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'less' );

		$this->assertStringContainsString( '@color-0: #FF0000', $output );
		$this->assertStringContainsString( '// Test Palette', $output );
	}

	public function test_export_ase(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'ase' );

		$this->assertStringStartsWith( 'ASEF', $output );
		$this->assertGreaterThan( 20, strlen( $output ) );
	}

	public function test_export_act(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'act' );

		$this->assertEquals( 768, strlen( $output ) ); // 256 colors * 3 bytes
	}

	public function test_export_gpl(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'gpl' );

		$this->assertStringContainsString( 'GIMP Palette', $output );
		$this->assertStringContainsString( 'Name: Test Palette', $output );
		$this->assertStringContainsString( '255   0   0', $output );
	}

	public function test_export_xml(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'xml' );

		$this->assertStringContainsString( '<?xml', $output );
		$this->assertStringContainsString( '<palette>', $output );
		$this->assertStringContainsString( '<color index="0" hex="#FF0000"', $output );
	}

	public function test_export_yaml(): void {
		$output = $this->exporter->exportPalette( $this->test_palette, 'yaml' );

		$this->assertStringContainsString( 'name: Test Palette', $output );
		$this->assertStringContainsString( 'colors:', $output );
		$this->assertStringContainsString( '- "#FF0000"', $output );
	}

	public function test_export_to_file(): void {
		$filename = $this->temp_dir . '/test_palette.json';
		$result   = $this->exporter->exportToFile( $this->test_palette, $filename, 'json' );

		$this->assertTrue( $result );
		$this->assertFileExists( $filename );
		$this->assertIsReadable( $filename );

		unlink( $filename );
	}

	public function test_get_supported_formats(): void {
		$formats = $this->exporter->get_supported_formats();

		$this->assertIsArray( $formats );
		$this->assertContains( 'json', $formats );
		$this->assertContains( 'css', $formats );
		$this->assertContains( 'scss', $formats );
	}

	public function test_get_format_options(): void {
		$options = $this->exporter->get_format_options( 'json' );

		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'pretty_print', $options );
		$this->assertArrayHasKey( 'include_metadata', $options );
	}

	public function test_get_file_extension(): void {
		$this->assertEquals( 'json', $this->exporter->get_file_extension( 'json' ) );
		$this->assertEquals( 'css', $this->exporter->get_file_extension( 'css' ) );
		$this->assertEquals( 'scss', $this->exporter->get_file_extension( 'scss' ) );
	}

	public function test_validate_options(): void {
		$valid_options = array(
			'pretty_print'     => true,
			'include_metadata' => false,
		);

		$invalid_options = array(
			'invalid_option' => 'value',
		);

		$this->assertTrue( $this->exporter->validate_options( $valid_options, 'json' ) );
		$this->assertFalse( $this->exporter->validate_options( $invalid_options, 'json' ) );
	}

	public function test_invalid_format(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->exporter->exportPalette( $this->test_palette, 'invalid' );
	}

	public function test_css_color_formats(): void {
		$options = array( 'format' => 'rgb' );
		$output  = $this->exporter->exportPalette( $this->test_palette, 'css', $options );
		$this->assertStringContainsString( 'rgb(255, 0, 0)', $output );

		$options['format'] = 'hsl';
		$output            = $this->exporter->exportPalette( $this->test_palette, 'css', $options );
		$this->assertStringContainsString( 'hsl(0, 100%, 50%)', $output );
	}

	public function test_export_empty_palette(): void {
		$empty_palette = new Color_Palette(
			array(
				'name'   => 'Empty Palette',
				'colors' => array(),
			)
		);

		$output = $this->exporter->exportPalette( $empty_palette, 'json' );
		$data   = json_decode( $output, true );

		$this->assertIsArray( $data );
		$this->assertEquals( 'Empty Palette', $data['name'] );
		$this->assertEmpty( $data['colors'] );
	}

	public function test_export_with_custom_options(): void {
		$options = array(
			'prefix'           => '--theme',
			'include_comments' => false,
		);

		$output = $this->exporter->exportPalette( $this->test_palette, 'css', $options );

		$this->assertStringContainsString( '--theme-0:', $output );
		$this->assertStringNotContainsString( '/* Test Palette */', $output );
	}

	public function tearDown(): void {
		// Clean up any temporary files if they exist
		$temp_files = glob( $this->temp_dir . '/test_palette.*' );
		foreach ( $temp_files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}
}
