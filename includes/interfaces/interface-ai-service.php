<?php
/**
 * AI Service Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface AI_Service
 *
 * Defines the contract for AI services
 *
 * @since 1.0.0
 */
interface AI_Service {
	/**
	 * Initialize the AI service
	 *
	 * @param array $config Service configuration
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function initialize( $config );

	/**
	 * Process input data
	 *
	 * @param mixed $input Input data to process
	 * @param array $options Processing options
	 * @return mixed|WP_Error Processed data or error
	 */
	public function process_input( $input, $options = array() );

	/**
	 * Get service status
	 *
	 * @return array Service status information
	 */
	public function get_status();

	/**
	 * Get service capabilities
	 *
	 * @return array List of service capabilities
	 */
	public function get_capabilities();
}
