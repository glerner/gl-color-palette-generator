<?php
/**
 * Business Analyzer Interface
 *
 * Defines the contract for analyzing color palettes in business contexts.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface BusinessAnalyzer
 *
 * Provides methods for analyzing color palettes in business and branding contexts.
 */
interface BusinessAnalyzer {
	/**
	 * Analyzes how well a color palette aligns with brand guidelines
	 *
	 * @param array $palette Color palette to analyze
	 * @return array {
	 *     Analysis results
	 *     @type float  $compatibility_score Overall compatibility score (0-1)
	 *     @type array  $strengths          List of palette strengths
	 *     @type array  $weaknesses         List of potential issues
	 *     @type array  $suggestions        Improvement suggestions
	 * }
	 */
	public function analyze_brand_compatibility( array $palette ): array;

	/**
	 * Get color recommendations for specific industries
	 *
	 * @param string $industry Industry identifier (e.g., 'tech', 'healthcare', 'finance')
	 * @return array {
	 *     Industry-specific color recommendations
	 *     @type array  $recommended_colors List of recommended colors
	 *     @type array  $color_meanings    Psychological meanings of colors
	 *     @type array  $industry_trends   Current color trends in the industry
	 *     @type string $rationale         Explanation of recommendations
	 * }
	 */
	public function get_industry_recommendations( string $industry ): array;

	/**
	 * Generate guidelines for using the color palette
	 *
	 * @param array $palette Color palette to generate guidelines for
	 * @return array {
	 *     Usage guidelines
	 *     @type array  $primary_uses      Recommended primary color uses
	 *     @type array  $secondary_uses    Recommended secondary color uses
	 *     @type array  $accessibility     Accessibility considerations
	 *     @type array  $combinations      Recommended color combinations
	 *     @type string $style_guide       Generated style guide content
	 * }
	 */
	public function generate_usage_guidelines( array $palette ): array;
}
