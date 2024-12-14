<?php declare(strict_types=1);
/**
 * AI Provider Factory
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Interfaces\AI_Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * AI Provider Factory
 *
 * Creates and configures AI provider instances based on type and credentials.
 *
 * @since 1.0.0
 */
class AI_Provider_Factory {
    /** @var array */
    private $providers = [];

    /** @var Provider_Config */
    private $config;

    /**
     * Constructor
     *
     * @param Provider_Config|null $config Configuration for providers
     */
    public function __construct(?Provider_Config $config = null) {
        $this->config = $config ?? new Provider_Config();
        $this->register_default_providers();
    }

    /**
     * Register default providers
     */
    private function register_default_providers() {
        $this->providers = [
            'openai' => OpenAI_Provider::class,
            'anthropic' => Anthropic_Provider::class,
            'palm' => Palm_Provider::class,
            'azure-openai' => Azure_OpenAI_Provider::class,
            'cohere' => Cohere_Provider::class,
            'huggingface' => HuggingFace_Provider::class,
            'color-pizza' => Color_Pizza_Provider::class
        ];
    }

    /**
     * Get provider instance
     *
     * @param string $provider_name Name of the provider
     * @return AI_Provider|WP_Error Provider instance or error if not found
     */
    public function get_provider(string $provider_name): AI_Provider|WP_Error {
        if (!isset($this->providers[$provider_name])) {
            return new WP_Error(
                'invalid_provider',
                sprintf('Invalid provider type: %s', $provider_name)
            );
        }

        $provider_class = $this->providers[$provider_name];
        $provider_config = $this->config->get_provider_config($provider_name);

        try {
            return new $provider_class($provider_config);
        } catch (\Exception $e) {
            return new WP_Error(
                'provider_creation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Get all registered providers
     *
     * @return array List of provider names
     */
    public function get_registered_providers(): array {
        return array_keys($this->providers);
    }
}
