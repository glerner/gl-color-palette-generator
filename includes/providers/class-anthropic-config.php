<?php
/**
 * Anthropic Provider Configuration
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

/**
 * Anthropic specific configuration
 */
class Anthropic_Config extends Provider_Config {
    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name(): string {
        return 'anthropic';
    }

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models(): array {
        return [
            'claude-3-sonnet' => __('Claude 3 Sonnet (Recommended)', 'gl-color-palette-generator'),
            'claude-3-opus' => __('Claude 3 Opus (Most Capable)', 'gl-color-palette-generator'),
            'claude-3-haiku' => __('Claude 3 Haiku (Fastest)', 'gl-color-palette-generator')
        ];
    }

    /**
     * Get default model
     *
     * @return string
     */
    public function get_default_model(): string {
        return 'claude-3-sonnet';
    }

    /**
     * Validate API key
     *
     * @param string $api_key API key to validate
     * @return bool Whether the API key is valid
     */
    public function validate_api_key(string $api_key): bool {
        if (empty($api_key)) {
            return false;
        }

        // Basic format check for Anthropic API keys
        if (!preg_match('/^sk-ant-[a-zA-Z0-9]{32,}$/', $api_key)) {
            return false;
        }

        return true;
    }

    /**
     * Get API endpoint
     *
     * @return string
     */
    public function get_api_endpoint(): string {
        return 'https://api.anthropic.com/v1/messages';
    }
} 
