<?php
namespace GLColorPalette\Providers;

class CohereProvider implements ProviderInterface {
    private $api_key;

    public function __construct() {
        $this->api_key = getenv('COHERE_API_KEY');
    }

    public function generatePalette(string $prompt): array {
        $response = wp_remote_post('https://api.cohere.ai/v1/generate', [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'command',
                'prompt' => "Generate 5 hex color codes that work well together for: {$prompt}. Only respond with the hex codes.",
                'max_tokens' => 50,
                'temperature' => 0.7,
                'k' => 0,
                'stop_sequences' => [],
                'return_likelihoods' => 'NONE'
            ])
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['generations'][0]['text'])) {
            throw new \Exception('Invalid response from Cohere');
        }

        return $this->parseColors($body['generations'][0]['text']);
    }

    private function parseColors(string $content): array {
        preg_match_all('/#[a-fA-F0-9]{6}/', $content, $matches);
        return array_slice($matches[0], 0, 5);
    }
} 
