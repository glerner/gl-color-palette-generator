<?php
/**
 * Anthropic Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * Anthropic Provider implementation
 */
class Anthropic_Provider extends AI_Provider_Base {
    /** @var string */
    private $api_key;

    /** @var string */
    private $model = 'claude-2';

    /**
     * Constructor
     *
     * @param Provider_Config|null $config Provider configuration
     */
    public function __construct(?Provider_Config $config = null) {
        parent::__construct($config);
        $config = $config ?? new Provider_Config();
        $this->api_key = $config->get_api_key();
        $this->model = $config->get_model() ?? 'claude-2';
    }

    /**
     * Generate color palette
     *
     * @param array $params Generation parameters
     * @return array|WP_Error Generated colors or error
     */
    public function generate_palette($params) {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', 'Anthropic API key is required');
        }

        $prompt = $this->build_prompt($params);
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 150
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['content'][0]['text'])) {
            return new WP_Error('invalid_response', 'Invalid response from Anthropic');
        }

        try {
            $colors = json_decode($data['content'][0]['text'], true);
            return $this->validate_colors($colors);
        } catch (\Exception $e) {
            return new WP_Error('parse_error', 'Failed to parse Anthropic response');
        }
    }

    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_name() {
        return 'anthropic';
    }

    /**
     * Get provider display name
     *
     * @return string Provider display name
     */
    public function get_display_name() {
        return 'Anthropic';
    }

    /**
     * Get provider capabilities
     *
     * @return array Provider capabilities
     */
    public function get_capabilities() {
        return [
            'max_colors' => 10,
            'supports_streaming' => true,
            'supports_batch' => true
        ];
    }

    /**
     * Build prompt for Anthropic API
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
