<?php
namespace GLColorPalette\Providers;

class AzureOpenAIProvider implements ProviderInterface {
    private $api_key;
    private $resource;
    private $deployment;

    public function __construct() {
        $this->api_key = getenv('AZURE_OPENAI_API_KEY');
        $this->resource = getenv('AZURE_OPENAI_RESOURCE');
        $this->deployment = getenv('AZURE_OPENAI_DEPLOYMENT');
    }

    public function generatePalette(string $prompt): array {
        $url = "https://{$this->resource}.openai.azure.com/openai/deployments/{$this->deployment}/chat/completions?api-version=2024-02-15-preview";

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'api-key' => $this->api_key
            ],
            'body' => json_encode([
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a color palette generator. Respond with exactly 5 hex color codes that work well together.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 150
            ])
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response from Azure OpenAI');
        }

        return $this->parseColors($body['choices'][0]['message']['content']);
    }

    private function parseColors(string $content): array {
        preg_match_all('/#[a-fA-F0-9]{6}/', $content, $matches);
        return array_slice($matches[0], 0, 5);
    }
} 
