<?php
/**
 * Color Analysis Class
 *
 * Handles color analysis, including accessibility, harmony, and psychological aspects.
 *
 * @package GL_Color_Palette_Generator
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator;

use GL_Color_Palette_Generator\Color_Management\Color_Utility;

class Color_Analysis {
	/**
	 * Color utility instance
	 *
	 * @var Color_Utility
	 */
	private $color_utility;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->color_utility = new Color_Utility();
	}

	/**
	 * Analyze color harmony and relationships
	 *
	 * @param array $colors Base colors
	 * @return array Harmony analysis
	 */
	public function analyze_harmony( array $colors ): array {
		$hex_colors = array_column( $colors, 'hex' );

		$harmony = array(
			'scheme_type'     => $this->detect_color_scheme( $hex_colors ),
			'balance'         => $this->analyze_color_balance( $colors ),
			'temperature'     => $this->analyze_temperature( $colors ),
			'contrast_levels' => $this->analyze_contrast_levels( $colors ),
			'recommendations' => array(),
		);

		// Analyze potential harmony issues
		$issues = array();

		// Check contrast between adjacent colors
		for ( $i = 0; $i < count( $hex_colors ) - 1; $i++ ) {
			$contrast = $this->color_utility->get_contrast_ratio( $hex_colors[ $i ], $hex_colors[ $i + 1 ] );
			if ( $contrast < 1.5 ) {
				$issues[] = array(
					'type'   => 'low_contrast',
					'colors' => array( $hex_colors[ $i ], $hex_colors[ $i + 1 ] ),
					'value'  => $contrast,
				);
			}
		}

		// Check balance distribution
		if ( $harmony['balance']['lightness']['std_dev'] > 30 ) {
			$issues[] = array(
				'type'  => 'unbalanced_lightness',
				'value' => $harmony['balance']['lightness']['std_dev'],
			);
		}

		$harmony['issues'] = $issues;
		return $harmony;
	}

	/**
	 * Analyze color balance
	 *
	 * @param array $colors Colors to analyze
	 * @return array Balance analysis
	 */
	private function analyze_color_balance( array $colors ): array {
		$lightness_values  = array();
		$saturation_values = array();

		foreach ( $colors as $color ) {
			$hsl                 = $this->color_utility->hex_to_hsl( $color['hex'] );
			$lightness_values[]  = $hsl['l'];
			$saturation_values[] = $hsl['s'];
		}

		return array(
			'lightness'  => array(
				'mean'    => array_sum( $lightness_values ) / count( $lightness_values ),
				'std_dev' => $this->calculate_std_dev( $lightness_values ),
				'range'   => max( $lightness_values ) - min( $lightness_values ),
			),
			'saturation' => array(
				'mean'    => array_sum( $saturation_values ) / count( $saturation_values ),
				'std_dev' => $this->calculate_std_dev( $saturation_values ),
				'range'   => max( $saturation_values ) - min( $saturation_values ),
			),
		);
	}

	/**
	 * Calculate standard deviation
	 *
	 * @param array $values Values to calculate std dev for
	 * @return float Standard deviation
	 */
	private function calculate_std_dev( array $values ): float {
		$mean     = array_sum( $values ) / count( $values );
		$variance = array_sum(
			array_map(
				function ( $x ) use ( $mean ) {
					return pow( $x - $mean, 2 );
				},
				$values
			)
		) / count( $values );
		return sqrt( $variance );
	}

	/**
	 * Detect color scheme type
	 *
	 * @param array $colors Colors to analyze
	 * @return string Detected scheme type
	 */
	private function detect_color_scheme( array $colors ): string {
		$hues = array();
		foreach ( $colors as $color ) {
			$hsl    = $this->color_utility->hex_to_hsl( $color );
			$hues[] = $hsl['h'];
		}

		sort( $hues );
		$hue_differences = array();
		for ( $i = 0; $i < count( $hues ) - 1; $i++ ) {
			$hue_differences[] = $hues[ $i + 1 ] - $hues[ $i ];
		}

		// Check for monochromatic
		if ( max( $hue_differences ) < 15 ) {
			return 'monochromatic';
		}

		// Check for complementary
		if ( count( $hues ) === 2 && abs( $hues[1] - $hues[0] ) >= 150 && abs( $hues[1] - $hues[0] ) <= 210 ) {
			return 'complementary';
		}

		// Check for triadic
		if ( count( $hues ) === 3 ) {
			$diff1 = abs( $hues[1] - $hues[0] );
			$diff2 = abs( $hues[2] - $hues[1] );
			if ( abs( $diff1 - 120 ) <= 15 && abs( $diff2 - 120 ) <= 15 ) {
				return 'triadic';
			}
		}

		// Check for analogous
		if ( max( $hue_differences ) <= 60 ) {
			return 'analogous';
		}

		return 'custom';
	}

	/**
	 * Analyze color temperature
	 *
	 * @param array $colors Colors to analyze
	 * @return array Temperature analysis
	 */
	private function analyze_temperature( array $colors ): array {
		$warm_hues    = array( 0, 60 );
		$cool_hues    = array( 180, 240 );
		$neutral_hues = array( 60, 180, 240, 360 );

		$temperatures = array();
		foreach ( $colors as $color ) {
			$hsl = $this->color_utility->hex_to_hsl( $color['hex'] );
			$hue = $hsl['h'];

			if ( $hue >= $warm_hues[0] && $hue <= $warm_hues[1] ) {
				$temperatures[] = 'warm';
			} elseif ( $hue >= $cool_hues[0] && $hue <= $cool_hues[1] ) {
				$temperatures[] = 'cool';
			} else {
				$temperatures[] = 'neutral';
			}
		}

		$counts   = array_count_values( $temperatures );
		$dominant = array_search( max( $counts ), $counts, true );

		return array(
			'dominant'     => $dominant,
			'distribution' => $counts,
		);
	}

	/**
	 * Analyze contrast levels
	 *
	 * @param array $colors Colors to analyze
	 * @return array Contrast analysis
	 */
	private function analyze_contrast_levels( array $colors ): array {
		$contrasts  = array();
		$hex_colors = array_column( $colors, 'hex' );

		for ( $i = 0; $i < count( $hex_colors ); $i++ ) {
			for ( $j = $i + 1; $j < count( $hex_colors ); $j++ ) {
				$contrast    = $this->color_utility->get_contrast_ratio( $hex_colors[ $i ], $hex_colors[ $j ] );
				$contrasts[] = array(
					'colors'     => array( $hex_colors[ $i ], $hex_colors[ $j ] ),
					'ratio'      => $contrast,
					'passes_aa'  => $contrast >= 4.5,
					'passes_aaa' => $contrast >= 7.0,
				);
			}
		}

		return array(
			'pairs'   => $contrasts,
			'summary' => array(
				'total_pairs' => count( $contrasts ),
				'aa_passing'  => count( array_filter( $contrasts, fn( $c ) => $c['passes_aa'] ) ),
				'aaa_passing' => count( array_filter( $contrasts, fn( $c ) => $c['passes_aaa'] ) ),
			),
		);
	}
}
