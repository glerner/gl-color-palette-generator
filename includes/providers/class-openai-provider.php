<?php declare(strict_types=1);
/**
 * OpenAI Provider Class
 *
 * Implements AI provider interface for OpenAI's GPT models.
 * Handles color palette generation using OpenAI's API.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Interfaces\AI_Provider_Interface;
use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use GL_Color_Palette_Generator\Types\Color_Types;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Provider Class
 *
 * @since 1.0.0
 */
class OpenAI_Provider extends AI_Provider_Base implements AI_Provider_Interface {
    /**
     * Default model to use
     *
     * @var string
     */
    private const DEFAULT_MODEL = 'gpt-4';

    /**
     * API base URL
     *
     * @var string
     */
    protected string $api_url = 'https://api.openai.com/v1/';

    /**
     * API credentials
     *
     * @var array{api_key: string, organization?: string}
     */
    protected array $credentials;

    /**
     * Constructor
     *
     * @param array{
     *     api_key: string,
     *     organization?: string,
     *     base_url?: string,
     *     timeout?: int,
     *     max_retries?: int
     * } $config Provider configuration
     */
    public function __construct(array $config) {
        $config['base_url'] = $config['base_url'] ?? 'https://api.openai.com/v1';
        $this->credentials = $config;
        $this->api_url = $config['base_url'];
        parent::__construct($config);
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name(): string {
        return 'openai';
    }

    /**
     * Get provider display name
     *
     * @return string
     */
    public function get_display_name(): string {
        return 'OpenAI';
    }

    /**
     * Get provider capabilities
     *
     * @return array{
     *     max_colors: int,
     *     supports_streaming: bool,
     *     supports_batch: bool,
     *     supports_style_transfer: bool,
     *     max_prompt_length: int,
     *     rate_limit: array{
     *         requests_per_minute: int,
     *         tokens_per_minute: int
     *     }
     * }
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 10,
            'supports_streaming' => true,
            'supports_batch' => false,
            'supports_style_transfer' => false,
            'max_prompt_length' => 4000,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'tokens_per_minute' => 90000
            ]
        ];
    }

    /**
     * Internal color generation method
     *
     * @param string $prompt     Text prompt describing desired palette
     * @param int    $num_colors Number of colors to generate
     * @param array  $options    Provider-specific options
     * @return array Raw provider response
     * @throws \Exception If generation fails
     */
    protected function generate_colors_internal(string $prompt, int $num_colors, array $options): array {
        if ($num_colors < 2 || $num_colors > 10) {
            throw new \InvalidArgumentException(
                sprintf(__('Number of colors must be between 2 and 10, got %d', 'gl-color-palette-generator'), $num_colors)
            );
        }

        if (empty($prompt)) {
            throw new \InvalidArgumentException(__('Prompt cannot be empty', 'gl-color-palette-generator'));
        }

        if (!empty($options) && !Color_Types::is_valid_provider_options($options)) {
            throw new \InvalidArgumentException(__('Invalid provider options', 'gl-color-palette-generator'));
        }

        $system_prompt = $this->get_system_prompt($num_colors);
        $user_prompt = $this->format_user_prompt($prompt, $num_colors);

        $response = $this->make_request('chat/completions', [
            'model' => $options['model'] ?? self::DEFAULT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt
                ],
                [
                    'role' => 'user',
                    'content' => $user_prompt
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 150,
            'top_p' => $options['top_p'] ?? 1.0,
            'frequency_penalty' => $options['frequency_penalty'] ?? 0.0,
            'presence_penalty' => $options['presence_penalty'] ?? 0.0
        ]);

        if (empty($response['choices'][0]['message']['content'])) {
            throw new PaletteGenerationException(__('Invalid API response format', 'gl-color-palette-generator'));
        }

        $result = json_decode($response['choices'][0]['message']['content'], true);
        if (!is_array($result) || !isset($result['colors'], $result['metadata'])) {
            throw new PaletteGenerationException(__('Invalid color data received', 'gl-color-palette-generator'));
        }

        // Validate returned colors
        foreach ($result['colors'] as $color) {
            if (!Color_Types::is_valid_hex_color($color)) {
                throw new PaletteGenerationException(
                    sprintf(__('Invalid color code received from API: %s', 'gl-color-palette-generator'), $color)
                );
            }
        }

        return $result;
    }

    /**
     * Get system prompt
     *
     * @param int $num_colors Number of colors to generate
     * @return string
     */
    private function get_system_prompt(int $num_colors): string {
        return sprintf(
            'You are a color palette generation assistant. Generate harmonious color palettes based on user prompts. ' .
            'Return a JSON object with two properties: ' .
            '"colors" (array of exactly %d hex color codes) and ' .
            '"metadata" (object with properties: theme, mood, description). ' .
            'Each color must be a valid hex code starting with #.',
            $num_colors
        );
    }

    /**
     * Format user prompt
     *
     * @param string $prompt     Raw user prompt
     * @param int    $num_colors Number of colors to generate
     * @return string
     */
    private function format_user_prompt(string $prompt, int $num_colors): string {
        return sprintf(
            'Generate a color palette with %d colors based on this description: %s',
            $num_colors,
            $prompt
        );
    }

    /**
     * Make an API request
     *
     * @param string $endpoint API endpoint
     * @param array  $data     Request data
     * @return array Response data
     * @throws \Exception If request fails
     */
    private function make_request(string $endpoint, array $data): array {
        $response = wp_remote_post(
            $this->api_url . $endpoint,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->credentials['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode($data),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            throw new \Exception(__('Empty response from API', 'gl-color-palette-generator'));
        }

        if (isset($data['error'])) {
            throw new \Exception($data['error']['message'] ?? __('Unknown API error', 'gl-color-palette-generator'));
        }

        return $data;
    }
}
