<?php
/**
 * Color Scheme Generator Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Scheme_Generator_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;

/**
 * Class Color_Scheme_Generator
 *
 * Implements color scheme generation functionality
 */
class Color_Scheme_Generator implements Color_Scheme_Generator_Interface {
	/**
	 * Color utility instance
	 *
	 * @var Color_Utility
	 */
	private Color_Utility $color_utility;

	/**
	 * Constructor
	 *
	 * @param Color_Utility $color_utility Color utility instance
	 */
	public function __construct( Color_Utility $color_utility ) {
		$this->color_utility = $color_utility;
	}

	/**
	 * Generate a color scheme from a base color
	 *
	 * @param string $base_color Base color in hex format
	 * @param array  $options Generation options
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_scheme( string $base_color, array $options = array() ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		$scheme_type = $options['type'] ?? Color_Constants::SCHEME_MONOCHROMATIC;
		$count       = (int) ( $options['count'] ?? 5 );

		// Validate scheme type
		if ( ! isset( Color_Constants::REQUIRED_ROLES[ $scheme_type ] ) ) {
			return new WP_Error( 'invalid_scheme', 'Invalid scheme type provided' );
		}

		return $this->process_scheme_type( $scheme_type, $base_color, $count );
	}

	/**
	 * Process scheme type
	 *
	 * @param string $scheme_type Scheme type
	 * @param string $base_color Base color
	 * @param int    $count Number of colors
	 * @return array|WP_Error Generated colors or error
	 */
	private function process_scheme_type( string $scheme_type, string $base_color, int $count ): array|WP_Error {
		return match ( $scheme_type ) {
			Color_Constants::SCHEME_MONOCHROMATIC => $this->generate_monochromatic( $base_color, $count ),
			Color_Constants::SCHEME_ANALOGOUS => $this->generate_analogous( $base_color, $count ),
			Color_Constants::SCHEME_COMPLEMENTARY => $this->generate_complementary( $base_color, $count ),
			Color_Constants::SCHEME_SPLIT_COMPLEMENTARY => $this->generate_split_complementary( $base_color, $count ),
			Color_Constants::SCHEME_TRIADIC => $this->generate_triadic( $base_color, $count ),
			default => new WP_Error( 'invalid_scheme', __( 'Invalid scheme type provided', 'gl-color-palette-generator' ) )
		};
	}

