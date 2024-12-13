<?php
/**
 * HuggingFace Provider Class
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
 * Class HuggingFace_Provider
 */
class HuggingFace_Provider extends Abstract_AI_Provider {
    public function __construct(array $credentials) {
        $this->api_url = 'https://api-inference.huggingface.co/models/';
        $this->credentials = $credentials;
    }

    public function generate_palette(array $params) {
        $validation = $this->validate_params($params);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $prompt = $this->format_prompt($params);
        $response = $this->make_request($this->credentials['model_id'], [
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 100,
                'return_full_text' => false,
            ]
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
        $required = ['api_key', 'model_id'];
        foreach ($required as $field) {
            if (empty($this->credentials[$field])) {
                return new WP_Error('missing_credential', "Missing required field: $field");
            }
        }
        return true;
    }

    public function get_requirements(): array {
        return [
            'api_key' => 'HuggingFace API Key',
            'model_id' => 'Model ID (e.g., gpt2)',
        ];
    }
} 
