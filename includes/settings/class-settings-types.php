<?php
declare(strict_types=1);

/**
 * Settings Types Class
 *
 * Defines types and validation for settings
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Settings
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Types class
 */
class Settings_Types {
    /**
     * Available AI providers
     *
     * @var array<string, string>
     */
    public const AI_PROVIDERS = [
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic',
        'palm' => 'PaLM',
        'cohere' => 'Cohere',
    ];

    /**
     * Default settings
     *
     * @var array{
     *     ai_provider: string,
     *     api_key: string,
     *     cache_duration: int,
     *     max_colors: int,
     *     default_colors: int,
     *     enable_analytics: bool,
     *     rate_limit: int,
     *     debug_mode: bool
     * }
     */
    public const DEFAULT_SETTINGS = [
        'ai_provider' => 'openai',
        'api_key' => '',
        'cache_duration' => 3600,
        'max_colors' => 10,
        'default_colors' => 5,
        'enable_analytics' => true,
        'rate_limit' => 60,
        'debug_mode' => false
    ];

    /**
     * Settings field definitions
     *
     * @return array{
     *     ai_provider: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         options: array<string, string>,
     *         required: bool,
     *         validator: callable
     *     },
     *     api_key: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         required: bool,
     *         validator: callable
     *     },
     *     cache_duration: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         min: int,
     *         max: int,
     *         required: bool,
     *         validator: callable
     *     },
     *     max_colors: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         min: int,
     *         max: int,
     *         required: bool,
     *         validator: callable
     *     },
     *     default_colors: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         min: int,
     *         max: int,
     *         required: bool,
     *         validator: callable
     *     },
     *     enable_analytics: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         required: bool,
     *         validator: callable
     *     },
     *     rate_limit: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         min: int,
     *         max: int,
     *         required: bool,
     *         validator: callable
     *     },
     *     debug_mode: array{
     *         type: string,
     *         title: string,
     *         description: string,
     *         required: bool,
     *         validator: callable
     *     }
     * }
     */
    public static function get_field_definitions(): array {
        return [
            'ai_provider' => [
                'type' => 'select',
                'title' => __('AI Provider', 'gl-color-palette-generator'),
                'description' => __('Select the AI provider to use for palette generation', 'gl-color-palette-generator'),
                'options' => self::AI_PROVIDERS,
                'required' => true,
                'validator' => [self::class, 'validate_ai_provider']
            ],
            'api_key' => [
                'type' => 'password',
                'title' => __('API Key', 'gl-color-palette-generator'),
                'description' => __('Enter your API key for the selected provider', 'gl-color-palette-generator'),
                'required' => true,
                'validator' => [self::class, 'validate_api_key']
            ],
            'cache_duration' => [
                'type' => 'number',
                'title' => __('Cache Duration', 'gl-color-palette-generator'),
                'description' => __('Duration in seconds to cache generated palettes', 'gl-color-palette-generator'),
                'min' => 0,
                'max' => 86400,
                'required' => true,
                'validator' => [self::class, 'validate_cache_duration']
            ],
            'max_colors' => [
                'type' => 'number',
                'title' => __('Maximum Colors', 'gl-color-palette-generator'),
                'description' => __('Maximum number of colors allowed in a palette', 'gl-color-palette-generator'),
                'min' => 2,
                'max' => 20,
                'required' => true,
                'validator' => [self::class, 'validate_max_colors']
            ],
            'default_colors' => [
                'type' => 'number',
                'title' => __('Default Colors', 'gl-color-palette-generator'),
                'description' => __('Default number of colors in a palette', 'gl-color-palette-generator'),
                'min' => 2,
                'max' => 10,
                'required' => true,
                'validator' => [self::class, 'validate_default_colors']
            ],
            'enable_analytics' => [
                'type' => 'checkbox',
                'title' => __('Enable Analytics', 'gl-color-palette-generator'),
                'description' => __('Track palette generation statistics', 'gl-color-palette-generator'),
                'required' => false,
                'validator' => [self::class, 'validate_boolean']
            ],
            'rate_limit' => [
                'type' => 'number',
                'title' => __('Rate Limit', 'gl-color-palette-generator'),
                'description' => __('Maximum API requests per minute', 'gl-color-palette-generator'),
                'min' => 1,
                'max' => 100,
                'required' => true,
                'validator' => [self::class, 'validate_rate_limit']
            ],
            'debug_mode' => [
                'type' => 'checkbox',
                'title' => __('Debug Mode', 'gl-color-palette-generator'),
                'description' => __('Enable detailed error logging', 'gl-color-palette-generator'),
                'required' => false,
                'validator' => [self::class, 'validate_boolean']
            ]
        ];
    }

    /**
     * Validate AI provider
     *
     * @param string $value Provider name
     * @return bool True if valid
     */
    public static function validate_ai_provider(string $value): bool {
        return array_key_exists($value, self::AI_PROVIDERS);
    }

    /**
     * Validate API key
     *
     * @param string $value API key
     * @return bool True if valid
     */
    public static function validate_api_key(string $value): bool {
        return !empty($value) && strlen($value) >= 32;
    }

    /**
     * Validate cache duration
     *
     * @param int $value Duration in seconds
     * @return bool True if valid
     */
    public static function validate_cache_duration(int $value): bool {
        return $value >= 0 && $value <= 86400;
    }

    /**
     * Validate maximum colors
     *
     * @param int $value Maximum colors
     * @return bool True if valid
     */
    public static function validate_max_colors(int $value): bool {
        return $value >= 2 && $value <= 20;
    }

    /**
     * Validate default colors
     *
     * @param int $value Default colors
     * @return bool True if valid
     */
    public static function validate_default_colors(int $value): bool {
        return $value >= 2 && $value <= 10;
    }

    /**
     * Validate boolean value
     *
     * @param bool $value Boolean value
     * @return bool True if valid
     */
    public static function validate_boolean(bool $value): bool {
        return is_bool($value);
    }

    /**
     * Validate rate limit
     *
     * @param int $value Rate limit
     * @return bool True if valid
     */
    public static function validate_rate_limit(int $value): bool {
        return $value >= 1 && $value <= 100;
    }
}