	/**
	 * Generate a monochromatic scheme
	 *
	 * @param string $base_color Base color in hex format
	 * @param int    $count Number of colors to generate
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_monochromatic( string $base_color, int $count = 5 ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$hsv    = $this->color_utility->hex_to_hsv( $base_color );
		$colors = array( $base_color );

		for ( $i = 1; $i < $count; $i++ ) {
			$step     = $i / ( $count - 1 );
			$new_hsv  = array(
				'h' => $hsv['h'],
				's' => max( Color_Constants::MIN_SATURATION_RANGE, min( Color_Constants::MAX_SATURATION, $hsv['s'] + ( $step - 0.5 ) * Color_Constants::COLOR_METRICS['saturation']['step'] ) ),
				'v' => max( Color_Constants::MIN_VALUE_RANGE, min( Color_Constants::MAX_VALUE, $hsv['v'] + ( $step - 0.5 ) * Color_Constants::COLOR_METRICS['value']['step'] ) ),
			);
			$colors[] = $this->color_utility->hsv_to_hex( $new_hsv );
		}

		return $colors;
	}

	/**
	 * Generate an analogous scheme
	 *
	 * @param string $base_color Base color in hex format
	 * @param int    $count Number of colors to generate
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_analogous( string $base_color, int $count = 5 ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$hsv    = $this->color_utility->hex_to_hsv( $base_color );
		$colors = array( $base_color );

		// Generate analogous colors by shifting hue
		for ( $i = 1; $i < $count; $i++ ) {
			$step     = $i / ( $count - 1 );
			$new_hsv  = array(
				'h' => ( $hsv['h'] + ( $step - 0.5 ) * 60 + 360 ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			);
			$colors[] = $this->color_utility->hsv_to_hex( $new_hsv );
		}

		return $colors;
	}

	/**
	 * Generate a complementary scheme
	 *
	 * @param string $base_color Base color in hex format
	 * @param int    $count Number of colors to generate
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_complementary( string $base_color, int $count = 4 ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$hsv              = $this->color_utility->hex_to_hsv( $base_color );
		$complement_angle = Color_Constants::COLOR_HARMONY_RULES['complementary']['angle'];
		$complement_hue   = ( $hsv['h'] + $complement_angle ) % 360;
		$colors           = array( $base_color );

		// Add variations of the base color and its complement
		for ( $i = 1; $i < $count / 2; $i++ ) {
			$saturation = max( Color_Constants::MIN_SATURATION_RANGE, min( Color_Constants::MAX_SATURATION, $hsv['s'] - ( $i * 20 ) ) );
			$colors[]   = $this->color_utility->hsv_to_hex(
				array(
					'h' => $hsv['h'],
					's' => $saturation,
					'v' => $hsv['v'],
				)
			);
			$colors[]   = $this->color_utility->hsv_to_hex(
				array(
					'h' => $complement_hue,
					's' => $saturation,
					'v' => $hsv['v'],
				)
			);
		}

		$colors[] = $this->color_utility->hsv_to_hex(
			array(
				'h' => $complement_hue,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			)
		);

		return array_slice( $colors, 0, $count );
	}

	/**
	 * Generate a split complementary scheme
	 *
	 * @param string $base_color Base color in hex format
	 * @param int    $count Number of colors to generate
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_split_complementary( string $base_color, int $count = 3 ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$hsv    = $this->color_utility->hex_to_hsv( $base_color );
		$colors = array( $base_color );

		// Add split complements using the defined angle from constants
		$split_angle = Color_Constants::COLOR_HARMONY_RULES['split-complementary']['angle'];
		$colors[]    = $this->color_utility->hsv_to_hex(
			array(
				'h' => ( $hsv['h'] + $split_angle ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			)
		);
		$colors[]    = $this->color_utility->hsv_to_hex(
			array(
				'h' => ( $hsv['h'] - $split_angle + 360 ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			)
		);

		return array_slice( $colors, 0, $count );
	}

	/**
	 * Generate a triadic scheme
	 *
	 * @param string $base_color Base color in hex format
	 * @param int    $count Number of colors to generate
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_triadic( string $base_color, int $count = 3 ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$hsv    = $this->color_utility->hex_to_hsv( $base_color );
		$colors = array( $base_color );

		// Add colors using the triadic angle from constants
		$triadic_angle = Color_Constants::COLOR_HARMONY_RULES['triadic']['angle'];
		$colors[]      = $this->color_utility->hsv_to_hex(
			array(
				'h' => ( $hsv['h'] + $triadic_angle ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			)
		);
		$colors[]      = $this->color_utility->hsv_to_hex(
			array(
				'h' => ( $hsv['h'] + 2 * $triadic_angle ) % 360,
				's' => $hsv['s'],
				'v' => $hsv['v'],
			)
		);

		return array_slice( $colors, 0, $count );
	}

	/**
	 * Generate a tetradic scheme
	 *
	 * @param string $base_color Base color in hex format
	 * @param int    $count Number of colors to generate
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_tetradic( string $base_color, int $count = 4 ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$hsv    = $this->color_utility->hex_to_hsv( $base_color );
		$colors = array( $base_color );

		// Add colors at 90, 180, and 270 degrees
		for ( $i = 1; $i <= 3; $i++ ) {
			$hue      = ( $hsv['h'] + ( $i * 90 ) ) % 360;
			$colors[] = $this->color_utility->hsv_to_hex(
				array(
					'h' => $hue,
					's' => $hsv['s'],
					'v' => $hsv['v'],
				)
			);
		}

		return array_slice( $colors, 0, $count );
	}

	/**
	 * Get available color scheme types
	 *
	 * @return array List of available scheme types
	 */
	public function get_available_schemes(): array {
		return array_keys( Color_Constants::REQUIRED_ROLES );
	}

	/**
	 * Get color theory rules for scheme generation
	 *
	 * @return array List of available color theory rules
	 */
	public function get_color_theory_rules(): array {
		return array(
			Color_Constants::SCHEME_MONOCHROMATIC       => array(
				'description' => 'Different shades and tints of the same hue',
				'harmony'     => 'High',
				'use_case'    => 'Clean, minimalist designs',
			),
			Color_Constants::SCHEME_ANALOGOUS           => array(
				'description' => 'Colors adjacent on the color wheel',
				'harmony'     => 'High',
				'use_case'    => 'Natural, harmonious designs',
			),
			Color_Constants::SCHEME_COMPLEMENTARY       => array(
				'description' => 'Colors opposite on the color wheel',
				'harmony'     => 'Medium',
				'use_case'    => 'Vibrant, contrasting designs',
			),
			Color_Constants::SCHEME_SPLIT_COMPLEMENTARY => array(
				'description' => 'Base color plus two colors adjacent to its complement',
				'harmony'     => 'Medium',
				'use_case'    => 'Balanced, dynamic designs',
			),
			Color_Constants::SCHEME_TRIADIC             => array(
				'description' => 'Three colors equally spaced around the color wheel',
				'harmony'     => 'High',
				'use_case'    => 'Vibrant, balanced designs',
			),
			Color_Constants::SCHEME_TETRADIC            => array(
				'description' => 'Four colors with two pairs of complementary colors',
				'harmony'     => 'Medium',
				'use_case'    => 'Rich, complex designs',
			),
		);
	}

