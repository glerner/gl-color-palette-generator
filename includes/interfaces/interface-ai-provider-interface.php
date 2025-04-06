<?php
/**
 * AI Provider Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface AI_Provider_Interface
 *
 * Defines the contract for AI providers
 *
 * @since 1.0.0
 */
interface AI_Provider_Interface {
	/**
	 * Generate text completion
	 *
	 * @param string $prompt The prompt to generate completion for
	 * @param array  $options Optional parameters for the generation
	 * @return string|WP_Error Generated text or error
	 */
	public function generate_completion( $prompt, $options = array() );

	/**
	 * Generate embeddings
	 *
	 * @param string $text Text to generate embeddings for
	 * @return array|WP_Error Array of embeddings or error
	 */
	public function generate_embeddings( $text );

	/**
	 * Get model information
	 *
	 * @return array Model information including name, version, capabilities
	 */
	public function get_model_info();
}
