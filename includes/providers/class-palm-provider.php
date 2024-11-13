<?php
/**
 * Google PaLM Provider Class
 *
 * @package GLColorPalette
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

use GLColorPalette\Abstracts\AI_Provider_Base;

class Palm_Provider extends AI_Provider_Base {
    public function __construct(array $credentials) {
        $this->api_url = 'https://generativelanguage.googleapis.com/v1beta/models/';
        $this->credentials = $credentials;
    }

    public function generate_palette(array $params) {
        $validation = $this->validate_params($params);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $prompt = $this->format_prompt($params);
        $response = $this->make_request('text-bison-001:generateText', [
            'prompt' => [
                'text' => $prompt
            ],
            'temperature' => 0.7,
            'candidate_count' => 1,
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
            return new \WP_Error('missing_api_key', 'Google PaLM API key is required');
        }
        return true;
    }

    public function get_requirements(): array {
        return [
            'api_key' => 'Google PaLM API Key',
        ];
    }
} 
