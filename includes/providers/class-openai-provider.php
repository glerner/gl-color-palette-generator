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
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
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
    /** @var string */
    private $api_key;
    
    /** @var string */
    private $model = 'gpt-4';

    /**
     * Constructor
     *
     * @param Provider_Config|null $config Provider configuration
     */
    public function __construct(?Provider_Config $config = null) {
        parent::__construct($config);
        $config = $config ?? new Provider_Config();
        $this->api_key = $config->get_api_key();
        $this->model = $config->get_model() ?? 'gpt-4';
    }

    /**
     * Generate color palette
     *
     * @param array $params Generation parameters
     * @return array|WP_Error Generated colors or error
     */
    public function generate_palette(array $params): array|WP_Error {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', 'OpenAI API key is required');
        }

        $prompt = $this->build_prompt($params);
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
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
            return new WP_Error('invalid_response', 'Invalid response from OpenAI');
        }

        try {
            $colors = json_decode($data['choices'][0]['message']['content'], true);
            return $this->validate_colors($colors);
        } catch (\Exception $e) {
            return new WP_Error('parse_error', 'Failed to parse OpenAI response');
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
     * @return array Provider capabilities
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 10,
            'supports_streaming' => true,
            'supports_batch' => true,
            'supports_style_transfer' => false,
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
     * Validate generated colors
     *
     * @param array $colors Colors to validate
     * @return array Validated colors
     * @throws \Exception If colors are invalid
     */
    private function validate_colors(array $colors): array {
        if (!is_array($colors)) {
            throw new \Exception('Invalid colors array');
        }

        foreach ($colors as $color) {
            if (!Color_Types::is_valid_hex_color($color)) {
                throw new PaletteGenerationException(
                    sprintf(__('Invalid color code received from API: %s', 'gl-color-palette-generator'), $color)
                );
            }
        }

        return $colors;
    }
}
