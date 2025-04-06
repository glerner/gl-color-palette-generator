<?php
/**
 * Color Converter Class
 *
 * Handles color space conversions and color manipulation.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Utils
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Converter class
 */
class Color_Converter {
	/**
	 * Convert hex color to RGB array
	 *
	 * @param string $hex Hex color code
	 * @return array{r: int, g: int, b: int} RGB color array
	 * @throws \InvalidArgumentException If hex color is invalid
	 */
	public function hex_to_rgb( string $hex ): array {
		$validator = new Validator();
		if ( ! $validator->is_valid_hex_color( $hex ) ) {
			throw new \InvalidArgumentException(
				sprintf( __( 'Invalid hex color: %s', 'gl-color-palette-generator' ), $hex )
			);
		}

		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return array(
			'r' => hexdec( substr( $hex, 0, 2 ) ),
			'g' => hexdec( substr( $hex, 2, 2 ) ),
			'b' => hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * Convert RGB array to hex color
	 *
	 * @param array{r: int, g: int, b: int} $rgb RGB color array
	 * @return string Hex color code
	 * @throws \InvalidArgumentException If RGB values are invalid
	 */
	public function rgb_to_hex( array $rgb ): string {
		$validator = new Validator();
		if ( ! $validator->is_valid_rgb_color( $rgb ) ) {
			throw new \InvalidArgumentException(
				__( 'Invalid RGB color values', 'gl-color-palette-generator' )
			);
		}

		return sprintf( '#%02X%02X%02X', $rgb['r'], $rgb['g'], $rgb['b'] );
	}

	/**
	 * Convert RGB array to HSL array
	 *
	 * @param array{r: int, g: int, b: int} $rgb RGB color array
	 * @return array{h: float, s: float, l: float} HSL color array
	 * @throws \InvalidArgumentException If RGB values are invalid
	 */
	public function rgb_to_hsl( array $rgb ): array {
		$validator = new Validator();
		if ( ! $validator->is_valid_rgb_color( $rgb ) ) {
			throw new \InvalidArgumentException(
				__( 'Invalid RGB color values', 'gl-color-palette-generator' )
			);
		}

		$r = $rgb['r'] / 255;
		$g = $rgb['g'] / 255;
		$b = $rgb['b'] / 255;

		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );
		$h   = $s = $l = ( $max + $min ) / 2;

		if ( $max === $min ) {
			$h = $s = 0;
		} else {
			$d = $max - $min;
			$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );

			switch ( $max ) {
				case $r:
					$h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 );
					break;
				case $g:
					$h = ( $b - $r ) / $d + 2;
					break;
				case $b:
					$h = ( $r - $g ) / $d + 4;
					break;
			}

			$h /= 6;
		}