	/**
	 * Generate a custom scheme based on color theory rules
	 *
	 * @param string $base_color Base color in hex format
	 * @param array  $rules Color theory rules to apply
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_custom_scheme( string $base_color, array $rules ): array|WP_Error {
		if ( ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		if ( count( $rules ) === 0 ) {
			return new WP_Error( 'invalid_rules', 'Invalid or empty rules provided' );
		}

		$colors = array( $base_color );
		$hsv    = $this->color_utility->hex_to_hsv( $base_color );

		foreach ( $rules as $rule ) {
			if ( ! isset( $rule['type'], $rule['value'] ) ) {
				continue;
			}

			switch ( $rule['type'] ) {
				case 'hue_shift':
					$new_hue  = ( $hsv['h'] + $rule['value'] ) % 360;
					$colors[] = $this->color_utility->hsv_to_hex(
						array(
							'h' => $new_hue,
							's' => $hsv['s'],
							'v' => $hsv['v'],
						)
					);
					break;
				case 'saturation_shift':
					$new_saturation = max( Color_Constants::MIN_SATURATION_RANGE, min( Color_Constants::MAX_SATURATION, $hsv['s'] + $rule['value'] ) );
					$colors[]       = $this->color_utility->hsv_to_hex(
						array(
							'h' => $hsv['h'],
							's' => $new_saturation,
							'v' => $hsv['v'],
						)
					);
					break;
				case 'value_shift':
					$new_value = max( Color_Constants::MIN_VALUE_RANGE, min( Color_Constants::MAX_VALUE, $hsv['v'] + $rule['value'] ) );
					$colors[]  = $this->color_utility->hsv_to_hex(
						array(
							'h' => $hsv['h'],
							's' => $hsv['s'],
							'v' => $new_value,
						)
					);
					break;
			}
		}

		return $colors;
	}

	/**
	 * Generate a scheme from an image
	 *
	 * @param string $image_path Path to image file
	 * @param array  $options Extraction options
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_from_image( string $image_path, array $options = array() ): array|WP_Error {
		if ( ! file_exists( $image_path ) ) {
			return new WP_Error( 'invalid_image', 'Image file does not exist' );
		}

		$count = (int) ( $options['count'] ?? 5 );
		if ( $count < 2 || $count > 10 ) {
			return new WP_Error( 'invalid_count', 'Count must be between 2 and 10' );
		}

		$colors = array();

		try {
			// Create image resource based on file type
			$image_info = getimagesize( $image_path );
			if ( $image_info === false ) {
				return new WP_Error( 'invalid_image', 'Invalid image file' );
			}

			switch ( $image_info[2] ) {
				case IMAGETYPE_JPEG:
					$image = imagecreatefromjpeg( $image_path );
					break;
				case IMAGETYPE_PNG:
					$image = imagecreatefrompng( $image_path );
					break;
				case IMAGETYPE_GIF:
					$image = imagecreatefromgif( $image_path );
					break;
				default:
					return new WP_Error( 'unsupported_type', 'Unsupported image type' );
			}

			if ( $image === false ) {
				return new WP_Error( 'image_creation_failed', 'Failed to create image resource' );
			}

			// Resize image for faster processing
			$thumb_width  = 150;
			$thumb_height = (int) ( $image_info[1] * ( $thumb_width / $image_info[0] ) );
			$thumb        = imagecreatetruecolor( $thumb_width, $thumb_height );
			imagecopyresampled( $thumb, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $image_info[0], $image_info[1] );

			// Create color map
			$color_map = array();
			for ( $x = 0; $x < $thumb_width; $x++ ) {
				for ( $y = 0; $y < $thumb_height; $y++ ) {
					$rgb = imagecolorat( $thumb, $x, $y );
					$r   = ( $rgb >> 16 ) & 0xFF;
					$g   = ( $rgb >> 8 ) & 0xFF;
					$b   = $rgb & 0xFF;
					$hex = sprintf( '#%02x%02x%02x', $r, $g, $b );
					if ( ! isset( $color_map[ $hex ] ) ) {
						$color_map[ $hex ] = 0;
					}
					++$color_map[ $hex ];
				}
			}

			// Sort colors by frequency
			arsort( $color_map );
			$colors = array_slice( array_keys( $color_map ), 0, $count );

			// Clean up
			imagedestroy( $image );
			imagedestroy( $thumb );

			return $colors;
		} catch ( \Exception $e ) {
			return new WP_Error( 'image_processing_error', $e->getMessage() );
		}
	}

	/**
	 * Generate a themed scheme
	 *
	 * @param string $theme Theme or mood name
	 * @param array  $options Theme options
	 * @return array|WP_Error Array of colors or error
	 */
	public function generate_themed_scheme( string $theme, array $options = array() ): array|WP_Error {
		if ( $theme === '' ) {
			return new WP_Error( 'invalid_theme', 'Theme must not be empty' );
		}

		$base_color = $options['base_color'] ?? null;
		if ( $base_color !== null && ! $this->color_utility->is_valid_hex_color( $base_color ) ) {
			return new WP_Error( 'invalid_color', 'Invalid base color provided' );
		}

		// Generate base color if not provided
		if ( $base_color === null ) {
			$base_color = $this->generate_theme_base_color( $theme );
		}

		return $this->generate_scheme(
			$base_color,
			array(
				'type'  => $options['scheme_type'] ?? Color_Constants::SCHEME_MONOCHROMATIC,
				'count' => $options['count'] ?? 5,
			)
		);
	}

