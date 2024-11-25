<?php
/**
 * Abstract AI Provider Class
 *
 * @package GLColorPalette
 * @subpackage Abstracts
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Abstracts;

use GL_Color_Palette_Generator\Interfaces\AI_Provider;

/**
 * Abstract class AI_Provider_Base
 *
 * Provides common functionality for AI providers.
 */
abstract class AI_Provider_Base implements AI_Provider {
    /**
     * API endpoint URL.
     *
     * @var string
     */
    protected string $api_url;

    /**
     * API credentials.
     *
     * @var array
     */
    protected array $credentials;

    /**
     * Validate input parameters.
     *
     * @param array $params Parameters to validate.
     * @return bool|WP_Error True if valid, WP_Error on failure.
     */
    protected function validate_params(array $params) {
        if (empty($params['base_color']) || !preg_match('/^#[a-fA-F0-9]{6}$/', $params['base_color'])) {
            return new \WP_Error('invalid_base_color', 'Invalid base color format');
        }

        if (empty($params['mode']) || !in_array($params['mode'], ['analogous', 'complementary', 'triadic', 'monochromatic'])) {
            return new \WP_Error('invalid_mode', 'Invalid palette mode');
        }

        if (empty($params['count']) || $params['count'] < 2 || $params['count'] > 10) {
            return new \WP_Error('invalid_count', 'Color count must be between 2 and 10');
        }

        return true;
    }

    /**
     * Make API request.
     *
     * @param string $endpoint API endpoint.
     * @param array  $data Request data.
     * @return array|WP_Error Response data or WP_Error on failure.
     */
    protected function make_request(string $endpoint, array $data) {
        $response = wp_remote_post($this->api_url . $endpoint, [
            'headers' => $this->get_headers(),
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error('invalid_response', 'Invalid API response');
        }

        return $data;
    }

    /**
     * Get request headers.
     *
     * @return array Headers array.
     */
    abstract protected function get_headers(): array;
}
