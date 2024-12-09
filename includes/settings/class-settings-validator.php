<?php
declare(strict_types=1);

/**
 * Settings Validator Class
 *
 * Handles complex validation scenarios and cross-field validation for settings
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Settings
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Settings;

use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Validator class
 */
class Settings_Validator {
    /**
     * Validate settings array
     *
     * @param array $settings Settings array to validate
     * @return bool True if valid
     * @throws Palette_Generation_Exception If validation fails
     */
    public static function validate_settings(array $settings): bool {
        self::validate_default_colors($settings);
        self::validate_provider_settings($settings);
        self::validate_cache_settings($settings);
        self::validate_rate_limit_settings($settings);
        return true;
    }

    /**
     * Validate default colors settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_default_colors(array $settings): void {
        if (isset($settings['default_colors'], $settings['max_colors'])) {
            if ($settings['default_colors'] > $settings['max_colors']) {
                throw new Palette_Generation_Exception(
                    __('Default colors cannot be greater than maximum colors', 'gl-color-palette-generator'),
                    'SETTINGS_VALIDATION_ERROR'
                );
            }
        }
    }

    /**
     * Validate provider settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_provider_settings(array $settings): void {
        if (isset($settings['ai_provider'])) {
            $provider = $settings['ai_provider'];
            
            // Check if API key is set when required
            if (!isset($settings['api_key']) || empty($settings['api_key'])) {
                throw new Palette_Generation_Exception(
                    sprintf(__('API key is required for %s provider', 'gl-color-palette-generator'), $provider),
                    'SETTINGS_VALIDATION_ERROR'
                );
            }

            // Provider-specific validation
            switch ($provider) {
                case 'openai':
                    self::validate_openai_settings($settings);
                    break;
                case 'anthropic':
                    self::validate_anthropic_settings($settings);
                    break;
                case 'palm':
                    self::validate_palm_settings($settings);
                    break;
                case 'cohere':
                    self::validate_cohere_settings($settings);
                    break;
            }
        }
    }

    /**
     * Validate OpenAI specific settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_openai_settings(array $settings): void {
        if (!preg_match('/^sk-[a-zA-Z0-9]{48}$/', $settings['api_key'])) {
            throw new Palette_Generation_Exception(
                __('Invalid OpenAI API key format', 'gl-color-palette-generator'),
                'SETTINGS_VALIDATION_ERROR'
            );
        }
    }

    /**
     * Validate Anthropic specific settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_anthropic_settings(array $settings): void {
        if (!preg_match('/^sk-ant-[a-zA-Z0-9]{32,}$/', $settings['api_key'])) {
            throw new Palette_Generation_Exception(
                __('Invalid Anthropic API key format', 'gl-color-palette-generator'),
                'SETTINGS_VALIDATION_ERROR'
            );
        }
    }

    /**
     * Validate PaLM specific settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_palm_settings(array $settings): void {
        if (strlen($settings['api_key']) !== 39) {
            throw new Palette_Generation_Exception(
                __('Invalid PaLM API key format', 'gl-color-palette-generator'),
                'SETTINGS_VALIDATION_ERROR'
            );
        }
    }

    /**
     * Validate Cohere specific settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_cohere_settings(array $settings): void {
        if (!preg_match('/^[a-zA-Z0-9]{40}$/', $settings['api_key'])) {
            throw new Palette_Generation_Exception(
                __('Invalid Cohere API key format', 'gl-color-palette-generator'),
                'SETTINGS_VALIDATION_ERROR'
            );
        }
    }

    /**
     * Validate cache settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_cache_settings(array $settings): void {
        if (isset($settings['cache_duration'])) {
            // If analytics is enabled, cache duration should be at least 1 hour
            if (isset($settings['enable_analytics']) && 
                $settings['enable_analytics'] && 
                $settings['cache_duration'] < 3600) {
                throw new Palette_Generation_Exception(
                    __('Cache duration must be at least 1 hour when analytics is enabled', 'gl-color-palette-generator'),
                    'SETTINGS_VALIDATION_ERROR'
                );
            }
        }
    }

    /**
     * Validate rate limit settings
     *
     * @param array $settings Settings array
     * @return void
     * @throws Palette_Generation_Exception If validation fails
     */
    private static function validate_rate_limit_settings(array $settings): void {
        if (isset($settings['rate_limit'], $settings['ai_provider'])) {
            // Provider-specific rate limit validation
            $max_rate_limits = [
                'openai' => 60,
                'anthropic' => 50,
                'palm' => 40,
                'cohere' => 30
            ];

            $provider = $settings['ai_provider'];
            if (isset($max_rate_limits[$provider]) && $settings['rate_limit'] > $max_rate_limits[$provider]) {
                throw new Palette_Generation_Exception(
                    sprintf(
                        __('Maximum rate limit for %s is %d requests per minute', 'gl-color-palette-generator'),
                        $provider,
                        $max_rate_limits[$provider]
                    ),
                    'SETTINGS_VALIDATION_ERROR'
                );
            }
        }
    }
}
