<?php
declare(strict_types=1);
/**
 * Tests for the Prompt_Engineer class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Generators
 * @since 1.0.0
 * @author George Lerner
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Generators;

use GL_Color_Palette_Generator\Generators\Prompt_Engineer;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;

/**
 * Test case for the Prompt_Engineer class.
 *
 * @covers \GL_Color_Palette_Generator\Generators\Prompt_Engineer
 */
class Test_Prompt_Engineer extends WP_Mock_Test_Case {
	/**
	 * The Prompt_Engineer instance being tested.
	 *
	 * @var Prompt_Engineer
	 */
	protected Prompt_Engineer $prompt_engineer;

	/**
	 * Set up the test environment.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
		$this->prompt_engineer = new Prompt_Engineer();
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Tests the generate_color_prompt method.
	 *
	 * @return void
	 */
	public function test_generate_color_prompt(): void {
		$input = array(
			'theme' => 'ocean',
			'mood'  => 'calm',
			'count' => 5,
		);

		$prompt = $this->prompt_engineer->generate_color_prompt( $input );

		$this->assertIsString( $prompt );
		$this->assertStringContainsString( 'ocean', $prompt );
		$this->assertStringContainsString( 'calm', $prompt );
		$this->assertStringContainsString( '5', $prompt );
	}

	/**
	 * Tests the generate_color_prompt method with constraints.
	 *
	 * @return void
	 */
	public function test_generate_color_prompt_with_constraints(): void {
		$input = array(
			'theme'       => 'forest',
			'mood'        => 'energetic',
			'count'       => 4,
			'constraints' => array(
				'include_colors' => array( '#00FF00' ),
				'exclude_colors' => array( '#FF0000' ),
				'min_contrast'   => 4.5,
			),
		);

		$prompt = $this->prompt_engineer->generate_color_prompt( $input );

		$this->assertIsString( $prompt );
		$this->assertStringContainsString( '#00FF00', $prompt );
		$this->assertStringContainsString( '#FF0000', $prompt );
		$this->assertStringContainsString( '4.5', $prompt );
	}

	/**
	 * Tests the generate_name_prompt method.
	 *
	 * @return void
	 */
	public function test_generate_name_prompt(): void {
		$colors = array( '#FF0000', '#00FF00', '#0000FF' );
		$prompt = $this->prompt_engineer->generate_name_prompt( $colors );

		$this->assertIsString( $prompt );
		foreach ( $colors as $color ) {
			$this->assertStringContainsString( $color, $prompt );
		}
	}

	/**
	 * Tests the generate_description_prompt method.
	 *
	 * @return void
	 */
	public function test_generate_description_prompt(): void {
		$colors = array( '#FF0000', '#00FF00', '#0000FF' );
		$name   = 'Ocean Breeze';

		$prompt = $this->prompt_engineer->generate_description_prompt( $colors, $name );

		$this->assertIsString( $prompt );
		$this->assertStringContainsString( $name, $prompt );
		foreach ( $colors as $color ) {
			$this->assertStringContainsString( $color, $prompt );
		}
	}

	/**
	 * Tests the generate_variation_prompt method.
	 *
	 * @return void
	 */
	public function test_generate_variation_prompt(): void {
		$colors         = array( '#FF0000', '#00FF00', '#0000FF' );
		$variation_type = 'analogous';

		$prompt = $this->prompt_engineer->generate_variation_prompt( $colors, $variation_type );

		$this->assertIsString( $prompt );
		$this->assertStringContainsString( $variation_type, $prompt );
		foreach ( $colors as $color ) {
			$this->assertStringContainsString( $color, $prompt );
		}
	}

	/**
	 * Tests the sanitize_prompt method.
	 *
	 * @return void
	 */
	public function test_sanitize_prompt(): void {
		$prompt    = "Test prompt with <script>alert('xss')</script> and {malicious: code}";
		$sanitized = $this->prompt_engineer->sanitize_prompt( $prompt );

		$this->assertIsString( $sanitized );
		$this->assertStringNotContainsString( '<script>', $sanitized );
		$this->assertStringNotContainsString( 'alert', $sanitized );
	}

	/**
	 * Tests the validate_prompt method.
	 *
	 * @return void
	 */
	public function test_validate_prompt(): void {
		$valid_prompt = $this->prompt_engineer->generate_color_prompt(
			array(
				'theme' => 'sunset',
				'mood'  => 'peaceful',
				'count' => 5,
			)
		);

		$this->assertTrue( $this->prompt_engineer->validate_prompt( $valid_prompt ) );

		$invalid_prompt = str_repeat( 'a', 5000 ); // Too long
		$this->assertFalse( $this->prompt_engineer->validate_prompt( $invalid_prompt ) );
	}

	/**
	 * Tests the format_prompt method.
	 *
	 * @return void
	 */
	public function test_format_prompt(): void {
		$input = array(
			'text'      => 'Test {placeholder}',
			'variables' => array( 'placeholder' => 'value' ),
		);

		$formatted = $this->prompt_engineer->format_prompt( $input['text'], $input['variables'] );

		$this->assertEquals( 'Test value', $formatted );
	}

	/**
	 * Tests the get_prompt_template method.
	 *
	 * @return void
	 */
	public function test_get_prompt_template(): void {
		$template = $this->prompt_engineer->get_prompt_template( 'color' );
		$this->assertIsString( $template );
		$this->assertNotEmpty( $template );
	}
}
