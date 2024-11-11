<?php

namespace GLColorPalette\Providers;

class AIProviderFactory {
    private static $providers = [
        'openai' => OpenAIProvider::class,
        'anthropic' => AnthropicProvider::class,
        'palm' => PalmProvider::class,
        'cohere' => CohereProvider::class,
        'stability' => StabilityProvider::class,
        'local' => LocalAIProvider::class,
        'huggingface' => HuggingFaceProvider::class,
        'azure' => AzureOpenAIProvider::class,
        'replicate' => ReplicateProvider::class,
        'custom' => CustomProvider::class
    ];

    public static function create($provider_name, $config = []) {
        if (!isset(self::$providers[$provider_name])) {
            throw new Exception("Unknown AI provider: {$provider_name}");
        }

        $provider_class = self::$providers[$provider_name];
        return new $provider_class($config);
    }

    /**
     * Create AI provider instance
     */
    public function create_provider(string $provider_type): AiProviderInterface {
        switch ($provider_type) {
            case 'openai':
                return new OpenAIProvider(
                    get_option('color_palette_generator_openai_key'),
                    new PromptEngineer()
                );

            case 'anthropic':
                return new AnthropicProvider(
                    get_option('color_palette_generator_anthropic_key'),
                    new PromptEngineer()
                );

            case 'azure':
                return new AzureOpenAIProvider(
                    get_option('color_palette_generator_azure_key'),
                    get_option('color_palette_generator_azure_endpoint'),
                    new PromptEngineer()
                );

            default:
                throw new Exception("Unsupported AI provider: {$provider_type}");
        }
    }

    /**
     * Get available providers
     */
    public function get_available_providers(): array {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'requires_key' => true,
                'supports_streaming' => true,
                'max_tokens' => 4096
            ],
            'anthropic' => [
                'name' => 'Anthropic Claude',
                'requires_key' => true,
                'supports_streaming' => true,
                'max_tokens' => 100000
            ],
            'azure' => [
                'name' => 'Azure OpenAI',
                'requires_key' => true,
                'requires_endpoint' => true,
                'supports_streaming' => true,
                'max_tokens' => 4096
            ]
        ];
    }
}
