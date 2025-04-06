<?php declare(strict_types=1);
/**
 * Compliance Checker Class
 *
 * Implements comprehensive accessibility compliance checking functionality
 * for WCAG 2.1 and Section 508 standards.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Accessibility
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Accessibility;

use GL_Color_Palette_Generator\Interfaces\Compliance_Checker_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Compliance_Types;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;

/**
 * Compliance Checker Class
 *
 * @since 1.0.0
 */
class Compliance_Checker implements Compliance_Checker_Interface {
	/**
	 * WCAG Requirements
	 */
	private const WCAG_REQUIREMENTS = array(
		'contrast_ratios' => array(
			'target'  => Color_Constants::WCAG_CONTRAST_TARGET,  // 7.0 - AAA level
			'minimum' => Color_Constants::WCAG_CONTRAST_MIN,     // 4.7 - Above AA level
		),
	);

	/**
	 * Section 508 compliance configurations
	 */
	private const SECTION_508_REQUIREMENTS = array(
		'contrast_ratios' => array(
			'minimum' => Color_Constants::WCAG_CONTRAST_MIN,
			'target'  => Color_Constants::WCAG_CONTRAST_TARGET,
		),
		'color_blindness' => array(
			'deuteranopia' => true,
			'protanopia'   => true,
			'tritanopia'   => true,
		),
	);

	/**
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
	 * Check WCAG compliance for a color palette
	 *
	 * @param array $colors Array of hex color codes
	 * @return array Compliance status and details
	 */
	public function check_wcag_compliance( array $colors ): array {
		$results = array(
			'status'     => 'pass',
			'level'      => null,
			'details'    => array(),
			'violations' => array(),
		);

		// Check all color combinations for contrast ratios
		foreach ( $colors as $i => $color1 ) {
			foreach ( $colors as $j => $color2 ) {
				if ( $i === $j ) {
					continue;
				}

				$ratio = $this->validate_contrast_ratio( $color1, $color2 );
				$level = $this->determine_wcag_level( $ratio );

				if ( ! $level ) {
					$results['status']       = 'fail';
					$results['violations'][] = array(
						'colors'   => array( $color1, $color2 ),
						'ratio'    => $ratio,
						'required' => self::WCAG_REQUIREMENTS['contrast_ratios']['minimum'],
					);
				} elseif ( $results['level'] === null || $level === 'AAA' ) {
					$results['level'] = $level;
				}

				$results['details'][] = array(
					'colors' => array( $color1, $color2 ),
					'ratio'  => $ratio,
					'level'  => $level,
				);
			}
		}

		return $results;
	}

	/**
	 * Check Section 508 compliance for a color palette
	 *
	 * @param array $colors Array of hex color codes
	 * @return array Compliance status and details
	 */
	public function check_section508_compliance( array $colors ): array {
		$results = array(
			'status'     => 'pass',
			'details'    => array(),
			'violations' => array(),
		);

		// Check contrast requirements
		foreach ( $colors as $i => $color1 ) {
			foreach ( $colors as $j => $color2 ) {
				if ( $i === $j ) {
					continue;
				}

				$ratio = $this->validate_contrast_ratio( $color1, $color2 );
				if ( $ratio < self::SECTION_508_REQUIREMENTS['contrast_ratios']['minimum'] ) {
					$results['status']       = 'fail';
					$results['violations'][] = array(
						'type'     => 'contrast',
						'colors'   => array( $color1, $color2 ),
						'ratio'    => $ratio,
						'required' => self::SECTION_508_REQUIREMENTS['contrast_ratios']['minimum'],
					);
				}
			}
		}

		// Check color blindness considerations
		foreach ( self::SECTION_508_REQUIREMENTS['color_blindness'] as $type => $required ) {
			if ( $required === false ) {
				continue;  // Skip if this type of color blindness testing is not enabled
			}

			$simulation = $this->simulate_color_blindness( $colors, $type );
			// First check if simulation returned valid results
			if ( count( $simulation ) > 0 ) {
				// Then check if the colors are distinguishable
				$is_distinguishable = $this->validate_color_blindness_distinction( $simulation );
				if ( ! $is_distinguishable ) {
					$results['status']       = 'fail';
					$results['violations'][] = array(
						'type'      => 'color_blindness',
						'condition' => $type,
						'colors'    => $colors,
					);
				}
			}
		}

		return $results;
	}

