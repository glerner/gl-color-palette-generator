<?php
/**
 * Color Validation Class
 *
 * Handles high-level color and palette validation:
 * - Validates color combinations and relationships
 * - Checks accessibility requirements (contrast ratios)
 * - Validates color palette harmony and balance
 * - Provides palette-wide validation rules
 *
 * Note: This class focuses on color palette validation as a whole.
 * For low-level color format validation, see Color_Validator class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Validation
 * @since 1.0.0
 * @todo Consider merging with Color_Validator class in future versions
 */

namespace GL_Color_Palette_Generator\Validation;

use WP_Error;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Color_Validation
 *
 * Handles validation of colors and color palettes
 *
 * @since 1.0.0
 */
class Color_Validation {
	/**
	 * Color utility instance
	 *
	 * @var Color_Utility
	 */
	private $color_utility;

	/**
	 * Constructor
	 *
	 * @param Color_Utility $color_utility Color utility instance
	 */
	public function __construct( Color_Utility $color_utility ) {
		$this->color_utility = $color_utility;
	}

	/**
	 * Validate color format
	 *
	 * @param string $color The color value to validate
	 * @param string $format The expected format (hex, rgb, hsl)
	 * @return array Validation results
	 */
	public function validate_color( $color, $format = 'hex' ) {
		try {
			$result = array(
				'is_valid'         => false,
				'message'          => '',
				'normalized_value' => '',
				'format'           => $format,
			);

			switch ( $format ) {
				case 'hex':
					$result = $this->validate_hex_color( $color );
					break;
				case 'rgb':
					$result = $this->validate_rgb_color( $color );
					break;
				case 'hsl':
					$result = $this->validate_hsl_color( $color );
					break;
				default:
					throw new \Exception( "Unsupported color format: {$format}" );
			}

			return $result;
		} catch ( \Exception $e ) {
			return array(
				'is_valid'         => false,
				'message'          => $e->getMessage(),
				'normalized_value' => '',
				'format'           => $format,
			);
		}
	}

	/**
	 * Validate hex color format
	 *
	 * @param string $color Hex color to validate
	 * @return array Validation results
	 */
	private function validate_hex_color( $color ) {
		$pattern  = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
		$is_valid = preg_match( $pattern, $color );

		return array(
			'is_valid'         => (bool) $is_valid,
			'message'          => $is_valid ? 'Valid hex color' : 'Invalid hex color format',
			'normalized_value' => strtoupper( $color ),
			'format'           => 'hex',
		);
	}

	/**
	 * Validate RGB color format
	 *
	 * @param string|array $color RGB color to validate
	 * @return array Validation results
	 */
	private function validate_rgb_color( $color ) {
		if ( is_string( $color ) ) {
			$pattern  = '/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/';
			$is_valid = preg_match( $pattern, $color );
		} elseif ( is_array( $color ) ) {
			$is_valid = count( $color ) === 3 &&
						array_reduce(
							$color,
							function ( $carry, $item ) {
								return $carry && is_numeric( $item ) && $item >= 0 && $item <= 255;
							},
							true
						);
		} else {
			$is_valid = false;
		}

		return array(
			'is_valid'         => $is_valid,
			'message'          => $is_valid ? 'Valid RGB color' : 'Invalid RGB color format',
			'normalized_value' => $color,
			'format'           => 'rgb',
		);
	}

	/**
	 * Validate HSL color format
	 *
	 * @param string|array $color HSL color to validate
	 * @return array Validation results
	 */
	private function validate_hsl_color( $color ): array {
		if ( is_string( $color ) ) {
			$pattern  = '/^hsl\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%\)$/';
			$is_valid = preg_match( $pattern, $color );
		} elseif ( is_array( $color ) ) {
			$is_valid = count( $color ) === 3 &&
						is_numeric( $color[0] ) && $color[0] >= 0 && $color[0] < 360 &&
						is_numeric( $color[1] ) && $color[1] >= 0 && $color[1] <= 100 &&
						is_numeric( $color[2] ) && $color[2] >= 0 && $color[2] <= 100;
		} else {
			$is_valid = false;
		}

		return array(
			'is_valid'         => $is_valid,
			'message'          => $is_valid ? 'Valid HSL color' : 'Invalid HSL color format',
			'normalized_value' => $color,
			'format'           => 'hsl',
		);
	}

