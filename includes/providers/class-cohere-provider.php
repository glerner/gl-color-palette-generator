<?php declare(strict_types=1);
/**
 * Cohere Provider Class
 *
 * Implements AI provider interface for Cohere's language models.
 * Handles color palette generation using Cohere's API.
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
 * Cohere Provider Class
 *
 * @since 1.0.0
 */
class Cohere_Provider extends AI_Provider_Base implements AI_Provider_Interface {
    /**
     * Constructor
     *
     * @param array $credentials API credentials
     */
    public function __construct(array $credentials) {
        $this->api_url = 'https://api.cohere.ai/v1/';
        parent::__construct($credentials);
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name(): string {
        return 'cohere';
    }

    /**
     * Get provider display name
     *
     * @return string
     */
    public function get_display_name(): string {
        return 'Cohere';
    }

    /**
     * Get provider capabilities
     *
     * @return array
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 10,
            'supports_streaming' => false,
            'supports_batch' => true,
            'supports_style_transfer' => true,
            'max_prompt_length' => 2048,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'tokens_per_minute' => 150000
            ]
        ];
    }

    public function generate_palette(array $params) {
        $validation = $this->validate_params($params);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $prompt = $this->format_prompt($params);
        $response = $this->make_request('generate', [
            'model' => 'command',
            'prompt' => $prompt,
            'max_tokens' => 100,
            'temperature' => 0.7,
            'format' => 'json',
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->parse_response($response);
    }

    protected function get_headers(): array {
        return [
            'Authorization' => 'Bearer ' . $this->credentials['api_key'],
            'Content-Type' => 'application/json',
        ];
    }

    public function validate_credentials() {
        if (empty($this->credentials['api_key'])) {
            return new \WP_Error('missing_api_key', 'Cohere API key is required');
        }
        return true;
    }

    public function get_requirements(): array {
        return [
            'api_key' => 'Cohere API Key',
        ];
    }
} 
