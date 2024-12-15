<?php
/**
 * Cohere Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * Cohere Provider implementation
 */
class Cohere_Provider extends AI_Provider_Base {
    /** @var string */
    private $api_key;

    /** @var string */
    private $model = 'command';

    /**
     * Constructor
     *
     * @param Provider_Config|null $config Provider configuration
     */
    public function __construct(?Provider_Config $config = null) {
        parent::__construct($config);
        $config = $config ?? new Provider_Config();
        $this->api_key = $config->get_api_key();
        $this->model = $config->get_model() ?? 'command';
    }

    /**
     * Validate provider credentials
     *
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    public function validate_credentials(): bool|WP_Error {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', 'Cohere API key is required');
        }

        // Make a simple API call to validate the key
        $response = wp_remote_post('https://api.cohere.ai/v1/generate', [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $this->model,
                'prompt' => 'Test',
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
                $error['message'] ?? 'Invalid API key or configuration'
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
                'description' => 'Cohere API key',
                'link' => 'https://dashboard.cohere.ai/api-keys',
            ],
            'model' => [
                'required' => false,
                'type' => 'string',
                'description' => 'Cohere model to use',
                'default' => 'command',
                'options' => ['command', 'command-light', 'command-nightly'],
            ],
        ];
    }

    /**
     * Generate color palette
     *
     * @param array $params Generation parameters
     * @return array|WP_Error Generated colors or error
     */
    public function generate_palette(array $params): array|WP_Error {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', 'Cohere API key is required');
        }

        $prompt = $this->build_prompt($params);
        $response = wp_remote_post('https://api.cohere.ai/v1/generate', [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $this->model,
                'prompt' => $prompt,
                'max_tokens' => 100,
                'temperature' => 0.7,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['generations'][0]['text'])) {
            return new WP_Error('invalid_response', 'Invalid response from Cohere');
        }

        try {
            $colors = json_decode($data['generations'][0]['text'], true);
            return $this->validate_colors($colors);
        } catch (\Exception $e) {
            return new WP_Error('parse_error', 'Failed to parse Cohere response');
        }
    }

    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name(): string {
        return 'cohere';
    }

    /**
     * Get provider display name
     *
     * @return string Provider display name
     */
    public function get_display_name(): string {
        return 'Cohere';
    }

    /**
     * Get provider capabilities
     *
     * @return array Provider capabilities
     */
    public function get_capabilities(): array {
        return [
            'max_colors' => 10,
            'supports_streaming' => false,
            'supports_batch' => true
        ];
    }

    /**
     * Build prompt for Cohere API
     *
     * @param array $params Generation parameters
     * @return string Prompt
     */
    private function build_prompt($params) {
        return sprintf(
            'Generate a color palette with %d colors based on this description: %s. Return only a JSON array of hex color codes.',
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
    private function validate_colors($colors) {
        if (!is_array($colors)) {
            throw new \Exception('Invalid colors array');
        }

        foreach ($colors as $color) {
            if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
                throw new \Exception(sprintf('Invalid color code: %s', $color));
            }
        }

        return $colors;
    }
}
