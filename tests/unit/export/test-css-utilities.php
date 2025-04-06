<?php
/**
 * Test CSS Utilities
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Export
 */

namespace GL_Color_Palette_Generator\Tests\Export;

use GL_Color_Palette_Generator\Export\CSS_Utilities;
use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;

/**
 * Test CSS Utilities
 */
class Test_CSS_Utilities extends Unit_Test_Case {
	/**
	 * Test CSS variable name generation
	 */
	public function test_generate_css_var_name(): void {
		// Test base color variables
		$this->assertEquals(
			'--wp--preset--color--primary',
			CSS_Utilities::generate_css_var_name( 'primary' )
		);

		// Test variations
		$this->assertEquals(
			'--wp--preset--color--primary-light',
			CSS_Utilities::generate_css_var_name( 'primary', 'light' )
		);
	}

	/**
	 * Test CSS value generation
	 */
	public function test_generate_css_var_value(): void {
		// Test base color values
		$this->assertEquals(
			'var(--wp--preset--color--primary)',
			CSS_Utilities::generate_css_var_value( 'primary' )
		);

		// Test variations
		$this->assertEquals(
			'var(--wp--preset--color--primary-light)',
			CSS_Utilities::generate_css_var_value( 'primary', 'light' )
		);
	}

	/**
	 * Test CSS class name generation
	 */
	public function test_generate_css_class_name(): void {
		// Test base color classes
		$this->assertEquals(
			'has-primary-color',
			CSS_Utilities::generate_css_class_name( 'primary' )
		);

		// Test variations
		$this->assertEquals(
			'has-primary-light-color',
			CSS_Utilities::generate_css_class_name( 'primary', 'light' )
		);
	}

	/**
	 * Test CSS background class name generation
	 */
	public function test_generate_css_bg_class_name(): void {
		// Test base color background classes
		$this->assertEquals(
			'has-primary-background-color',
			CSS_Utilities::generate_css_bg_class_name( 'primary' )
		);

		// Test variations
		$this->assertEquals(
			'has-primary-light-background-color',
			CSS_Utilities::generate_css_bg_class_name( 'primary', 'light' )
		);
	}

	/**
	 * Test CSS name generation with all color roles
	 */
	public function test_generate_names_for_all_roles(): void {
		foreach ( self::COLOR_ROLES as $role ) {
			foreach ( self::COLOR_VARIATIONS as $variation => $label ) {
				// Test variable names
				$var_name = CSS_Utilities::generate_css_var_name( $role, $variation );
				$this->assertStringStartsWith( '--wp--preset--color--', $var_name );

				// Test class names
				$class_name = CSS_Utilities::generate_css_class_name( $role, $variation );
				$this->assertStringStartsWith( 'has-', $class_name );
				$this->assertStringEndsWith( '-color', $class_name );

				// Test background class names
				$bg_class_name = CSS_Utilities::generate_css_bg_class_name( $role, $variation );
				$this->assertStringStartsWith( 'has-', $bg_class_name );
				$this->assertStringEndsWith( '-background-color', $bg_class_name );
			}
		}
	}
}
