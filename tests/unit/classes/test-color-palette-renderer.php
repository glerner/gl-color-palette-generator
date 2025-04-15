<?php
/**
 * Color Palette Renderer Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Classes
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Palette;
use GL_Color_Palette_Generator\Color_Palette_Renderer;
use GL_Color_Palette_Generator\Color_Palette_Formatter;

/**
 * Test class for Color_Palette_Renderer core functionality
 *
 * @covers GL_Color_Palette_Generator\Color_Palette_Renderer
 */
class Test_Color_Palette_Renderer extends Unit_Test_Case {
	private Color_Palette_Renderer $renderer;
	private Color_Palette_Formatter $formatter;
	private Color_Palette $test_palette;

	public function setUp(): void {
		$this->formatter    = new Color_Palette_Formatter();
		$this->renderer     = new Color_Palette_Renderer( $this->formatter );
		$this->test_palette = new Color_Palette(
			array(
				'name'     => 'Test Palette',
				'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
				'metadata' => array( 'type' => 'test' ),
			)
		);
	}

	public function test_render_html(): void {
		$output = $this->renderer->renderPalette( $this->test_palette, 'html' );

		$this->assertStringContainsString( 'class="color-palette"', $output );
		$this->assertStringContainsString( 'class="color-swatch"', $output );
		$this->assertStringContainsString( '#FF0000', $output );
		$this->assertStringContainsString( '#00FF00', $output );
		$this->assertStringContainsString( '#0000FF', $output );
	}

	public function test_render_svg(): void {
		$output = $this->renderer->renderPalette( $this->test_palette, 'svg' );

		$this->assertStringContainsString( '<svg', $output );
		$this->assertStringContainsString( '</svg>', $output );
		$this->assertStringContainsString( 'fill="#FF0000"', $output );
		$this->assertStringContainsString( 'fill="#00FF00"', $output );
		$this->assertStringContainsString( 'fill="#0000FF"', $output );
	}

	public function test_render_text(): void {
		$output = $this->renderer->renderPalette( $this->test_palette, 'text' );

		$this->assertStringContainsString( '#FF0000', $output );
		$this->assertStringContainsString( '#00FF00', $output );
		$this->assertStringContainsString( '#0000FF', $output );
		$this->assertStringContainsString( 'Test Palette', $output );
	}

	public function test_render_json(): void {
		$output = $this->renderer->renderPalette( $this->test_palette, 'json' );
		$data   = json_decode( $output, true );

		$this->assertIsArray( $data );
		$this->assertEquals( 'Test Palette', $data['name'] );
		$this->assertContains( '#FF0000', $data['colors'] );
		$this->assertContains( '#00FF00', $data['colors'] );
		$this->assertContains( '#0000FF', $data['colors'] );
	}

	public function test_render_html_swatch(): void {
		$output = $this->renderer->renderSwatch( '#FF0000', 'html' );

		$this->assertStringContainsString( 'class="color-swatch"', $output );
		$this->assertStringContainsString( 'background-color: #FF0000', $output );
	}

	public function test_render_svg_swatch(): void {
		$output = $this->renderer->renderSwatch( '#FF0000', 'svg', array( 'x' => 0 ) );

		$this->assertStringContainsString( '<rect', $output );
		$this->assertStringContainsString( 'fill="#FF0000"', $output );
	}

	public function test_render_color_info(): void {
		$output = $this->renderer->renderColorInfo( '#FF0000' );

		$this->assertStringContainsString( 'HEX: #FF0000', $output );
		$this->assertStringContainsString( 'RGB: 255, 0, 0', $output );
		$this->assertStringContainsString( 'HSL:', $output );
	}

	public function test_get_supported_formats(): void {
		$formats = $this->renderer->get_supported_formats();

		$this->assertIsArray( $formats );
		$this->assertContains( 'html', $formats );
		$this->assertContains( 'svg', $formats );
		$this->assertContains( 'text', $formats );
		$this->assertContains( 'json', $formats );
	}

	public function test_get_format_options(): void {
		$options = $this->renderer->get_format_options( 'html' );

		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'swatch_size', $options );
		$this->assertArrayHasKey( 'border_radius', $options );
		$this->assertArrayHasKey( 'spacing', $options );
	}

	public function test_validate_options(): void {
		$valid_options = array(
			'swatch_size'   => '60px',
			'border_radius' => '8px',
		);

		$invalid_options = array(
			'invalid_option' => 'value',
		);

		$this->assertTrue( $this->renderer->validate_options( $valid_options, 'html' ) );
		$this->assertFalse( $this->renderer->validate_options( $invalid_options, 'html' ) );
	}

	public function test_invalid_format(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->renderer->renderPalette( $this->test_palette, 'invalid' );
	}

	public function test_html_custom_options(): void {
		$options = array(
			'swatch_size'   => '100px',
			'border_radius' => '10px',
			'spacing'       => '20px',
		);

		$output = $this->renderer->renderPalette( $this->test_palette, 'html', $options );

		$this->assertStringContainsString( 'width: 100px', $output );
		$this->assertStringContainsString( 'border-radius: 10px', $output );
		$this->assertStringContainsString( 'gap: 20px', $output );
	}

	public function test_svg_custom_options(): void {
		$options = array(
			'width'       => 800,
			'height'      => 200,
			'swatch_size' => 120,
		);

		$output = $this->renderer->renderPalette( $this->test_palette, 'svg', $options );

		$this->assertStringContainsString( 'width="800"', $output );
		$this->assertStringContainsString( 'height="200"', $output );
		$this->assertStringContainsString( 'width="120"', $output );
	}

	public function test_text_custom_separator(): void {
		$options = array(
			'separator' => ' | ',
			'show_name' => true,
			'show_info' => true,
		);

		$output = $this->renderer->renderPalette( $this->test_palette, 'text', $options );

		$this->assertStringContainsString( ' | ', $output );
	}

	public function test_json_pretty_print(): void {
		$options = array(
			'pretty_print'     => true,
			'include_metadata' => true,
		);

		$output = $this->renderer->renderPalette( $this->test_palette, 'json', $options );

		$this->assertStringContainsString( "\n", $output );
		$this->assertStringContainsString( '    ', $output );
	}

	public function test_render_with_empty_palette(): void {
		$empty_palette = new Color_Palette(
			array(
				'name'   => 'Empty Palette',
				'colors' => array(),
			)
		);

		$html = $this->renderer->renderPalette( $empty_palette, 'html' );
		$svg  = $this->renderer->renderPalette( $empty_palette, 'svg' );
		$text = $this->renderer->renderPalette( $empty_palette, 'text' );
		$json = $this->renderer->renderPalette( $empty_palette, 'json' );

		$this->assertNotEmpty( $html );
		$this->assertNotEmpty( $svg );
		$this->assertNotEmpty( $text );
		$this->assertNotEmpty( $json );
	}

	public function test_color_info_custom_separator(): void {
		$output = $this->renderer->renderColorInfo( '#FF0000', array( 'separator' => ' | ' ) );

		$this->assertStringContainsString( ' | ', $output );
	}
}
