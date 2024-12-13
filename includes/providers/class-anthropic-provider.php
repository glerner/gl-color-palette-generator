<?php declare(strict_types=1);
/**
 * Anthropic Provider Class
 *
 * Implements AI provider interface for Anthropic's Claude models.
 * Handles color palette generation using Anthropic's API.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Interfaces\AI_Provider_Interface;
use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Types\Provider_Config;
use GL_Color_Palette_Generator\Types\Color_Types;
use WP_Error;

/**
 * Anthropic Provider Class
 *
 * @since 1.0.0
 */
class Anthropic_Provider extends AI_Provider_Base implements AI_Provider_Interface {
    /**
     * Constructor.
     *
     * @param array $credentials API credentials.
     */
    public function __construct(array $credentials) {
        $this->api_url = 'https://api.anthropic.com/v1/';
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
        $response = $this->make_request('messages', [
            'model' => 'claude-3-opus-20240229',
            'max_tokens' => 150,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a color palette generator. Respond only with a JSON array of hex colors.'
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
            'x-api-key' => $this->credentials['api_key'],
            'anthropic-version' => '2024-01-01',
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
            'Generate a %s color palette with %d colors, starting with %s. Return only a JSON array of hex colors.',
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
        if (empty($response['content'])) {
            return new \WP_Error('invalid_response', 'Invalid API response format');
        }

        // Extract JSON array from response
        preg_match('/\[.*\]/', $response['content'], $matches);
        if (empty($matches[0])) {
            return new \WP_Error('invalid_colors', 'No color array found in response');
        }

        $colors = json_decode($matches[0], true);
        if (!is_array($colors)) {
            return new \WP_Error('invalid_colors', 'Invalid color data received');
        }

        // Validate each color is a proper hex code
        foreach ($colors as $color) {
            if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                return new \WP_Error('invalid_color_format', 'Invalid color format in response');
            }
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
            return new \WP_Error('missing_api_key', 'Anthropic API key is required');
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
            'api_key' => 'Anthropic API key',
        ];
    }
}
