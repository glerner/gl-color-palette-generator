<?php
namespace GLColorPalette\Providers;

class PaLMProvider implements ProviderInterface {
    private $api_key;

    public function __construct() {
        $this->api_key = getenv('PALM_API_KEY');
    }

    public function generatePalette(string $prompt): array {
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/text-bison-001:generateText', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'prompt' => [
                    'text' => "Generate 5 hex color codes that work well together for: {$prompt}. Only respond with the hex codes."
                ],
                'temperature' => 0.7,
                'candidate_count' => 1
            ]),
            'query' => [
                'key' => $this->api_key
            ]
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['candidates'][0]['output'])) {
            throw new \Exception('Invalid response from PaLM');
        }

        return $this->parseColors($body['candidates'][0]['output']);
    }

    private function parseColors(string $content): array {
        preg_match_all('/#[a-fA-F0-9]{6}/', $content, $matches);
        return array_slice($matches[0], 0, 5);
    }
} 