	/**
	 * Analyze detailed compliance status
	 *
	 * @param array $status Compliance check results
	 * @return array Detailed analysis and recommendations
	 */
	public function analyze_compliance_details( array $status ): array {
		$analysis = array(
			'summary'         => array(),
			'critical_issues' => array(),
			'warnings'        => array(),
			'recommendations' => array(),
		);

		if ( isset( $status['violations'] ) && $status['violations'] ) {
			foreach ( $status['violations'] as $violation ) {
				if ( isset( $violation['type'] ) && $violation['type'] === 'contrast' ) {
					$analysis['critical_issues'][] = sprintf(
						'Insufficient contrast ratio (%f) between colors %s and %s. Minimum required: %f',
						$violation['ratio'],
						$violation['colors'][0],
						$violation['colors'][1],
						$violation['required']
					);
				} elseif ( isset( $violation['type'] ) && $violation['type'] === 'color_blindness' ) {
					$analysis['critical_issues'][] = sprintf(
						'Colors may not be distinguishable for users with %s',
						$violation['condition']
					);
				}
			}
		}

		if ( count( $analysis['critical_issues'] ) === 0 ) {
			$analysis['summary'][] = 'Palette meets basic accessibility requirements';
		} else {
			$analysis['summary'][] = sprintf(
				'Palette has %d critical accessibility issues that need attention',
				count( $analysis['critical_issues'] )
			);
		}

		return $analysis;
	}

	/**
	 * Generate compliance recommendations
	 *
	 * @return array List of recommendations for improving compliance
	 */
	public function generate_compliance_recommendations(): array {
		return array(
			'contrast'        => array(
				sprintf(
					'Target contrast ratio is %s:1 (WCAG AAA). If not achievable, minimum acceptable is %s:1 (above WCAG AA)',
					Color_Constants::WCAG_CONTRAST_TARGET,
					Color_Constants::WCAG_CONTRAST_MIN
				),
				'Note: We do not test separately for large text contrast, as text size is determined by theme implementation',
				sprintf(
					'Maximum comfortable contrast is %s:1 to prevent eye strain',
					Color_Constants::CONTRAST_MAX
				),
			),
			'color_blindness' => array(
				'Avoid relying solely on color to convey information',
				'Use patterns or icons in addition to color for important UI elements',
				'Color blindness testing will be implemented in a future version',
			),
			'general'         => array(
				'Light mode: Ensure dark text colors maintain sufficient contrast with light backgrounds',
				'Dark mode: Ensure light text colors maintain sufficient contrast with dark backgrounds',
				'Include focus indicators that meet contrast requirements',
				sprintf(
					'Light backgrounds should have luminance above %s, dark backgrounds below %s',
					Color_Constants::LIGHT_LUMINANCE_THRESHOLD,
					Color_Constants::DARK_LUMINANCE_THRESHOLD
				),
			),
		);
	}

	/**
	 * Validate contrast ratio between two colors
	 *
	 * @param string $color1 First hex color code
	 * @param string $color2 Second hex color code
	 * @return float Contrast ratio value
	 */
	public function validate_contrast_ratio( string $color1, string $color2 ): float {
		return $this->color_utility->get_contrast_ratio( $color1, $color2 );
	}

	/**
	 * Determine WCAG compliance level based on contrast ratio
	 *
	 * @param float $ratio Contrast ratio
	 * @return string|false 'AAA', 'AA', or false if non-compliant
	 */
	private function determine_wcag_level( float $ratio ): string|false {
		// Check AAA compliance first (most strict)
		if ( $ratio >= Color_Constants::WCAG_CONTRAST_AAA ) {
			return 'AAA';
		}

		// Then check AA compliance
		if ( $ratio >= Color_Constants::WCAG_CONTRAST_AA ) {
			return 'AA';
		}

		// Return false if neither level is met
		return false;
	}

	/**
	 * Process compliance level
	 *
	 * @param string|false $level Compliance level
	 * @return array Results array
	 */
	private function process_compliance_level( $level ): array {
		if ( ! $level ) {
			return array(
				'status'  => 'fail',
				'level'   => 'none',
				'message' => 'Does not meet minimum contrast requirements',
			);
		}

		return array(
			'status'  => 'pass',
			'level'   => $level,
			'message' => "Meets WCAG {$level} requirements",
		);
	}

	/**
	 * Color blindness simulation and validation will be implemented after v1.0 release
	 *
	 * @todo Implement color blindness simulation algorithms
	 * @see https://github.com/glerner/gl-color-palette-generator/blob/main/.github/issues/implement-color-blindness-testing.md
	 */
	private function simulate_color_blindness( array $colors, string $type ): array {
		return $colors; // Placeholder until v1.1
	}

	/**
	 * Color blindness validation will be implemented in v1.1
	 *
	 * @todo Implement color distinction validation for color blindness
	 * @see https://github.com/glerner/gl-color-palette-generator/blob/main/.github/issues/implement-color-blindness-testing.md
	 */
	private function validate_color_blindness_distinction( array $colors ): bool {
		return true; // Placeholder until v1.1
	}
}
