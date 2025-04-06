<?php
/**
 * Color Palette Preview Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for color palette previews
 */
interface Color_Palette_Preview_Interface {
	/**
	 * Generate preview image
	 *
	 * @param array  $colors Array of hex colors
	 * @param string $format Output format ('png', 'jpg', 'svg')
	 * @param array  $options Preview options
	 * @return string|WP_Error Preview image data URI or error
	 */
	public function generate_preview( $colors, $format = 'png', $options = array() );

	/**
	 * Generate preview HTML
	 *
	 * @param array $colors Array of hex colors
	 * @param array $options Preview options
	 * @return string|WP_Error Preview HTML or error
	 */
	public function generate_html( $colors, $options = array() );

	/**
	 * Generate preview CSS
	 *
	 * @param array $colors Array of hex colors
	 * @param array $options Preview options
	 * @return string|WP_Error Preview CSS or error
	 */
	public function generate_css( $colors, $options = array() );

	/**
	 * Generate preview for specific context
	 *
	 * @param array  $colors Array of hex colors
	 * @param string $context Preview context ('website', 'branding', 'print')
	 * @param array  $options Preview options
	 * @return string|WP_Error Preview data or error
	 */
	public function generate_context_preview( $colors, $context, $options = array() );

	/**
	 * Get preview templates
	 *
	 * @return array List of available preview templates
	 */
	public function get_templates();

	/**
	 * Apply preview template
	 *
	 * @param array  $colors Array of hex colors
	 * @param string $template_id Template identifier
	 * @param array  $options Template options
	 * @return string|WP_Error Preview data or error
	 */
	public function apply_template( $colors, $template_id, $options = array() );

	/**
	 * Generate color swatches
	 *
	 * @param array $colors Array of hex colors
	 * @param array $options Swatch options
	 * @return string|WP_Error Swatch HTML or error
	 */
	public function generate_swatches( $colors, $options = array() );

	/**
	 * Generate color information
	 *
	 * @param array $colors Array of hex colors
	 * @param array $options Information options
	 * @return array|WP_Error Color information or error
	 */
	public function generate_info( $colors, $options = array() );

	/**
	 * Check if preview format is supported
	 *
	 * @param string $format Format to check
	 * @return bool True if format is supported
	 */
	public function is_format_supported( $format );

	/**
	 * Get supported preview formats
	 *
	 * @return array List of supported formats
	 */
	public function get_supported_formats();
}
