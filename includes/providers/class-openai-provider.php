<?php
/**
 * OpenAI Provider Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Provider class
 */
class OpenAI_Provider implements AI_Provider {
    /**
     * API base URL
     *
     * @var string
     */
    private string $api_url = 'https://api.openai.com/v1/';

    /**
     * API credentials
     *
     * @var array
     */
    private array $credentials;

    /**
     * Constructor
     *
     * @param array $credentials API credentials
     */
    public function __construct(array $credentials) {
        $this->credentials = $credentials;
    }

    /**
     * Generate a color palette based on a prompt
     *
     * @param string $prompt     Text prompt describing desired palette
     * @param int    $num_colors Number of colors to generate (2-10)
     * @param array  $options    Additional provider-specific options
     * @return array{colors: array<string>, metadata: array} Generated palette data
     * @throws \Exception If generation fails
     */
    public function generate_palette(string $prompt, int $num_colors = 5, array $options = []): array {
        if ($num_colors < 2 || $num_colors > 10) {
            throw new \InvalidArgumentException(
                sprintf(__('Number of colors must be between 2 and 10, got %d', 'gl-color-palette-generator'), $num_colors)
            );
        }

        if (empty($prompt)) {
            throw new \InvalidArgumentException(
                __('Prompt cannot be empty', 'gl-color-palette-generator')
            );
        }

        $system_prompt = 'You are a color palette generator. Generate aesthetically pleasing and harmonious color combinations. ' .
                        'Respond with a JSON object containing two properties: ' .
                        '"colors" (array of hex color codes) and ' .
                        '"metadata" (object with properties: theme, mood, description).';

        $user_prompt = sprintf(
            'Generate a color palette with %d colors based on this description: %s',
            $num_colors,
            $prompt
        );

        $response = $this->make_request('chat/completions', [
            'model' => $options['model'] ?? 'gpt-4',
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
        ]);

        if (empty($response['choices'][0]['message']['content'])) {
            throw new \Exception(__('Invalid API response format', 'gl-color-palette-generator'));
        }

        $result = json_decode($response['choices'][0]['message']['content'], true);
        if (!is_array($result) || !isset($result['colors'], $result['metadata'])) {
            throw new \Exception(__('Invalid color data received', 'gl-color-palette-generator'));
        }

        return $result;
    }

    /**
     * Get provider name
     *
     * @return string Provider identifier
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
     * Check if provider is configured and ready
     *
     * @return bool True if ready, false otherwise
     */
    public function is_ready(): bool {
        return !empty($this->credentials['api_key']);
    }

    /**
     * Get provider configuration requirements
     *
     * @return array Configuration field definitions
     */
    public function get_config_fields(): array {
        return [
            'api_key' => [
                'type' => 'password',
                'label' => __('API Key', 'gl-color-palette-generator'),
                'description' => __('Your OpenAI API key', 'gl-color-palette-generator'),
                'required' => true,
            ],
            'model' => [
                'type' => 'select',
                'label' => __('Model', 'gl-color-palette-generator'),
                'description' => __('OpenAI model to use', 'gl-color-palette-generator'),
                'required' => false,
                'default' => 'gpt-4',
                'options' => [
                    'gpt-4' => 'GPT-4',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                ],
            ],
            'temperature' => [
                'type' => 'number',
                'label' => __('Temperature', 'gl-color-palette-generator'),
                'description' => __('Controls randomness (0.0 to 1.0)', 'gl-color-palette-generator'),
                'required' => false,
                'default' => 0.7,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
            ],
        ];
    }

    /**
     * Validate provider configuration
     *
     * @param array $config Configuration to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_config(array $config): bool {
        if (empty($config['api_key'])) {
            return false;
        }

        if (isset($config['model']) && !in_array($config['model'], ['gpt-4', 'gpt-3.5-turbo'])) {
            return false;
        }

        if (isset($config['temperature'])) {
            $temp = (float) $config['temperature'];
            if ($temp < 0 || $temp > 1) {
                return false;
            }
        }

        return true;
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