	/**
	 * Adjust scheme contrast
	 *
	 * @param array $colors Array of colors in hex format
	 * @param array $options Adjustment options
	 * @return array|WP_Error Array of colors or error
	 */
	public function adjust_scheme_contrast( array $colors, array $options = array() ): array|WP_Error {
		if ( count( $colors ) === 0 ) {
			return new WP_Error( 'invalid_colors', 'Colors array cannot be empty' );
		}

		$target_contrast = $options['target_contrast'] ?? Color_Constants::COLOR_METRICS['contrast']['target'];

		$adjusted_colors = array();
		foreach ( $colors as $color ) {
			if ( ! $this->color_utility->is_valid_hex_color( $color ) ) {
				return new WP_Error( 'invalid_color', 'Invalid color in scheme' );
			}

			$adjusted_colors[] = $this->adjust_single_color_contrast( $color, $target_contrast );
		}

		return $adjusted_colors;
	}

	/**
	 * Validate a color scheme
	 *
	 * @param array $colors Array of colors in hex format
	 * @param array $rules Validation rules
	 * @return bool|WP_Error True if valid, WP_Error if not
	 */
	public function validate_scheme( array $colors, array $rules = array() ): bool|WP_Error {
		if ( count( $colors ) === 0 ) {
			return new WP_Error( 'invalid_colors', 'Colors array cannot be empty' );
		}

		$scheme_type    = $rules['type'] ?? Color_Constants::SCHEME_MONOCHROMATIC;
		$required_roles = Color_Constants::REQUIRED_ROLES[ $scheme_type ] ?? Color_Constants::REQUIRED_ROLES[ Color_Constants::SCHEME_MONOCHROMATIC ];

		// Check required roles
		if ( count( $colors ) < count( $required_roles ) ) {
			return new WP_Error(
				'insufficient_colors',
				'Not enough colors for scheme type: ' . $scheme_type
			);
		}

		// Validate each color
		foreach ( $colors as $color ) {
			if ( ! $this->color_utility->is_valid_hex_color( $color ) ) {
				return new WP_Error( 'invalid_color', 'Invalid color in scheme' );
			}
		}

		// Validate contrast if specified
		if ( isset( $rules['min_contrast'] ) ) {
			$min_contrast = (float) $rules['min_contrast'];
			foreach ( $colors as $i => $color1 ) {
				foreach ( $colors as $j => $color2 ) {
					if ( $i !== $j ) {
						$contrast = $this->color_utility->get_contrast_ratio( $color1, $color2 );
						if ( $contrast < $min_contrast ) {
							return new WP_Error(
								'insufficient_contrast',
								sprintf(
									'Insufficient contrast between colors %s and %s: %f',
									$color1,
									$color2,
									$contrast
								)
							);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Generate a base color for a theme
	 *
	 * @param string $theme Theme name
	 * @return string Generated base color
	 */
	private function generate_theme_base_color( string $theme ): string {
		// Implementation depends on theme definitions
		// For now, return a default color
		return '#336699';
	}

	/**
	 * Adjust contrast of a single color
	 *
	 * @param string $color Color to adjust
	 * @param float  $target_contrast Target contrast ratio
	 * @return string Adjusted color
	 */
	private function adjust_single_color_contrast( string $color, float $target_contrast ): string {
		$hsv = $this->color_utility->hex_to_hsv( $color );

		// Adjust value to achieve target contrast
		if ( $target_contrast > Color_Constants::COLOR_METRICS['contrast']['target'] ) {
			$hsv['v'] = min( $hsv['v'] * 1.2, Color_Constants::MAX_VALUE );
		} else {
			$hsv['v'] = max( $hsv['v'] * 0.8, Color_Constants::MIN_VALUE_RANGE );
		}

		return $this->color_utility->hsv_to_hex( $hsv );
	}
}
