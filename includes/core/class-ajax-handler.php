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
        add_action('wp_ajax_gl_color_palette_generator_generate', [$this, 'handle_generate_request']);
        add_action('wp_ajax_nopriv_gl_color_palette_generator_generate', [$this, 'handle_generate_request']);
    }

    /**
     * Handle palette generation request
     */
    public function handle_generate_request() {
        // Verify nonce
        check_ajax_referer('gl_color_palette_generator_nonce', 'nonce');

        // Get request parameters
        $prompt = sanitize_text_field($_POST['prompt'] ?? '');
        $count = intval($_POST['count'] ?? 5);
        $format = sanitize_text_field($_POST['format'] ?? 'hex');

        // Generate palette
        $result = ['success' => true, 'data' => []];

        wp_send_json($result);
    }
}
