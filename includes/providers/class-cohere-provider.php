<?php
/**
 * Cohere Provider Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Providers\Abstract_AI_Provider;
use GL_Color_Palette_Generator\Providers\Provider_Config;
use WP_Error;

/**
 * Class Cohere_Provider
 */
class Cohere_Provider extends Abstract_AI_Provider {
    public function __construct(array $credentials) {
        $this->api_url = 'https://api.cohere.ai/v1/';
        $this->credentials = $credentials;
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
