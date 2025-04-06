<?php
namespace GL_Color_Palette_Generator\Generators;

class ML_Color_Engine {
	protected $settings;
	protected $cache_duration = 3600; // 1 hour in seconds

	public function __construct() {
		$this->settings = get_option( 'gl_color_palette_generator_settings', array() );
	}

	public function generate_palette( $input ) {
		$theme       = $input['theme'] ?? '';
		$mood        = $input['mood'] ?? '';
		$count       = $input['count'] ?? 5;
		$constraints = $input['constraints'] ?? array();

		$colors = $this->generate_base_colors( $count, $theme, $mood );

		if ( ! empty( $constraints ) ) {
			$colors = $this->apply_constraints( $colors, $constraints );
		}

		return $colors;
	}

	public function validate_color( $color ) {
		if ( ! is_string( $color ) ) {
			return false;
		}

		return preg_match( '/^#[0-9A-F]{6}$/i', $color ) === 1;
	}

	public function calculate_contrast( $color1, $color2 ) {
		$l1 = $this->get_relative_luminance( $color1 );
		$l2 = $this->get_relative_luminance( $color2 );

		$ratio = ( $l1 > $l2 )
			? ( $l1 + 0.05 ) / ( $l2 + 0.05 )
			: ( $l2 + 0.05 ) / ( $l1 + 0.05 );

		return round( $ratio, 2 );
	}

	public function analyze_color_harmony( $colors ) {
		if ( empty( $colors ) ) {
			return array(
				'type'  => 'unknown',
				'score' => 0,
			);
		}

		$harmony_types = $this->detect_harmony_types( $colors );
		$harmony_score = $this->calculate_harmony_score( $colors );

		return array(
			'type'  => $harmony_types[0] ?? 'custom',
			'score' => $harmony_score,
		);
	}

	public function get_color_properties( $color ) {
		$rgb = $this->hex_to_rgb( $color );
		$hsv = $this->rgb_to_hsv( $rgb );

		return array(
			'hue'        => round( $hsv['h'] ),
			'saturation' => round( $hsv['s'] * 100 ),
			'lightness'  => round( $hsv['v'] * 100 ),
		);
	}