		return array(
			'h' => round( $h * 360, 2 ),
			's' => round( $s * 100, 2 ),
			'l' => round( $l * 100, 2 ),
		);
	}

	/**
	 * Convert HSL array to RGB array
	 *
	 * @param array{h: float, s: float, l: float} $hsl HSL color array
	 * @return array{r: int, g: int, b: int} RGB color array
	 * @throws \InvalidArgumentException If HSL values are invalid
	 */
	public function hsl_to_rgb( array $hsl ): array {
		$validator = new Validator();
		if ( ! $validator->is_valid_hsl_color( $hsl ) ) {
			throw new \InvalidArgumentException(
				__( 'Invalid HSL color values', 'gl-color-palette-generator' )
			);
		}

		$h = $hsl['h'] / 360;
		$s = $hsl['s'] / 100;
		$l = $hsl['l'] / 100;

		if ( $s === 0 ) {
			$r = $g = $b = $l;
		} else {
			$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
			$p = 2 * $l - $q;

			$r = $this->hue_to_rgb( $p, $q, $h + 1 / 3 );
			$g = $this->hue_to_rgb( $p, $q, $h );
			$b = $this->hue_to_rgb( $p, $q, $h - 1 / 3 );
		}

		return array(
			'r' => round( $r * 255 ),
			'g' => round( $g * 255 ),
			'b' => round( $b * 255 ),
		);
	}

	/**
	 * Helper function for HSL to RGB conversion
	 *
	 * @param float $p First value
	 * @param float $q Second value
	 * @param float $t Third value
	 * @return float Resulting value
	 */
	private function hue_to_rgb( float $p, float $q, float $t ): float {
		if ( $t < 0 ) {
			$t += 1;
		}
		if ( $t > 1 ) {
			$t -= 1;
		}
		if ( $t < 1 / 6 ) {
			return $p + ( $q - $p ) * 6 * $t;
		}
		if ( $t < 1 / 2 ) {
			return $q;
		}
		if ( $t < 2 / 3 ) {
			return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
		}
		return $p;
	}

	/**
	 * Adjust color brightness
	 *
	 * @param string $hex    Hex color code
	 * @param float  $factor Brightness factor (-1 to 1)
	 * @return string Modified hex color
	 * @throws \InvalidArgumentException If hex color is invalid or factor is out of range
	 */
	public function adjust_brightness( string $hex, float $factor ): string {
		if ( $factor < -1 || $factor > 1 ) {
			throw new \InvalidArgumentException(
				__( 'Brightness factor must be between -1 and 1', 'gl-color-palette-generator' )
			);
		}

		$rgb = $this->hex_to_rgb( $hex );
		foreach ( array( 'r', 'g', 'b' ) as $component ) {
			$value = $rgb[ $component ];
			if ( $factor > 0 ) {
				$value += ( 255 - $value ) * $factor;
			} else {
				$value += $value * $factor;
			}
			$rgb[ $component ] = round( max( 0, min( 255, $value ) ) );
		}

		return $this->rgb_to_hex( $rgb );
	}

	/**
	 * Get color contrast ratio
	 *
	 * @param string $hex1 First hex color
	 * @param string $hex2 Second hex color
	 * @return float Contrast ratio (1 to 21)
	 * @throws \InvalidArgumentException If hex colors are invalid
	 */
	public function get_contrast_ratio( string $hex1, string $hex2 ): float {
		$rgb1 = $this->hex_to_rgb( $hex1 );
		$rgb2 = $this->hex_to_rgb( $hex2 );

		$l1 = $this->get_relative_luminance( $rgb1 );
		$l2 = $this->get_relative_luminance( $rgb2 );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Calculate relative luminance
	 *
	 * @param array{r: int, g: int, b: int} $rgb RGB color array
	 * @return float Relative luminance value
	 */
	private function get_relative_luminance( array $rgb ): float {
		$rgb = array_map(
			function ( $val ) {
				$val = $val / 255;
				return $val <= 0.03928
				? $val / 12.92
				: pow( ( $val + 0.055 ) / 1.055, 2.4 );
			},
			$rgb
		);

		return 0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b'];
	}

	/**
	 * Get complementary color
	 *
	 * @param string $hex Hex color code
	 * @return string Complementary hex color
	 * @throws \InvalidArgumentException If hex color is invalid
	 */
	public function get_complementary( string $hex ): string {
		$rgb      = $this->hex_to_rgb( $hex );
		$hsl      = $this->rgb_to_hsl( $rgb );
		$hsl['h'] = ( $hsl['h'] + 180 ) % 360;
		$rgb      = $this->hsl_to_rgb( $hsl );
		return $this->rgb_to_hex( $rgb );
	}

	/**
	 * Get analogous colors
	 *
	 * @param string $hex   Hex color code
	 * @param int    $count Number of colors (2-5)
	 * @param int    $angle Angle between colors (15-45)
	 * @return string[] Array of hex colors
	 * @throws \InvalidArgumentException If parameters are invalid
	 */
	public function get_analogous( string $hex, int $count = 2, int $angle = 30 ): array {
		if ( $count < 2 || $count > 5 ) {
			throw new \InvalidArgumentException(
				__( 'Count must be between 2 and 5', 'gl-color-palette-generator' )
			);
		}

		if ( $angle < 15 || $angle > 45 ) {
			throw new \InvalidArgumentException(
				__( 'Angle must be between 15 and 45', 'gl-color-palette-generator' )
			);
		}

		$rgb    = $this->hex_to_rgb( $hex );
		$hsl    = $this->rgb_to_hsl( $rgb );
		$colors = array( $hex );

		$totalAngle = ( $count - 1 ) * $angle;
		$startAngle = -$totalAngle / 2;

		for ( $i = 1; $i < $count; $i++ ) {
			$newHsl      = $hsl;
			$newHsl['h'] = ( $hsl['h'] + $startAngle + ( $i * $angle ) + 360 ) % 360;
			$newRgb      = $this->hsl_to_rgb( $newHsl );
			$colors[]    = $this->rgb_to_hex( $newRgb );
		}

		return $colors;
	}
}
