<?php
namespace GLColorPalette\Providers;

class HuggingFaceProvider implements ProviderInterface {
    private $api_key;
    private $model_id;

    public function __construct() {
        $this->api_key = getenv('HUGGINGFACE_API_KEY');
        $this->model_id = getenv('HUGGINGFACE_MODEL_ID');
    }

    public function generatePalette(string $prompt): array {
        $response = wp_remote_post("https://api-inference.huggingface.co/models/{$this->model_id}", [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'inputs' => "Generate 5 hex color codes that work well together for: {$prompt}. Only respond with the hex codes.",
                'parameters' => [
                    'max_new_tokens' => 50,
                    'temperature' => 0.7,
                    'top_p' => 0.95,
                    'do_sample' => true
                ]
            ])
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body[0]['generated_text'])) {
            throw new \Exception('Invalid response from HuggingFace');
        }

        return $this->parseColors($body[0]['generated_text']);
    }

    private function parseColors(string $content): array {
        preg_match_all('/#[a-fA-F0-9]{6}/', $content, $matches);
        return array_slice($matches[0], 0, 5);
    }
} 
