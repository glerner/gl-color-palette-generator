<?php
/**
 * Test Color Palette Renderer
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration\Color_Management
 */

namespace GL_Color_Palette_Generator\Tests\Integration\Color_Management;
use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Renderer;
use GL_Color_Palette_Generator\Color_Management\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

class Test_Color_Palette_Renderer extends Integration_Test_Case {
	private $renderer;
	private $palette;

	public function setUp(): void {
		parent::setUp();
		$this->renderer = new Color_Palette_Renderer();
		$this->palette  = new Color_Palette(
			array(
				'primary'   => '#2C3E50',
				'secondary' => '#E74C3C',
				'tertiary'  => '#3498DB',
				'accent'    => '#2ECC71',
			)
		);
	}

	public function test_render_with_default_options() {
		$output = $this->renderer->render( $this->palette );

		$this->assertIsString( $output );
		$this->assertStringContainsString( 'gl-color-palette', $output );
		$this->assertStringContainsString( '#2C3E50', $output );
	}

	public function test_render_with_ai_descriptions() {
		// Set up mock AI description
		$description = array(
			'colors'        => array(
				'primary' => array(
					'hex'     => '#2C3E50',
					'name'    => 'Midnight Ocean',
					'emotion' => 'Deep trust and stability',
				),
			),
			'palette_story' => 'A professional and modern palette',
		);
		update_option( 'gl_cpg_last_palette_description', $description );

		$output = $this->renderer->render(
			$this->palette,
			array(
				'layout'    => 'cards',
				'show_info' => true,
			)
		);

		$this->assertStringContainsString( 'Midnight Ocean', $output );
		$this->assertStringContainsString( 'Deep trust and stability', $output );
		$this->assertStringContainsString( 'A professional and modern palette', $output );
	}

	public function test_render_with_missing_ai_descriptions() {
		delete_option( 'gl_cpg_last_palette_description' );

		$output = $this->renderer->render(
			$this->palette,
			array(
				'layout'    => 'cards',
				'show_info' => true,
			)
		);

		// Should still render without AI descriptions
		$this->assertIsString( $output );
		$this->assertStringContainsString( '#2C3E50', $output );
	}

	public function test_render_different_layouts() {
		$layouts = array( 'swatches', 'grid', 'cards' );

		foreach ( $layouts as $layout ) {
			$output = $this->renderer->render(
				$this->palette,
				array(
					'layout' => $layout,
				)
			);

			$this->assertIsString( $output );
			$this->assertStringContainsString( "gl-color-palette--$layout", $output );
		}
	}

	public function test_render_palette_info() {
		$description = array(
			'palette_story' => 'A harmonious blend of colors',
		);
		update_option( 'gl_cpg_last_palette_description', $description );

		$output = $this->renderer->render_palette_info( $this->palette );

		$this->assertStringContainsString( 'Palette Story', $output );
		$this->assertStringContainsString( 'A harmonious blend of colors', $output );
	}

	public function test_render_color_info() {
		$reflection = new \ReflectionClass( $this->renderer );
		$method     = $reflection->getMethod( 'render_color_info' );
		$method->setAccessible( true );

		$output = $method->invoke(
			$this->renderer,
			'#2C3E50',
			0,
			$this->palette,
			array(
				'show_names'  => true,
				'show_values' => true,
			)
		);

		$this->assertStringContainsString( 'Primary', $output );
		$this->assertStringContainsString( '#2C3E50', $output );
	}
}
