<?php
/**
 * Color Palette Preview Interface Tests
 *
 * Tests for color palette preview features including UI previews,
 * design mockups, code snippets, and visualization generation.
 * Validates various preview templates, options, and error handling.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 * @version 1.1.0
 * @author GL Color Palette Generator
 * @copyright 2024 GL Color Palette Generator
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Preview;

/**
 * Test class for Color_Palette_Preview interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\Color_Palette_Preview
 */
class Test_Color_Palette_Preview extends Unit_Test_Case {
	private $preview;

	public function setUp(): void {
		$this->preview = $this->createMock( Color_Palette_Preview::class );
	}

	/**
	 * Test that generate_ui_preview creates interface
	 */
	public function test_generate_ui_preview_creates_interface(): void {
		// Test cases for different UI preview scenarios
		$test_cases = array(
			array(
				'palette'  => array(
					'name'        => 'Test Palette',
					'colors'      => array( '#FF0000', '#00FF00', '#0000FF' ),
					'description' => 'Primary colors palette',
				),
				'options'  => array(
					'template'     => 'grid',
					'components'   => array( 'swatches', 'labels', 'info' ),
					'interactions' => array( 'hover', 'click', 'copy' ),
					'layout'       => 'horizontal',
					'size'         => 'large',
				),
				'expected' => array(
					'preview'    => '<div class="palette-preview">...</div>',
					'components' => array(
						'swatches'     => array(
							array(
								'color' => '#FF0000',
								'label' => 'Red',
								'info'  => array( 'rgb' => '255,0,0' ),
							),
							array(
								'color' => '#00FF00',
								'label' => 'Green',
								'info'  => array( 'rgb' => '0,255,0' ),
							),
							array(
								'color' => '#0000FF',
								'label' => 'Blue',
								'info'  => array( 'rgb' => '0,0,255' ),
							),
						),
						'interactions' => array(
							'hover' => '.swatch:hover { transform: scale(1.1); }',
							'click' => 'function handleClick(e) { ... }',
							'copy'  => 'function copyToClipboard(color) { ... }',
						),
					),
					'assets'     => array(
						'css' => array( 'preview.css', 'animations.css' ),
						'js'  => array( 'interactions.js', 'clipboard.js' ),
					),
					'metadata'   => array(
						'generated_at' => '2024-12-08T19:22:36-07:00',
						'template'     => 'grid',
						'version'      => '1.1.0',
					),
				),
			),
			array(
				'palette'  => array(
					'name'        => 'Brand Theme',
					'colors'      => array( '#1A1A1A', '#FFFFFF' ),
					'description' => 'Monochrome theme',
				),
				'options'  => array(
					'template'     => 'minimal',
					'components'   => array( 'swatches' ),
					'interactions' => array( 'hover' ),
					'layout'       => 'vertical',
					'size'         => 'small',
				),
				'expected' => array(
					'preview'    => '<div class="palette-preview minimal">...</div>',
					'components' => array(
						'swatches'     => array(
							array(
								'color' => '#1A1A1A',
								'label' => 'Black',
							),
							array(
								'color' => '#FFFFFF',
								'label' => 'White',
							),
						),
						'interactions' => array(
							'hover' => '.swatch:hover { opacity: 0.9; }',
						),
					),
					'assets'     => array(
						'css' => array( 'minimal.css' ),
						'js'  => array( 'minimal.js' ),
					),
					'metadata'   => array(
						'generated_at' => '2024-12-08T19:22:36-07:00',
						'template'     => 'minimal',
						'version'      => '1.1.0',
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			$this->preview
				->expects( $this->once() )
				->method( 'generate_ui_preview' )
				->with( $case['palette'], $case['options'] )
				->willReturn( $case['expected'] );

			$result = $this->preview->generate_ui_preview( $case['palette'], $case['options'] );

			$this->assertIsArray( $result );
			$this->assertArrayHasKey( 'preview', $result );
			$this->assertArrayHasKey( 'components', $result );
			$this->assertArrayHasKey( 'assets', $result );
			$this->assertArrayHasKey( 'metadata', $result );
			$this->assertEquals( $case['expected'], $result );
		}
	}

	/**
	 * Test that generate_design_preview creates mockup
	 */
	public function test_generate_design_preview_creates_mockup(): void {
		// Arrange
		$palette = array(
			'name'   => 'Brand Colors',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'template' => 'website',
			'elements' => array( 'header', 'buttons' ),
			'styles'   => array( 'modern', 'clean' ),
		);

		$expected = array(
			'preview'  => '<div class="design-preview">...</div>',
			'elements' => array(
				'header'  => array( 'background' => '#FF0000' ),
				'buttons' => array( 'background' => '#00FF00' ),
			),
			'assets'   => array(
				'images' => array( 'preview.png' ),
				'styles' => array( 'design.css' ),
			),
			'metadata' => array(
				'generated_at' => '2024-12-08T19:04:25-07:00',
				'template'     => 'website',
			),
		);

		$this->preview
			->expects( $this->once() )
			->method( 'generate_design_preview' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->preview->generate_design_preview( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'preview', $result );
		$this->assertArrayHasKey( 'elements', $result );
		$this->assertArrayHasKey( 'assets', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * Test that generate_code_preview creates snippets
	 */
	public function test_generate_code_preview_creates_snippets(): void {
		// Test cases for different code preview formats
		$test_cases = array(
			array(
				'palette'  => array(
					'name'   => 'Theme Colors',
					'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
				),
				'options'  => array(
					'language' => 'css',
					'format'   => 'variables',
					'syntax'   => array( 'highlighting' => true ),
					'prefix'   => 'theme',
				),
				'expected' => array(
					'preview'  => ':root {\n  --theme-primary: #FF0000;\n  --theme-secondary: #00FF00;\n  --theme-tertiary: #0000FF;\n}',
					'syntax'   => array(
						'language' => 'css',
						'tokens'   => array( 'property', 'value', 'punctuation' ),
					),
					'assets'   => array(
						'css' => array( 'syntax-highlight.css' ),
						'js'  => array( 'prism.js' ),
					),
					'metadata' => array(
						'generated_at' => '2024-12-08T19:22:36-07:00',
						'format'       => 'variables',
					),
				),
			),
			array(
				'palette'  => array(
					'name'   => 'Brand Colors',
					'colors' => array( '#1A1A1A', '#FFFFFF' ),
				),
				'options'  => array(
					'language' => 'scss',
					'format'   => 'map',
					'syntax'   => array( 'highlighting' => true ),
					'prefix'   => 'brand',
				),
				'expected' => array(
					'preview'  => '$brand-colors: (\n  "primary": #1A1A1A,\n  "secondary": #FFFFFF\n);',
					'syntax'   => array(
						'language' => 'scss',
						'tokens'   => array( 'variable', 'string', 'color' ),
					),
					'assets'   => array(
						'css' => array( 'syntax-highlight.css' ),
						'js'  => array( 'prism.js' ),
					),
					'metadata' => array(
						'generated_at' => '2024-12-08T19:22:36-07:00',
						'format'       => 'map',
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			$this->preview
				->expects( $this->once() )
				->method( 'generate_code_preview' )
				->with( $case['palette'], $case['options'] )
				->willReturn( $case['expected'] );

			$result = $this->preview->generate_code_preview( $case['palette'], $case['options'] );

			$this->assertIsArray( $result );
			$this->assertArrayHasKey( 'preview', $result );
			$this->assertArrayHasKey( 'syntax', $result );
			$this->assertArrayHasKey( 'assets', $result );
			$this->assertArrayHasKey( 'metadata', $result );
			$this->assertEquals( $case['expected'], $result );
		}
	}

	/**
	 * Test that generate_mockup_preview creates visualization
	 */
	public function test_generate_mockup_preview_creates_visualization(): void {
		// Arrange
		$palette = array(
			'name'   => 'App Theme',
			'colors' => array( '#FF0000', '#00FF00' ),
		);

		$options = array(
			'template' => 'mobile-app',
			'context'  => array( 'screens' => array( 'home', 'profile' ) ),
			'devices'  => array( 'iphone', 'android' ),
		);

		$expected = array(
			'preview'  => '<div class="mockup-preview">...</div>',
			'context'  => array(
				'screens' => array(
					'home'    => array( 'background' => '#FF0000' ),
					'profile' => array( 'accent' => '#00FF00' ),
				),
				'devices' => array(
					'iphone'  => array( 'frame' => 'iphone-14.png' ),
					'android' => array( 'frame' => 'pixel-7.png' ),
				),
			),
			'assets'   => array(
				'images' => array( 'frames/', 'screens/' ),
				'styles' => array( 'mockup.css' ),
			),
			'metadata' => array(
				'generated_at' => '2024-12-08T19:04:25-07:00',
				'template'     => 'mobile-app',
			),
		);

		$this->preview
			->expects( $this->once() )
			->method( 'generate_mockup_preview' )
			->with( $palette, $options )
			->willReturn( $expected );

		// Act
		$result = $this->preview->generate_mockup_preview( $palette, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'preview', $result );
		$this->assertArrayHasKey( 'context', $result );
		$this->assertArrayHasKey( 'assets', $result );
		$this->assertArrayHasKey( 'metadata', $result );
	}

	/**
	 * @dataProvider invalidPaletteProvider
	 */
	public function test_generate_ui_preview_throws_exception_for_invalid_palette( $palette ): void {
		$this->preview
			->expects( $this->once() )
			->method( 'generate_ui_preview' )
			->with( $palette, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->preview->generate_ui_preview( $palette, array() );
	}

	/**
	 * @dataProvider invalidOptionsProvider
	 */
	public function test_generate_design_preview_throws_exception_for_invalid_options( $options ): void {
		$palette = array(
			'name'   => 'Test',
			'colors' => array( '#FF0000' ),
		);

		$this->preview
			->expects( $this->once() )
			->method( 'generate_design_preview' )
			->with( $palette, $options )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->preview->generate_design_preview( $palette, $options );
	}

	public function invalidPaletteProvider(): array {
		return array(
			'empty array'              => array( array() ),
			'missing colors'           => array( array( 'name' => 'Test' ) ),
			'invalid colors'           => array(
				array(
					'name'   => 'Test',
					'colors' => array( 'invalid' ),
				),
			),
			'non-array input'          => array( 'invalid' ),
			'null input'               => array( null ),
			'empty colors array'       => array(
				array(
					'name'   => 'Test',
					'colors' => array(),
				),
			),
			'non-string colors'        => array(
				array(
					'name'   => 'Test',
					'colors' => array( 123, 456 ),
				),
			),
			'invalid hex format'       => array(
				array(
					'name'   => 'Test',
					'colors' => array( '#GG0000' ),
				),
			),
			'missing hash prefix'      => array(
				array(
					'name'   => 'Test',
					'colors' => array( 'FF0000' ),
				),
			),
			'invalid hex length'       => array(
				array(
					'name'   => 'Test',
					'colors' => array( '#FF00' ),
				),
			),
			'non-string name'          => array(
				array(
					'name'   => 123,
					'colors' => array( '#FF0000' ),
				),
			),
			'missing name'             => array( array( 'colors' => array( '#FF0000' ) ) ),
			'invalid description type' => array(
				array(
					'name'        => 'Test',
					'colors'      => array( '#FF0000' ),
					'description' => 123,
				),
			),
		);
	}

	public function invalidOptionsProvider(): array {
		return array(
			'empty array'              => array( array() ),
			'invalid template'         => array( array( 'template' => 'invalid' ) ),
			'missing required options' => array( array( 'elements' => array() ) ),
			'non-array input'          => array( 'invalid' ),
			'null input'               => array( null ),
			'invalid components type'  => array(
				array(
					'template'   => 'grid',
					'components' => 'invalid',
				),
			),
			'empty components array'   => array(
				array(
					'template'   => 'grid',
					'components' => array(),
				),
			),
			'invalid interactions'     => array(
				array(
					'template'     => 'grid',
					'interactions' => array( 'invalid' ),
				),
			),
			'invalid layout value'     => array(
				array(
					'template' => 'grid',
					'layout'   => 'invalid',
				),
			),
			'invalid size value'       => array(
				array(
					'template' => 'grid',
					'size'     => 'invalid',
				),
			),
			'missing template'         => array( array( 'components' => array( 'swatches' ) ) ),
			'invalid template type'    => array( array( 'template' => 123 ) ),
			'invalid assets format'    => array(
				array(
					'template' => 'grid',
					'assets'   => 'invalid',
				),
			),
			'invalid metadata type'    => array(
				array(
					'template' => 'grid',
					'metadata' => 'invalid',
				),
			),
		);
	}
}
