<?php declare(strict_types=1);
/**
 * OpenAI Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Provider implementation
 */
class OpenAI_Provider extends AI_Provider_Base {
    /** @var string|null */
    private $api_key;

    /** @var string */
    private $model = 'gpt-4';

    /**
     * Constructor
     *
     * @param Provider_Config|null $config Provider configuration
     */
    public function __construct(?Provider_Config $config = null) {
        $config = $config ?? new Provider_Config([]);
        $this->api_key = $config->get_api_key();
        $this->model = $config->get_model() ?? 'gpt-4';
    }

    /**
     * Validate provider credentials
     *
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    public function validate_credentials(): bool|WP_Error {
        if ($this->api_key === null || $this->api_key === '') {
            return new WP_Error('missing_api_key', 'OpenAI API key is required');
        }

        // Make a simple API call to validate the key
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Test'
                    ]
                ],
                'max_tokens' => 1,
            ]),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error = json_decode($body, true);
            return new WP_Error(
                'invalid_credentials',
                $error['error']['message'] ?? 'Invalid API key or configuration'
            );
        }

        return true;
    }

    /**
     * Get provider requirements
     *
     * @return array Array of requirements
     */
    public function get_requirements(): array {
        return [
            'api_key' => [
                'required' => true,
                'type' => 'string',
                'description' => 'OpenAI API key',
                'link' => 'https://platform.openai.com/api-keys',
            ],
            'model' => [
                'required' => false,
                'type' => 'string',
                'description' => 'OpenAI model to use',
                'default' => 'gpt-4',
                'options' => ['gpt-4', 'gpt-4-32k', 'gpt-3.5-turbo', 'gpt-3.5-turbo-16k'],
            ],
        ];
    }

    /**
     * Generate color palette
     *
     * @param array $params Generation parameters
     * @return array{colors: array<string>, metadata: array{theme: string, mood: string, description: string, provider: string, model?: string, timestamp: int}}|WP_Error Generated colors or error
     */
    public function generate_palette(array $params): array|WP_Error {
        if ($this->api_key === null || $this->api_key === '') {
            return new WP_Error('missing_api_key', 'OpenAI API key is required');
        }

        $prompt = $this->build_prompt($params);
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => $this->get_headers(),
            'body' => wp_json_encode([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a color palette generator. Return only a JSON array of hex color codes.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error(
                'invalid_response',
                __('Invalid response from OpenAI API', 'gl-color-palette-generator')
            );
        }

        try {
            $colors = json_decode($data['choices'][0]['message']['content'], true);
            $validated_colors = $this->validate_colors($colors);

            return [
                'colors' => $validated_colors,
                'metadata' => [
                    'theme' => $params['theme'] ?? '',
                    'mood' => $params['mood'] ?? '',
                    'description' => $params['prompt'] ?? '',
                    'provider' => 'openai',
                    'model' => $this->model,
                    'timestamp' => time()
                ]
            ];
        } catch (\Exception $e) {
            return new WP_Error('generation_failed', $e->getMessage());
        }
    }

    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name(): string {
        return 'openai';
    }

    /**
     * Get provider display name
     *
     * @return string Provider display name
     */
    public function get_display_name(): string {
        return 'OpenAI';
    }

    /**
     * Get provider capabilities
     *
     * @return array{max_colors: int, supports_streaming: bool, supports_batch: bool, supports_style_transfer: bool, max_prompt_length: int, rate_limit: array{requests_per_minute: int, tokens_per_minute: int}}
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 10,
            'supports_streaming' => true,
            'supports_batch' => true,
            'supports_style_transfer' => true,
            'max_prompt_length' => 4000,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'tokens_per_minute' => 90000
            ]
        ];
    }

    /**
     * Build prompt for OpenAI API
     *
     * @param array $params Generation parameters
     * @return string Prompt
     */
    private function build_prompt(array $params): string {
        return sprintf(
            'Generate a color palette with %d colors based on this description: %s',
            $params['num_colors'] ?? 5,
            $params['prompt'] ?? ''
        );
    }

    /**
     * Validate colors from API response
     *
     * @param array<string>|null $colors Colors to validate
     * @return array<string> Validated colors
     * @throws Palette_Generation_Exception If colors are invalid
     */
    protected function validate_colors(?array $colors): array {
        if ($colors === null || $colors === []) {
            throw new Palette_Generation_Exception(
                __('No colors received from API', 'gl-color-palette-generator')
            );
        }

        foreach ($colors as $color) {
            if (!Color_Types::is_valid_hex_color($color)) {
                throw new Palette_Generation_Exception(
                    sprintf(__('Invalid color code received from API: %s', 'gl-color-palette-generator'), $color)
                );
            }
        }

        return $colors;
    }

    /**
     * Get request headers
     *
     * @return array<string, string>
     */
    protected function get_headers(): array {
        return [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Check if provider is ready
     *
     * @return bool
     */
    public function is_ready(): bool {
        return $this->api_key !== null && $this->api_key !== '';
    }

    /**
     * Validate provider options
     *
     * @param array $options Provider options
     * @return bool
     */
    public function validate_options(array $options): bool {
        $valid_options = ['model', 'temperature', 'max_tokens', 'top_p', 'frequency_penalty', 'presence_penalty'];
        foreach ($options as $key => $value) {
            if (!in_array($key, $valid_options, true)) {
                return false;
            }
        }
        return true;
    }
}
