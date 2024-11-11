<?php

class CohereProvider implements AIProviderInterface {
    private $client;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->client = Cohere::client($config['api_key']);
    }

    public function generate($prompt, $parameters) {
        try {
            $response = $this->client->generate([
                'model' => $this->config['model'] ?? 'command',
                'prompt' => $this->format_cohere_prompt($prompt),
                'max_tokens' => $parameters['max_tokens'],
                'temperature' => $parameters['temperature'],
                'k' => 0,
                'p' => $parameters['top_p'],
                'frequency_penalty' => $parameters['frequency_penalty'],
                'presence_penalty' => $parameters['presence_penalty'],
                'stop_sequences' => $parameters['stop']
            ]);

            return $this->process_response($response);
        } catch (Exception $e) {
            throw new AIGenerationException(
                "Cohere generation failed: " . $e->getMessage(),
                ErrorCodes::API_GENERATION_FAILED
            );
        }
    }

    private function format_cohere_prompt($prompt) {
        return "System: {$prompt['system']}\n\nUser: {$prompt['user']}";
    }

    private function process_response($response) {
        if (empty($response->generations)) {
            throw new Exception("Empty response from Cohere");
        }
        return $response->generations[0]->text;
    }
} 
