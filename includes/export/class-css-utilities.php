<?php
/**
 * CSS Utilities Class
 *
 * Provides utility functions for CSS generation and naming conventions.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Export
 */

namespace GL_Color_Palette_Generator\Export;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * CSS Utilities Class
 */
class CSS_Utilities implements Color_Constants {
	/**
	 * Generate CSS custom property name for a color
	 *
	 * @param string $role Color role (e.g., 'primary', 'secondary')
	 * @param string $variation Variation type (e.g., 'base', 'light', 'dark')
	 * @return string CSS custom property name
	 */
	public static function generate_css_var_name( string $role, string $variation = 'base' ): string {
		return "--wp--preset--color--{$role}" . ( $variation !== 'base' ? "-{$variation}" : '' );
	}

	/**
	 * Generate CSS color value
	 *
	 * @param string $role Color role
	 * @param string $variation Color variation
	 * @return string CSS var() function call
	 */
	public static function generate_css_var_value( string $role, string $variation = 'base' ): string {
		return sprintf( 'var(%s)', self::generate_css_var_name( $role, $variation ) );
	}

	/**
	 * Generate CSS class name for a color role
	 *
	 * @param string $role Color role
	 * @param string $variation Color variation
	 * @return string CSS class name
	 */
	public static function generate_css_class_name( string $role, string $variation = 'base' ): string {
		return "has-{$role}" . ( $variation !== 'base' ? "-{$variation}" : '' ) . '-color';
	}

	/**
	 * Generate CSS background class name
	 *
	 * @param string $role Color role
	 * @param string $variation Color variation
	 * @return string CSS background class name
	 */
	public static function generate_css_bg_class_name( string $role, string $variation = 'base' ): string {
		return "has-{$role}" . ( $variation !== 'base' ? "-{$variation}" : '' ) . '-background-color';
	}
}
