<?php
/**
 * Azure OpenAI Provider Class
 *
 * @package GLColorPalette
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

use GLColorPalette\Abstracts\AI_Provider_Base;

class Azure_OpenAI_Provider extends AI_Provider_Base {
    public function __construct(array $credentials) {
        $this->credentials = $credentials;
        $this->api_url = sprintf(
            'https://%s.openai.azure.com/openai/deployments/%s/',
            $credentials['resource_name'],
            $credentials['deployment_id']
        );
    }

    public function generate_palette(array $params) {
        $validation = $this->validate_params($params);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $prompt = $this->format_prompt($params);
        $response = $this->make_request('chat/completions', [
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

    protected function get_headers(): array {
        return [
            'api-key' => $this->credentials['api_key'],
            'Content-Type' => 'application/json',
        ];
    }

    public function validate_credentials() {
        $required = ['api_key', 'resource_name', 'deployment_id'];
        foreach ($required as $field) {
            if (empty($this->credentials[$field])) {
                return new \WP_Error('missing_credential', "Missing required field: $field");
            }
        }
        return true;
    }

    public function get_requirements(): array {
        return [
            'api_key' => 'Azure OpenAI API Key',
            'resource_name' => 'Azure Resource Name',
            'deployment_id' => 'Model Deployment ID',
        ];
    }
} 
