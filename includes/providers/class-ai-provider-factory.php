<?php
/**
 * AI Provider Factory Class
 *
 * @package GLColorPalette
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GLColorPalette\Providers;

use GLColorPalette\Interfaces\AI_Provider;

/**
 * Class AI_Provider_Factory
 *
 * Creates instances of AI providers based on configuration.
 */
class AI_Provider_Factory {
    /**
     * Available provider types.
     */
    private const PROVIDERS = [
        'openai' => OpenAI_Provider::class,
        'anthropic' => Anthropic_Provider::class,
        'azure' => Azure_OpenAI_Provider::class,
        'cohere' => Cohere_Provider::class,
        'huggingface' => HuggingFace_Provider::class,
        'palm' => Palm_Provider::class,
    ];

    /**
     * Create a provider instance.
     *
     * @param string $type Provider type.
     * @param array  $credentials Provider credentials.
     * @return AI_Provider|WP_Error Provider instance or error.
     */
    public function create(string $type, array $credentials) {
        if (!isset(self::PROVIDERS[$type])) {
            return new \WP_Error(
                'invalid_provider',
                sprintf('Unknown provider type: %s', $type)
            );
        }

        $provider_class = self::PROVIDERS[$type];
        $provider = new $provider_class($credentials);

        $validation = $provider->validate_credentials();
        if (is_wp_error($validation)) {
            return $validation;
        }

        return $provider;
    }

    /**
     * Get available provider types.
     *
     * @return array Provider types and their requirements.
     */
    public function get_available_providers(): array {
        $providers = [];
        foreach (self::PROVIDERS as $type => $class) {
            $provider = new $class([]);
            $providers[$type] = [
                'name' => ucfirst($type),
                'requirements' => $provider->get_requirements(),
            ];
        }
        return $providers;
    }
} 
