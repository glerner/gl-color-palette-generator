<?php
/**
 * OpenAI Provider Class
 *
 * @package GLColorPalette
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

use GLColorPalette\Abstracts\AI_Provider_Base;

/**
 * Class OpenAI_Provider
 *
 * OpenAI-specific implementation of the AI provider interface.
 */
class OpenAI_Provider extends AI_Provider_Base {
    /**
     * Constructor.
     *
     * @param array $credentials API credentials.
     */
    public function __construct(array $credentials) {
        $this->api_url = 'https://api.openai.com/v1/';
        $this->credentials = $credentials;
    }

    /**
     * Generate color palette.
     *
     * @param array $params Generation parameters.
     * @return array|WP_Error Color array or error.
     */
    public function generate_palette(array $params) {
        $validation = $this->validate_params($params);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $prompt = $this->format_prompt($params);
        $response = $this->make_request('chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a color palette generator. Respond only with JSON array of hex colors.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->parse_response($response);
    }

    /**
     * Get headers for API requests.
     *
     * @return array Headers array.
     */
    protected function get_headers(): array {
        return [
            'Authorization' => 'Bearer ' . $this->credentials['api_key'],
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Format prompt for the API.
     *
     * @param array $params Generation parameters.
     * @return string Formatted prompt.
     */
    private function format_prompt(array $params): string {
        return sprintf(
            'Generate a %s color palette with %d colors, based on the color %s.',
            $params['mode'],
            $params['count'],
            $params['base_color']
        );
    }

    /**
     * Parse API response.
     *
     * @param array $response API response.
     * @return array|WP_Error Parsed colors or error.
     */
    private function parse_response(array $response) {
        if (empty($response['choices'][0]['message']['content'])) {
            return new \WP_Error('invalid_response', 'Invalid API response format');
        }

        $colors = json_decode($response['choices'][0]['message']['content'], true);
        if (!is_array($colors)) {
            return new \WP_Error('invalid_colors', 'Invalid color data received');
        }

        return $colors;
    }

    /**
     * Validate credentials.
     *
     * @return bool|WP_Error True if valid, error if not.
     */
    public function validate_credentials() {
        if (empty($this->credentials['api_key'])) {
            return new \WP_Error('missing_api_key', 'OpenAI API key is required');
        }
        return true;
    }

    /**
     * Get provider requirements.
     *
     * @return array Configuration requirements.
     */
    public function get_requirements(): array {
        return [
            'api_key' => 'OpenAI API key',
        ];
    }
} 
