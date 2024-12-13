<?php declare(strict_types=1);
/**
 * AI Provider Factory Class
 *
 * Factory class for creating AI provider instances.
 * Handles provider instantiation and configuration management.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Interfaces\AI_Provider_Interface;
use GL_Color_Palette_Generator\Interfaces\Factory_Interface;
use GL_Color_Palette_Generator\Types\Provider_Config;
use GL_Color_Palette_Generator\Types\Factory_Types;
use WP_Error;

/**
 * AI Provider Factory Class
 *
 * Creates and configures AI provider instances based on type and credentials.
 *
 * @since 1.0.0
 */
class AI_Provider_Factory implements Factory_Interface {
    /**
     * Create a provider instance
     *
     * @param string $type        Provider type
     * @param array  $credentials Provider credentials
     * @return AI_Provider_Interface|WP_Error Provider instance or error
     */
    public static function create(string $type, array $credentials): AI_Provider_Interface|WP_Error {
        $provider_class = match(strtolower($type)) {
            'openai'         => OpenAI_Provider::class,
            'azure-openai'   => Azure_OpenAI_Provider::class,
            'anthropic'      => Anthropic_Provider::class,
            'cohere'        => Cohere_Provider::class,
            'huggingface'   => HuggingFace_Provider::class,
            'palm'          => Palm_Provider::class,
            default         => null
        };

        if (!$provider_class) {
            return new WP_Error(
                'invalid_provider',
                sprintf('Invalid provider type: %s', $type)
            );
        }

        try {
            return new $provider_class($credentials);
        } catch (\Exception $e) {
            return new WP_Error(
                'provider_creation_failed',
                sprintf('Failed to create provider: %s', $e->getMessage())
            );
        }
    }

    /**
     * Get available provider types
     *
     * @return array Provider types and their requirements
     */
    public static function get_available_providers(): array {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'requires' => ['api_key'],
                'optional' => ['organization_id']
            ],
            'azure-openai' => [
                'name' => 'Azure OpenAI',
                'requires' => ['api_key', 'resource_name', 'deployment_id']
            ],
            'anthropic' => [
                'name' => 'Anthropic',
                'requires' => ['api_key']
            ],
            'cohere' => [
                'name' => 'Cohere',
                'requires' => ['api_key']
            ],
            'huggingface' => [
                'name' => 'HuggingFace',
                'requires' => ['api_key'],
                'optional' => ['model_id']
            ],
            'palm' => [
                'name' => 'Google PaLM',
                'requires' => ['api_key']
            ]
        ];
    }
}
