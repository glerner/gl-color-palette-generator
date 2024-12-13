<?php declare(strict_types=1);
/**
 * HuggingFace Provider Class
 *
 * Implements AI provider interface for HuggingFace's models.
 * Handles color palette generation using HuggingFace's Inference API.
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
 * HuggingFace Provider Class
 *
 * @since 1.0.0
 */
class HuggingFace_Provider extends AI_Provider_Base implements AI_Provider_Interface {
    /**
     * Constructor
     *
     * @param array $credentials API credentials
     */
    public function __construct(array $credentials) {
        $this->api_url = 'https://api-inference.huggingface.co/models/';
        parent::__construct($credentials);
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name(): string {
        return 'huggingface';
    }

    /**
     * Get provider display name
     *
     * @return string
     */
    public function get_display_name(): string {
        return 'HuggingFace';
    }

    /**
     * Get provider capabilities
     *
     * @return array
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 12,
            'supports_streaming' => false,
            'supports_batch' => true,
            'supports_style_transfer' => true,
            'max_prompt_length' => 2048,
            'rate_limit' => [
                'requests_per_minute' => 120,
                'tokens_per_minute' => 200000
            ]
        ];
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
