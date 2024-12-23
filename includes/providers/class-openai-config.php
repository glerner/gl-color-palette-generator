<?php
/**
 * OpenAI Provider Configuration
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

/**
 * OpenAI specific configuration
 */
class OpenAI_Config extends Provider_Config {
    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name(): string {
        return 'openai';
    }

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models(): array {
        return [
            'gpt-4' => __('GPT-4 (Recommended)', 'gl-color-palette-generator'),
            'gpt-3.5-turbo' => __('GPT-3.5 Turbo (Faster)', 'gl-color-palette-generator')
        ];
    }

    /**
     * Get default model
     *
     * @return string
     */
    public function get_default_model(): string {
        return 'gpt-4';
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

        // Basic format check
        if (!preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $api_key)) {
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
        return 'https://api.openai.com/v1/chat/completions';
    }
} 
