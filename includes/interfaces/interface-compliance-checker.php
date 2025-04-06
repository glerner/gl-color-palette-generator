<?php declare(strict_types=1);
/**
 * Compliance Checker Interface
 *
 * Defines the contract for accessibility compliance checking functionality.
 * Includes methods for WCAG and Section 508 compliance validation.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Compliance Checker Interface
 *
 * @since 1.0.0
 */
interface Compliance_Checker_Interface {
	/**
	 * Check WCAG compliance for a color palette
	 *
	 * @param array $colors Array of hex color codes
	 * @return array Compliance status and details
	 */
	public function check_wcag_compliance( array $colors ): array;

	/**
	 * Check Section 508 compliance for a color palette
	 *
	 * @param array $colors Array of hex color codes
	 * @return array Compliance status and details
	 */
	public function check_section508_compliance( array $colors ): array;

	/**
	 * Analyze detailed compliance status
	 *
	 * @param array $status Compliance check results
	 * @return array Detailed analysis and recommendations
	 */
	public function analyze_compliance_details( array $status ): array;

	/**
	 * Generate compliance recommendations
	 *
	 * @return array List of recommendations for improving compliance
	 */
	public function generate_compliance_recommendations(): array;

	/**
	 * Validate contrast ratio between two colors
	 *
	 * @param string $color1 First hex color code
	 * @param string $color2 Second hex color code
	 * @return float Contrast ratio value
	 */
	public function validate_contrast_ratio( string $color1, string $color2 ): float;
}