	/**
	 * Validate contrast requirements
	 *
	 * @param string $color Color to validate
	 * @return array Validation results
	 */
	private function validate_contrast_requirements( string $color ): array {
		$results   = array();
		$rgb       = $this->color_utility->hex_to_rgb( $color );
		$luminance = $this->color_utility->get_relative_luminance( $color );

		// Check contrast with off-white and near-black for better visual comfort
		$light_contrast = $this->color_utility->get_contrast_ratio(
			$color,
			Color_Constants::COLOR_OFF_WHITE  // Off-white for better visual comfort
		);
		$dark_contrast  = $this->color_utility->get_contrast_ratio(
			$color,
			Color_Constants::COLOR_NEAR_BLACK  // Near-black for better visual comfort
		);

		$min_contrast = Color_Constants::ACCESSIBILITY_CONFIG['contrast']['min_ratio'];

		$results['passes_light_contrast'] = $light_contrast >= $min_contrast;
		$results['passes_dark_contrast']  = $dark_contrast >= $min_contrast;
		$results['light_contrast_ratio']  = $light_contrast;
		$results['dark_contrast_ratio']   = $dark_contrast;

		return $results;
	}

	/**
	 * Validate color combinations for contrast and accessibility
	 *
	 * @param array $colors Array of colors to validate
	 * @return array Validation results for the combination
	 */
	public function validate_color_combinations( $colors ) {
		$validation_results = array();

		foreach ( $colors as $index => $color ) {
			$validation_results[ $index ] = array(
				'color'               => $color,
				'format_valid'        => $this->validate_color( $color ),
				'contrast_valid'      => $this->validate_contrast_requirements( $color ),
				'accessibility_valid' => $this->validate_accessibility_requirements( $color ),
				'harmony_valid'       => $this->validate_harmony_rules( $color ),
			);
		}

		return array(
			'validation_results' => $validation_results,
			'overall_valid'      => $this->check_overall_validity( $validation_results ),
			'recommendations'    => $this->generate_validation_recommendations( $validation_results ),
		);
	}

	/**
	 * Validate palette structure and relationships
	 *
	 * @param array $palette The palette to validate
	 * @return array Structure validation results
	 */
	public function validate_palette( $palette ) {
		return array(
			'structure_validation'    => array(
				'required_colors' => $this->validate_required_colors( $palette ),
				'color_roles'     => $this->validate_color_roles( $palette ),
				'hierarchy'       => $this->validate_color_hierarchy( $palette ),
			),
			'relationship_validation' => array(
				'contrast_relationships'      => $this->validate_contrast_relationships( $palette ),
				'harmony_relationships'       => $this->validate_harmony_relationships( $palette ),
				'accessibility_relationships' => $this->validate_accessibility_relationships( $palette ),
			),
			'metadata_validation'     => array(
				'naming_convention' => $this->validate_naming_convention( $palette ),
				'documentation'     => $this->validate_documentation( $palette ),
				'version_control'   => $this->validate_version_control( $palette ),
			),
		);
	}

	/**
	 * Validate accessibility requirements
	 *
	 * @param string $color Color to validate
	 * @return bool True if valid
	 */
	private function validate_accessibility_requirements( $color ) {
		// Implementation for accessibility validation
		return true; // Placeholder
	}

	/**
	 * Validate harmony rules
	 *
	 * @param string $color Color to validate
	 * @return bool True if valid
	 */
	private function validate_harmony_rules( $color ) {
		// Implementation for harmony validation
		return true; // Placeholder
	}

	private function check_overall_validity( $validation_results ) {
		// Implementation for checking overall validity
		return true; // Placeholder
	}

	private function generate_validation_recommendations( $validation_results ) {
		// Implementation for generating recommendations
		return array(); // Placeholder
	}

	private function validate_required_colors( $palette ) {
		// Implementation for required colors validation
		return true; // Placeholder
	}

	private function validate_color_roles( $palette ) {
		// Implementation for color roles validation
		return true; // Placeholder
	}

	private function validate_color_hierarchy( $palette ) {
		// Implementation for hierarchy validation
		return true; // Placeholder
	}

	private function validate_contrast_relationships( $palette ) {
		// Implementation for contrast relationships validation
		return true; // Placeholder
	}

	private function validate_harmony_relationships( $palette ) {
		// Implementation for harmony relationships validation
		return true; // Placeholder
	}

	private function validate_accessibility_relationships( $palette ) {
		// Implementation for accessibility relationships validation
		return true; // Placeholder
	}

	private function validate_naming_convention( $palette ) {
		// Implementation for naming convention validation
		return true; // Placeholder
	}

	private function validate_documentation( $palette ) {
		// Implementation for documentation validation
		return true; // Placeholder
	}

	private function validate_version_control( $palette ) {
		// Implementation for version control validation
		return true; // Placeholder
	}
}
