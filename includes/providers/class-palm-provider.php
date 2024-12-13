<?php declare(strict_types=1);
/**
 * Google PaLM Provider Class
 *
 * Implements AI provider interface for Google's PaLM models.
 * Handles color palette generation using PaLM API.
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
 * PaLM Provider Class
 *
 * @since 1.0.0
 */
class Palm_Provider extends AI_Provider_Base implements AI_Provider_Interface {
    /**
     * Constructor
     *
     * @param array $credentials API credentials
     */
    public function __construct(array $credentials) {
        $this->api_url = 'https://generativelanguage.googleapis.com/v1beta/models/';
        parent::__construct($credentials);
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name(): string {
        return 'palm';
    }

    /**
     * Get provider display name
     *
     * @return string
     */
    public function get_display_name(): string {
        return 'Google PaLM';
    }

    /**
     * Get provider capabilities
     *
     * @return array
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 8,
            'supports_streaming' => false,
            'supports_batch' => true,
            'supports_style_transfer' => true,
            'max_prompt_length' => 4096,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'tokens_per_minute' => 100000
            ]
        ];
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
            return new WP_Error('missing_api_key', 'Google PaLM API key is required');
        }
        return true;
    }

    public function get_requirements(): array {
        return [
            'api_key' => 'Google PaLM API Key',
        ];
    }
} 
