<?php
/**
 * Ajax Handler Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 */

namespace GL_Color_Palette_Generator\Core;

/**
 * Handles AJAX requests for the plugin
 */
class Ajax_Handler {
	/**
	 * Initialize the AJAX handler
	 */
	public function init() {
		// Add AJAX endpoints
		add_action( 'wp_ajax_gl_color_palette_generator_generate', array( $this, 'handle_generate_request' ) );
		add_action( 'wp_ajax_nopriv_gl_color_palette_generator_generate', array( $this, 'handle_generate_request' ) );
	}

	/**
	 * Handle palette generation request
	 */
	public function handle_generate_request() {
		// Verify nonce
		check_ajax_referer( 'gl_color_palette_generator_nonce', 'nonce' );

		// Get request parameters
		$image_url  = sanitize_text_field( $_POST['image_url'] ?? '' );
		$num_colors = intval( $_POST['num_colors'] ?? 5 );

		// TODO: Add actual palette generation logic
		wp_send_json_success( array( 'colors' => array() ) );
	}
}
