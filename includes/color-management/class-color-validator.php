<?php
namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Color Validator
 *
 * Validates colors and color schemes
 */
class Color_Validator implements Color_Constants {
	/**
	 * Validate colors for scheme
	 *
	 * @param array  $colors Colors to validate
	 * @param string $scheme Color scheme type
	 * @return bool|\WP_Error True if valid, WP_Error if not
	 */
	public function validate_colors_for_scheme( $colors, $scheme ) {
		if ( ! isset( self::COLOR_SCHEMES[ $scheme ] ) ) {
			return new \WP_Error(
				'invalid_scheme',
				sprintf(
					/* translators: %s: scheme name */
					__( 'Invalid color scheme: %s', 'gl-color-palette-generator' ),
					$scheme
				)
			);
		}

		$scheme_info    = self::COLOR_SCHEMES[ $scheme ];
		$required_roles = self::REQUIRED_ROLES[ $scheme ];

		// Check required colors
		foreach ( $required_roles as $role ) {
			if ( ! isset( $colors[ $role ] ) || empty( $colors[ $role ] ) ) {
				return new \WP_Error(
					'missing_color',
					sprintf(
						/* translators: 1: color role (primary, secondary, etc.), 2: scheme label */
						__( 'Missing required %1$s color for %2$s scheme', 'gl-color-palette-generator' ),
						$role,
						$scheme_info['label']
					)
				);
			}
		}

		// Check optional colors
		$optional_roles = array_diff( self::COLOR_ROLES, $required_roles );
		foreach ( $colors as $role => $color ) {
			if ( ! in_array( $role, self::COLOR_ROLES ) ) {
				return new \WP_Error(
					'invalid_role',
					sprintf(
						/* translators: %s: color role */
						__( 'Invalid color role: %s', 'gl-color-palette-generator' ),
						$role
					)
				);
			}
		}

		return true;
	}

	/**
	 * Validate hex color format
	 *
	 * @param string $color Color to validate
	 * @return bool True if valid
	 */
	public function is_valid_hex_color( $color ) {
		return preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color );
	}
}