	public function generate_variations( $base_color, $count = 3 ) {
		$variations = array();
		$rgb        = $this->hex_to_rgb( $base_color );
		$hsv        = $this->rgb_to_hsv( $rgb );

		for ( $i = 0; $i < $count; $i++ ) {
			$new_hsv      = array(
				'h' => ( $hsv['h'] + ( 360 / $count ) * $i ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			);
			$variations[] = $this->hsv_to_hex( $new_hsv );
		}

		return $variations;
	}

	public function get_color_name( $color ) {
		$cached = get_transient( 'color_name_' . $color );
		if ( $cached !== false ) {
			return $cached;
		}

		$properties = $this->get_color_properties( $color );
		$name       = $this->generate_color_name( $properties );

		set_transient( 'color_name_' . $color, $name, $this->cache_duration );
		return $name;
	}

	public function get_complementary_color( $color ) {
		$rgb = $this->hex_to_rgb( $color );
		$hsv = $this->rgb_to_hsv( $rgb );

		$complementary_hsv = array(
			'h' => ( $hsv['h'] + 180 ) % 360,
			's' => $hsv['s'],
			'v' => $hsv['v'],
		);

		return $this->hsv_to_hex( $complementary_hsv );
	}

	public function get_analogous_colors( $color ) {
		$rgb = $this->hex_to_rgb( $color );
		$hsv = $this->rgb_to_hsv( $rgb );

		$analogous = array();
		$angles    = array( -30, 30 );

		foreach ( $angles as $angle ) {
			$new_hsv     = array(
				'h' => ( $hsv['h'] + $angle + 360 ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			);
			$analogous[] = $this->hsv_to_hex( $new_hsv );
		}

		return $analogous;
	}

	private function generate_base_colors( $count, $theme, $mood ) {
		$colors   = array();
		$base_hue = rand( 0, 360 );

		for ( $i = 0; $i < $count; $i++ ) {
			$hue        = ( $base_hue + ( 360 / $count ) * $i ) % 360;
			$saturation = 0.7 + ( rand( -20, 20 ) / 100 );
			$value      = 0.8 + ( rand( -20, 20 ) / 100 );

			$colors[] = $this->hsv_to_hex(
				array(
					'h' => $hue,
					's' => max( 0, min( 1, $saturation ) ),
					'v' => max( 0, min( 1, $value ) ),
				)
			);
		}

		return $colors;
	}

	private function apply_constraints( $colors, $constraints ) {
		if ( ! empty( $constraints['include_colors'] ) ) {
			$colors = array_merge( $constraints['include_colors'], $colors );
			$colors = array_slice( $colors, 0, count( $colors ) );
		}

		if ( ! empty( $constraints['exclude_colors'] ) ) {
			$colors = array_diff( $colors, $constraints['exclude_colors'] );
		}

		if ( ! empty( $constraints['min_contrast'] ) ) {
			$colors = $this->ensure_minimum_contrast( $colors, $constraints['min_contrast'] );
		}

		return array_values( $colors );
	}

	private function ensure_minimum_contrast( $colors, $min_contrast ) {
		$result = array();
		foreach ( $colors as $color ) {
			$valid = true;
			foreach ( $result as $existing ) {
				if ( $this->calculate_contrast( $color, $existing ) < $min_contrast ) {
					$valid = false;
					break;
				}
			}
			if ( $valid ) {
				$result[] = $color;
			}
		}
		return $result;
	}

	private function get_relative_luminance( $color ) {
		$rgb = $this->hex_to_rgb( $color );
		$rgb = array_map(
			function ( $val ) {
				$val = $val / 255;
				return $val <= 0.03928
				? $val / 12.92
				: pow( ( $val + 0.055 ) / 1.055, 2.4 );
			},
			$rgb
		);

		return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
	}

	private function hex_to_rgb( $hex ) {
		$hex = ltrim( $hex, '#' );
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	private function rgb_to_hsv( $rgb ) {
		$r = $rgb[0] / 255;
		$g = $rgb[1] / 255;
		$b = $rgb[2] / 255;

		$max  = max( $r, $g, $b );
		$min  = min( $r, $g, $b );
		$diff = $max - $min;

		$h = 0;
		$s = ( $max == 0 ) ? 0 : ( $diff / $max );
		$v = $max;

		if ( $diff != 0 ) {
			if ( $max == $r ) {
				$h = 60 * fmod( ( $g - $b ) / $diff, 6 );
			} elseif ( $max == $g ) {
				$h = 60 * ( ( $b - $r ) / $diff + 2 );
			} elseif ( $max == $b ) {
				$h = 60 * ( ( $r - $g ) / $diff + 4 );
			}
		}

		if ( $h < 0 ) {
			$h += 360;
		}

		return array(
			'h' => $h,
			's' => $s,
			'v' => $v,
		);
	}

	private function hsv_to_hex( $hsv ) {
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

		$r = round( ( $r + $m ) * 255 );
		$g = round( ( $g + $m ) * 255 );
		$b = round( ( $b + $m ) * 255 );

		return sprintf( '#%02X%02X%02X', $r, $g, $b );
	}

	private function detect_harmony_types( $colors ) {
		$types = array();
		$hues  = array_map(
			function ( $color ) {
				return $this->rgb_to_hsv( $this->hex_to_rgb( $color ) )['h'];
			},
			$colors
		);

		sort( $hues );
		$differences = array();
		for ( $i = 0; $i < count( $hues ) - 1; $i++ ) {
			$differences[] = $hues[ $i + 1 ] - $hues[ $i ];
		}

		if ( count( array_unique( $differences ) ) === 1 ) {
			$types[] = 'equidistant';
		}

		if ( count( $colors ) === 2 && abs( $hues[1] - $hues[0] ) === 180 ) {
			$types[] = 'complementary';
		}

		if ( empty( $types ) ) {
			$types[] = 'custom';
		}

		return $types;
	}

	private function calculate_harmony_score( $colors ) {
		$score             = 0;
		$total_comparisons = 0;

		for ( $i = 0; $i < count( $colors ); $i++ ) {
			for ( $j = $i + 1; $j < count( $colors ); $j++ ) {
				$contrast = $this->calculate_contrast( $colors[ $i ], $colors[ $j ] );
				$score   += min( $contrast, 7 ) / 7;
				++$total_comparisons;
			}
		}

		return $total_comparisons > 0 ? round( ( $score / $total_comparisons ) * 100 ) : 0;
	}

	private function generate_color_name( $properties ) {
		$base_names = array(
			array(
				'min'  => 0,
				'max'  => 30,
				'name' => 'Red',
			),
			array(
				'min'  => 30,
				'max'  => 60,
				'name' => 'Orange',
			),
			array(
				'min'  => 60,
				'max'  => 120,
				'name' => 'Yellow',
			),
			array(
				'min'  => 120,
				'max'  => 180,
				'name' => 'Green',
			),
			array(
				'min'  => 180,
				'max'  => 240,
				'name' => 'Blue',
			),
			array(
				'min'  => 240,
				'max'  => 300,
				'name' => 'Purple',
			),
			array(
				'min'  => 300,
				'max'  => 360,
				'name' => 'Pink',
			),
		);

		foreach ( $base_names as $range ) {
			if ( $properties['hue'] >= $range['min'] && $properties['hue'] < $range['max'] ) {
				if ( $properties['saturation'] < 10 ) {
					return 'Gray';
				}
				if ( $properties['lightness'] > 90 ) {
					return 'Light ' . $range['name'];
				}
				if ( $properties['lightness'] < 20 ) {
					return 'Dark ' . $range['name'];
				}
				return $range['name'];
			}
		}

		return 'Custom';
	}
}
