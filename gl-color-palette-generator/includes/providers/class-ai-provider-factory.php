<?php

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
} 
