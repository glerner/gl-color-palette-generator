<?php
/**
 * Contrast Checker Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Accessibility
 */

namespace GL_Color_Palette_Generator\Accessibility;

use WP_Error;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Contrast_Checker
 *
 * Calculates and validates contrast ratios according to WCAG 2.1 standards.
 * Provides methods for checking text readability and color contrast compliance.
 *
 * @since 1.0.0
 */
class Contrast_Checker {
	/**
	 * Calculate relative luminance
	 *
	 * @param float $r Red component (0-255)
	 * @param float $g Green component (0-255)
	 * @param float $b Blue component (0-255)
	 * @return float Relative luminance value
	 */
	private function get_relative_luminance( $r, $g, $b ) {
		$r = $r / 255;
		$g = $g / 255;
		$b = $b / 255;

		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Convert hex to RGB
	 *
	 * @param string $hex Hex color code
	 * @return array RGB color components
	 */
	private function hex_to_rgb( $hex ) {
		$hex = ltrim( $hex, '#' );
		return array(
			hexdec( substr( $hex, 0, 2 ) ), // R
			hexdec( substr( $hex, 2, 2 ) ), // G
			hexdec( substr( $hex, 4, 2 ) ),  // B
		);
	}

	/**
	 * Calculate contrast ratio between two colors
	 *
	 * @param string $color1 Hex color code
	 * @param string $color2 Hex color code
	 * @return float Contrast ratio value
	 */
	public function get_contrast_ratio( $color1, $color2 ) {
		$rgb1 = $this->hex_to_rgb( $color1 );
		$rgb2 = $this->hex_to_rgb( $color2 );

		$l1 = $this->get_relative_luminance( $rgb1[0], $rgb1[1], $rgb1[2] );
		$l2 = $this->get_relative_luminance( $rgb2[0], $rgb2[1], $rgb2[2] );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Check if contrast ratio meets WCAG AA requirements
	 *
	 * @param float $ratio The contrast ratio to check
	 * @param bool  $is_large_text Whether this is for large text
	 * @return bool True if meets requirements, false otherwise
	 */
	public function meets_wcag_aa( $ratio, $is_large_text = false ) {
		return $ratio >= ( $is_large_text ? Color_Constants::WCAG_CONTRAST_AA_LARGE : Color_Constants::WCAG_CONTRAST_AA );
	}

	/**
	 * Check if contrast ratio meets WCAG AAA requirements
	 *
	 * @param float $ratio The contrast ratio to check
	 * @param bool  $is_large_text Whether this is for large text
	 * @return bool True if meets requirements, false otherwise
	 */
	public function meets_wcag_aaa( $ratio, $is_large_text = false ) {
		return $ratio >= ( $is_large_text ? Color_Constants::WCAG_CONTRAST_AA : Color_Constants::WCAG_CONTRAST_AAA );
	}
}
