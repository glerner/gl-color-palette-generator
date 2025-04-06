<?php
namespace GL_Color_Palette_Generator\Localization;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Theme Namer Class
 *
 * Generates theme variation names based on their color roles (primary, secondary, etc.)
 */
class Theme_Namer implements Color_Constants {
	private $color_namer;

	public function __construct( Color_Namer $color_namer ) {
		$this->color_namer = $color_namer;
	}

	/**
	 * Generate a theme name from color roles
	 *
	 * @param array  $colors Array of colors with roles ['primary' => '#hex', 'secondary' => '#hex', etc.]
	 * @param string $scheme Color scheme type
	 * @return string Theme name
	 */
	public function generate_theme_name( $colors, $scheme = 'complementary' ) {
		$names = array();

		foreach ( self::COLOR_ROLES as $role ) {
			if ( isset( $colors[ $role ] ) && ! empty( $colors[ $role ] ) ) {
				$name    = $this->color_namer->get_color_name( $colors[ $role ] );
				$names[] = $this->format_color_name( $name );
			}
		}

		if ( empty( $names ) ) {
			return __( 'Custom Theme', 'gl-color-palette-generator' );
		}

		$scheme_info    = self::COLOR_SCHEMES[ $scheme ] ?? self::COLOR_SCHEMES['complementary'];
		$required_roles = self::REQUIRED_ROLES[ $scheme ];
		$name_count     = count( $required_roles );

		return implode( '-', array_slice( $names, 0, $name_count ) ) .
				( $scheme_info['name'] ? ' ' . __( $scheme_info['name'], 'gl-color-palette-generator' ) : '' );
	}

	/**
	 * Format a color name into CamelCase
	 */
	private function format_color_name( $name ) {
		// Split on spaces and special characters
		$parts = preg_split( '/[\s\-_]+/', $name );
		// Capitalize each part and combine
		return implode( '', array_map( 'ucfirst', $parts ) );
	}
}
