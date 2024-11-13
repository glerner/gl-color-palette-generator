<?php
/**
 * Anthropic Provider Class
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GLColorPalette\Providers;

class AnthropicProvider implements AIProviderInterface {
    private $client;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->client = new Anthropic([
            'api_key' => $config['api_key']
        ]);
    }

    public function generate($prompt, $parameters) {
        try {
            $response = $this->client->messages->create([
                'model' => $this->config['model'] ?? 'claude-3-opus',
                'max_tokens' => $parameters['max_tokens'],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt['user']],
                ],
                'system' => $prompt['system'],
                'temperature' => $parameters['temperature']
            ]);

            return $this->process_response($response);
        } catch (Exception $e) {
            throw new AIGenerationException(
                "Anthropic generation failed: " . $e->getMessage(),
                ErrorCodes::API_GENERATION_FAILED
            );
        }
    }

    private function process_response($response) {
        if (empty($response->content)) {
            throw new Exception("Empty response from Anthropic");
        }
        return $response->content[0]->text;
    }
}
