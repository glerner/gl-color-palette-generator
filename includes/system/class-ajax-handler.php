<?php
/**
 * AJAX Handler
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Handles AJAX requests for the plugin
 */
class Ajax_Handler {
    /**
     * Initialize AJAX handlers
     */
    public function init(): void {
        add_action('wp_ajax_gl_test_api_connection', [$this, 'test_api_connection']);
        add_action('wp_ajax_gl_save_settings', [$this, 'save_settings']);
    }

    /**
     * Test API connection
     */
    public function test_api_connection(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'gl-color-palette-generator')
            ]);
        }

        $provider = filter_input(INPUT_POST, 'provider', FILTER_SANITIZE_STRING);
        $api_key = filter_input(INPUT_POST, 'api_key', FILTER_SANITIZE_STRING);

        if (empty($provider) || empty($api_key)) {
            wp_send_json_error([
                'message' => __('Missing required parameters', 'gl-color-palette-generator')
            ]);
        }

        // Get provider configuration
        $provider_class = "\\GLColorPalette\\Providers\\" . ucfirst($provider) . '_Config';
        if (!class_exists($provider_class)) {
            wp_send_json_error([
                'message' => __('Invalid provider', 'gl-color-palette-generator')
            ]);
        }

        $provider_config = new $provider_class();

        // Test connection
        try {
            $response = wp_remote_post($provider_config->get_api_endpoint(), [
                'headers' => [
                    'Authorization' => "Bearer $api_key",
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'messages' => [
                        ['role' => 'user', 'content' => 'Test connection']
                    ],
                    'model' => $provider_config->get_default_model(),
                    'max_tokens' => 10
                ])
            ]);

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                throw new \Exception(
                    sprintf(
                        __('API returned error code: %d', 'gl-color-palette-generator'),
                        $response_code
                    )
                );
            }

            wp_send_json_success([
                'message' => __('Connection successful', 'gl-color-palette-generator')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Save settings
     */
    public function save_settings(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'gl-color-palette-generator')
            ]);
        }

        $settings = filter_input(
            INPUT_POST,
            'settings',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );

        if (empty($settings)) {
            wp_send_json_error([
                'message' => __('No settings provided', 'gl-color-palette-generator')
            ]);
        }

        foreach ($settings as $key => $value) {
            update_option("gl_color_palette_$key", $value);
        }

        wp_send_json_success([
            'message' => __('Settings saved successfully', 'gl-color-palette-generator')
        ]);
    }
} 
