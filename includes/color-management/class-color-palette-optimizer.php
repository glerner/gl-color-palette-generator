<?php
/**
 * Color Palette Optimizer
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Optimizer
 *
 * Optimizes color palettes for:
 * - WCAG accessibility compliance
 * - Color harmony and balance
 * - Contrast ratios
 * - Color distribution
 *
 * @since 1.0.0
 */
class Color_Palette_Optimizer {
	/**
	 * Analyzer instance
	 *
	 * @var Color_Palette_Analyzer
	 */
	protected $analyzer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->analyzer = new Color_Palette_Analyzer();
	}

	/**
	 * Optimize a color palette
	 *
	 * @param Color_Palette $palette The palette to optimize
	 * @param array         $options {
	 *             Optional. Array of optimization options.
	 *
	 *     @type string $target_wcag    Target WCAG level ('AA' or 'AAA'). Default 'AA'.
	 *     @type bool   $preserve_hues  Whether to preserve original hues. Default true.
	 *     @type float  $max_adjustment Maximum color adjustment allowed (0-1). Default 0.2.
	 * }
	 * @return Color_Palette Optimized palette
	 */
	public function optimize( Color_Palette $palette, array $options = array() ): Color_Palette {
		$default_options = array(
			'target_wcag'    => 'AA',
			'preserve_hues'  => true,
			'max_adjustment' => 0.2,
		);

		$options = wp_parse_args( $options, $default_options );

		/**
		 * Get initial analysis
		 */
		$analysis = $this->analyzer->analyze( $palette );

		/**
		 * Create a new palette for optimization
		 */
		$optimized_colors = $palette->get_colors();

		/**
		 * Optimize contrast ratios
		 */
		$optimized_colors = $this->optimize_contrast(
			$optimized_colors,
			$options['target_wcag'],
			$options['max_adjustment']
		);

		/**
		 * Optimize color harmony
		 */
		$optimized_colors = $this->optimize_harmony(
			$optimized_colors,
			$options['preserve_hues'],
			$options['max_adjustment']
		);

		/**
		 * Create new palette with optimized colors
		 */
		return new Color_Palette(
			$optimized_colors,
			array_merge(
				$palette->get_metadata(),
				array(
					'optimized'            => true,
					'optimization_date'    => current_time( 'mysql' ),
					'optimization_options' => $options,
				)
			)
		);
	}

	/**
	 * Optimize contrast ratios between colors
	 *
	 * @param array  $colors Array of hex colors
	 * @param string $target_wcag Target WCAG level
	 * @param float  $max_adjustment Maximum adjustment allowed
	 * @return array Optimized colors
	 */
	private function optimize_contrast( array $colors, string $target_wcag, float $max_adjustment ): array {
		$min_contrast = $target_wcag === 'AAA'
			? Color_Palette_Analyzer::WCAG_AAA_CONTRAST
			: Color_Palette_Analyzer::WCAG_AA_CONTRAST;

		$optimized = $colors;

		for ( $i = 0; $i < count( $colors ); $i++ ) {
			for ( $j = $i + 1; $j < count( $colors ); $j++ ) {
				$contrast = $this->analyzer->get_contrast_ratio( $optimized[ $i ], $optimized[ $j ] );

				if ( $contrast < $min_contrast ) {
					/**
					 * Adjust colors to improve contrast
					 */
					list($color1, $color2) = $this->adjust_contrast_pair(
						$optimized[ $i ],
						$optimized[ $j ],
						$min_contrast,
						$max_adjustment
					);

					$optimized[ $i ] = $color1;
					$optimized[ $j ] = $color2;
				}
			}
		}

		return $optimized;
	}

	/**
	 * Optimize color harmony
	 *
	 * @param array $colors Array of hex colors
	 * @param bool  $preserve_hues Whether to preserve original hues
	 * @param float $max_adjustment Maximum adjustment allowed
	 * @return array Optimized colors
	 */
	private function optimize_harmony( array $colors, bool $preserve_hues, float $max_adjustment ): array {
		$optimized = $colors;

		/**
		 * Get current harmony analysis
		 */
		$harmony = $this->analyzer->analyze_harmony( $optimized );

		/**
		 * Adjust saturation variance if needed
		 */
		if ( $harmony['saturation_variance'] > 0.1 ) {
			$optimized = $this->normalize_saturation( $optimized, $max_adjustment );
		}

		/**
		 * Adjust value distribution if needed
		 */
		if ( ! $harmony['is_balanced'] ) {
			$optimized = $this->balance_values( $optimized, $max_adjustment );
		}

		/**
		 * Adjust hue distribution if allowed and needed
		 */
		if ( ! $preserve_hues && $harmony['hue_variance'] > 2000 ) {
			$optimized = $this->distribute_hues( $optimized, $max_adjustment );
		}

		return $optimized;
	}

	/**
	 * Adjust a pair of colors to meet contrast requirements
	 *
	 * @param string $color1 First hex color
	 * @param string $color2 Second hex color
	 * @param float  $target_contrast Target contrast ratio
	 * @param float  $max_adjustment Maximum adjustment allowed
	 * @return array Adjusted color pair
	 */
	private function adjust_contrast_pair(
		string $color1,
		string $color2,
		float $target_contrast,
		float $max_adjustment
	): array {
		$rgb1 = $this->hex_to_rgb( $color1 );
		$rgb2 = $this->hex_to_rgb( $color2 );

		$l1 = $this->get_relative_luminance( $color1 );
		$l2 = $this->get_relative_luminance( $color2 );

		/**
		 * Determine which color to lighten and which to darken
		 */
		if ( $l1 > $l2 ) {
			$lighter =& $rgb1;
			$darker  =& $rgb2;
		} else {
			$lighter =& $rgb2;
			$darker  =& $rgb1;
		}

		/**
		 * Adjust colors within max_adjustment limit
		 */
		$adjustment = min( $max_adjustment * 255, 255 * 0.9 );

		/**
		 * Lighten the lighter color
		 */
		foreach ( $lighter as &$component ) {
			$component = min( 255, $component + $adjustment );
		}

		/**
		 * Darken the darker color
		 */
		foreach ( $darker as &$component ) {
			$component = max( 0, $component - $adjustment );
		}

		return array(
			$this->rgb_to_hex( $rgb1 ),
			$this->rgb_to_hex( $rgb2 ),
		);
	}

	/**
	 * Normalize saturation across colors
	 *
	 * @param array $colors Array of hex colors
	 * @param float $max_adjustment Maximum adjustment allowed
	 * @return array Normalized colors
	 */
	private function normalize_saturation( array $colors, float $max_adjustment ): array {
		$saturations     = array_map( array( $this->analyzer, 'get_saturation' ), $colors );
		$mean_saturation = array_sum( $saturations ) / count( $saturations );

		return array_map(
			function ( $color ) use ( $mean_saturation, $max_adjustment ) {
				$hsv                = $this->hex_to_hsv( $color );
				$current_saturation = $hsv['s'];

				$adjustment = ( $mean_saturation - $current_saturation ) * $max_adjustment;
				$hsv['s']   = max( 0, min( 1, $current_saturation + $adjustment ) );

				return $this->hsv_to_hex( $hsv );
			},
			$colors
		);
	}

	/**
	 * Convert hex color to RGB values
	 *
	 * @param string $color Hex color
	 * @return array RGB values
	 */
	private function hex_to_rgb( string $color ): array {
		$color = ltrim( $color, '#' );
		return array(
			'r' => hexdec( substr( $color, 0, 2 ) ),
			'g' => hexdec( substr( $color, 2, 2 ) ),
			'b' => hexdec( substr( $color, 4, 2 ) ),
		);
	}

	/**
	 * Convert RGB values to hex color
	 *
	 * @param array $rgb RGB values
	 * @return string Hex color
	 */
	private function rgb_to_hex( array $rgb ): string {
		return sprintf(
			'#%02X%02X%02X',
			round( $rgb['r'] ),
			round( $rgb['g'] ),
			round( $rgb['b'] )
		);
	}

	/**
	 * Convert hex color to HSV values
	 *
	 * @param string $color Hex color
	 * @return array HSV values
	 */
	private function hex_to_hsv( string $color ): array {
		$rgb = $this->hex_to_rgb( $color );
		$r   = $rgb['r'] / 255;
		$g   = $rgb['g'] / 255;
		$b   = $rgb['b'] / 255;

		$max   = max( $r, $g, $b );
		$min   = min( $r, $g, $b );
		$delta = $max - $min;

		/**
		 * Calculate hue
		 */
		if ( $delta == 0 ) {
			$h = 0;
		} elseif ( $max == $r ) {
			$h = 60 * fmod( ( ( $g - $b ) / $delta ), 6 );
		} elseif ( $max == $g ) {
			$h = 60 * ( ( ( $b - $r ) / $delta ) + 2 );
		} else {
			$h = 60 * ( ( ( $r - $g ) / $delta ) + 4 );
		}

		/**
		 * Calculate saturation
		 */
		$s = ( $max == 0 ) ? 0 : ( $delta / $max );

		/**
		 * Calculate value
		 *
		 * The value (brightness) is the maximum of the RGB values.
		 */
		$v = $max;

		return array(
			'h' => $h < 0 ? $h + 360 : $h,
			's' => $s,
			'v' => $v,
		);
	}

	/**
	 * Convert HSV values to hex color
	 *
	 * @param array $hsv HSV values
	 * @return string Hex color
	 */
	private function hsv_to_hex( array $hsv ): string {
		$h = $hsv['h'];
		$s = $hsv['s'];
		$v = $hsv['v'];

		$c = $v * $s;
		$x = $c * ( 1 - abs( fmod( $h / 60, 2 ) - 1 ) );
		$m = $v - $c;

		if ( $h >= 0 && $h < 60 ) {
			$r = $c;
			$g = $x;
			$b = 0;
		} elseif ( $h >= 60 && $h < 120 ) {
			$r = $x;
			$g = $c;
			$b = 0;
		} elseif ( $h >= 120 && $h < 180 ) {
			$r = 0;
			$g = $c;
			$b = $x;
		} elseif ( $h >= 180 && $h < 240 ) {
			$r = 0;
			$g = $x;
			$b = $c;
		} elseif ( $h >= 240 && $h < 300 ) {
			$r = $x;
			$g = 0;
			$b = $c;
		} else {
			$r = $c;
			$g = 0;
			$b = $x;
		}

		return $this->rgb_to_hex(
			array(
				'r' => ( $r + $m ) * 255,
				'g' => ( $g + $m ) * 255,
				'b' => ( $b + $m ) * 255,
			)
		);
	}

	/**
	 * Balance color values (brightness)
	 *
	 * @param array $colors Array of hex colors
	 * @param float $max_adjustment Maximum adjustment allowed
	 * @return array Balanced colors
	 */
	private function balance_values( array $colors, float $max_adjustment ): array {
		$values = array_map(
			function ( $color ) {
				return $this->hex_to_hsv( $color )['v'];
			},
			$colors
		);

		$mean_value = array_sum( $values ) / count( $values );

		return array_map(
			function ( $color, $index ) use ( $mean_value, $max_adjustment ) {
				$hsv           = $this->hex_to_hsv( $color );
				$current_value = $hsv['v'];

				$adjustment = ( $mean_value - $current_value ) * $max_adjustment;
				$hsv['v']   = max( 0, min( 1, $current_value + $adjustment ) );

				return $this->hsv_to_hex( $hsv );
			},
			$colors,
			array_keys( $colors )
		);
	}

	/**
	 * Distribute hues evenly
	 *
	 * @param array $colors Array of hex colors
	 * @param float $max_adjustment Maximum adjustment allowed
	 * @return array Distributed colors
	 */
	private function distribute_hues( array $colors, float $max_adjustment ): array {
		$count       = count( $colors );
		$target_step = 360 / $count;

		/**
		 * Sort colors by hue
		 */
		usort(
			$colors,
			function ( $a, $b ) {
				return $this->hex_to_hsv( $a )['h'] <=> $this->hex_to_hsv( $b )['h'];
			}
		);

		return array_map(
			function ( $index ) use ( $colors, $target_step, $max_adjustment ) {
				$hsv        = $this->hex_to_hsv( $colors[ $index ] );
				$target_hue = $index * $target_step;

				$adjustment = ( $target_hue - $hsv['h'] ) * $max_adjustment;
				$hsv['h']   = fmod( ( $hsv['h'] + $adjustment + 360 ), 360 );

				return $this->hsv_to_hex( $hsv );
			},
			range( 0, $count - 1 )
		);
	}
}
