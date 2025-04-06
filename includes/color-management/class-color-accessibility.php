<?php

namespace GL_Color_Palette_Generator\Color_Management;

use WP_Error;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Color Accessibility
 *
 * Handles color accessibility checks according to WCAG guidelines.
 *
 * Note: For core contrast ratio calculations, prefer using Color_Utility::get_contrast_ratio()
 * which provides a more optimized implementation. This class's check_contrast() method
 * is maintained for backwards compatibility and higher-level accessibility checks.
 *
 * @see Color_Utility::get_contrast_ratio()
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */
class Color_Accessibility {
	/**
	 * WCAG contrast ratios
	 *
	 * @var array
	 */
	private const WCAG_RATIOS = array(
		'AAA' => array(
			'large'  => 4.5,
			'normal' => 7.0,
		),
		'AA'  => array(
			'large'  => 3.0,
			'normal' => 4.5,
		),
		'A'   => array(
			'large'  => 3.0,
			'normal' => 3.0,
		),
	);

	/**
	 * Check contrast ratio between two colors
	 *
	 * @param string $color1 First color
	 * @param string $color2 Second color
	 * @return float Contrast ratio
	 */
	public function check_contrast( string $color1, string $color2 ): float {
		$l1 = $this->get_relative_luminance( $color1 );
		$l2 = $this->get_relative_luminance( $color2 );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Check if contrast meets WCAG level
	 *
	 * @param float  $contrast Contrast ratio
	 * @param string $level    WCAG level ('A', 'AA', or 'AAA')
	 * @param string $size     Text size ('normal' or 'large')
	 * @return bool True if meets level
	 */
	public function meets_wcag_level( float $contrast, string $level, string $size = 'normal' ): bool {
		if ( ! isset( self::WCAG_RATIOS[ $level ][ $size ] ) ) {
			return false;
		}

		return $contrast >= self::WCAG_RATIOS[ $level ][ $size ];
	}

	/**
	 * Get relative luminance of a color
	 *
	 * @param string $color Color in hex format
	 * @return float Relative luminance
	 */
	private function get_relative_luminance( string $color ): float {
		$rgb = $this->hex_to_rgb( $color );
		if ( count( $rgb ) === 0 ) {
			return 0.0;
		}

		$rgb = array_map(
			function ( $val ) {
				$val = $val / 255;
				return $val <= 0.03928
				? $val / 12.92
				: pow( ( $val + 0.055 ) / 1.055, 2.4 );
			},
			$rgb
		);

		return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
	}

	/**
	 * Convert hex color to RGB array
	 *
	 * @param string $hex Hex color code
	 * @return array RGB values
	 */
	private function hex_to_rgb( string $hex ): array {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return array_map(
			'hexdec',
			array(
				substr( $hex, 0, 2 ),
				substr( $hex, 2, 2 ),
				substr( $hex, 4, 2 ),
			)
		);
	}
}
