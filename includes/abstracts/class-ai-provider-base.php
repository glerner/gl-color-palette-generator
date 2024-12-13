<?php
/**
 * Base AI Provider Class
 *
 * Base class for AI service providers that generate color palettes.
 * Handles authentication, API communication, and response processing.
 *
 * @package    GL_Color_Palette_Generator
 * @subpackage Abstracts
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GL_Color_Palette_Generator\Abstracts;

/**
 * Abstract class AI_Provider_Base
 *
 * Provides base functionality for AI service providers including
 * authentication, request handling, and response processing.
 *
 * @since 1.0.0
 */
abstract class AI_Provider_Base {
    /**
     * Provider credentials
     *
     * @var array
     */
    protected $credentials;

    /**
     * API base URL
     *
     * @var string
     */
    protected $api_url;

    /**
     * Generate a color palette
     *
     * @param array $params Parameters for palette generation
     * @return array|WP_Error Array of hex colors or error
     */
    abstract public function generate_palette(array $params);

    /**
     * Validate provider credentials
     *
     * @return bool|WP_Error True if valid, WP_Error if not
     */
    abstract public function validate_credentials();

    /**
     * Get provider requirements
     *
     * @return array Array of required fields and their descriptions
     */
    abstract public function get_requirements(): array;

    /**
     * Make an API request
     *
     * @param string $endpoint API endpoint
     * @param array  $data Request data
     * @return mixed|WP_Error Response data or error
     */
    protected function make_request(string $endpoint, array $data) {
        $url = trailingslashit($this->api_url) . $endpoint;

        $response = wp_remote_post($url, [
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
            return new \WP_Error('invalid_response', 'Invalid response from API');
        }

        return $data;
    }

    /**
     * Get request headers
     *
     * @return array Headers array
     */
    protected function get_headers(): array {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Validate generation parameters
     *
     * @param array $params Parameters to validate
     * @return true|WP_Error True if valid, WP_Error if not
     */
    protected function validate_params(array $params): bool|\WP_Error {
        $required = ['count', 'theme'];

        foreach ($required as $field) {
            if (empty($params[$field])) {
                return new \WP_Error('missing_parameter', "Missing required parameter: $field");
            }
        }

        if ($params['count'] < 2 || $params['count'] > 10) {
            return new \WP_Error('invalid_count', 'Color count must be between 2 and 10');
        }

        return true;
    }

    /**
     * Format the prompt for the AI
     *
     * @param array $params Generation parameters
     * @return string Formatted prompt
     */
    protected function format_prompt(array $params): string {
        $prompt = sprintf(
            'Generate a color palette with %d colors for a %s theme.',
            $params['count'],
            $params['theme']
        );

        if (!empty($params['description'])) {
            $prompt .= ' ' . $params['description'];
        }

        return $prompt;
    }

    /**
     * Parse the API response
     *
     * @param array $response API response
     * @return array|WP_Error Array of hex colors or error
     */
    protected function parse_response($response) {
        if (empty($response['choices'][0]['message']['content'])) {
            return new \WP_Error('empty_response', 'Empty response from API');
        }

        try {
            $colors = json_decode($response['choices'][0]['message']['content'], true);

            if (!is_array($colors)) {
                throw new \Exception('Invalid color array');
            }

            foreach ($colors as $color) {
                if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
                    throw new \Exception('Invalid hex color: ' . $color);
                }
            }

            return $colors;
        } catch (\Exception $e) {
            return new \WP_Error('invalid_colors', $e->getMessage());
        }
    }
}
