<?php declare(strict_types=1);
/**
 * PaLM Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * PaLM Provider implementation
 *
 * @since 1.0.0
 */
class Palm_Provider extends AI_Provider_Base {
    /** @var string */
    private $api_key;

    /** @var string */
    private $model = 'text-bison-001';

    /**
     * Constructor
     *
     * @param Provider_Config|null $config Provider configuration
     */
    public function __construct(?Provider_Config $config = null) {
        parent::__construct($config);
        $config = $config ?? new Provider_Config();
        $this->api_key = $config->get_api_key();
        $this->model = $config->get_model() ?? 'text-bison-001';
    }

    /**
     * Validate provider credentials
     *
     * @return bool|WP_Error True if valid, WP_Error otherwise
     */
    public function validate_credentials(): bool|WP_Error {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', 'PaLM API key is required');
        }

        // Make a simple API call to validate the key
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateText', [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'prompt' => [
                    'text' => 'Test',
                ],
                'temperature' => 0,
                'candidate_count' => 1,
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
                'description' => 'Google PaLM API key',
                'link' => 'https://makersuite.google.com/app/apikey',
            ],
            'model' => [
                'required' => false,
                'type' => 'string',
                'description' => 'PaLM model to use',
                'default' => 'text-bison-001',
                'options' => ['text-bison-001'],
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
            return new WP_Error('missing_api_key', 'PaLM API key is required');
        }

        $prompt = $this->build_prompt($params);
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta3/models/' . $this->model . ':generateText', [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'prompt' => [
                    'text' => $prompt
                ],
                'temperature' => 0.7,
                'candidate_count' => 1,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['candidates'][0]['output'])) {
            return new WP_Error('invalid_response', 'Invalid response from PaLM');
        }

        try {
            $colors = json_decode($data['candidates'][0]['output'], true);
            return $this->validate_colors($colors);
        } catch (\Exception $e) {
            return new WP_Error('parse_error', 'Failed to parse PaLM response');
        }
    }

    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name(): string {
        return 'palm';
    }

    /**
     * Get provider display name
     *
     * @return string Provider display name
     */
    public function get_display_name(): string {
        return 'PaLM';
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
     * Build prompt for PaLM API
     *
     * @param array $params Generation parameters
     * @return string Prompt
     */
    private function build_prompt(array $params): string {
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
    private function validate_colors(array $colors): array {
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
