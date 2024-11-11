<?php

class AzureOpenAIProvider implements AIProviderInterface {
    private $client;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->client = new OpenAI([
            'api_key' => $config['api_key'],
            'azure' => [
                'endpoint' => $config['endpoint'],
                'deployment' => $config['deployment'],
                'api_version' => $config['api_version'] ?? '2024-02-15-preview'
            ]
        ]);
    }

    public function generate($prompt, $parameters) {
        try {
            $response = $this->client->chat->create([
                'model' => $this->config['deployment'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt['system']
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt['user']
                    ]
                ],
                'temperature' => $parameters['temperature'],
                'max_tokens' => $parameters['max_tokens'],
                'top_p' => $parameters['top_p'],
                'frequency_penalty' => $parameters['frequency_penalty'],
                'presence_penalty' => $parameters['presence_penalty'],
                'stop' => $parameters['stop']
            ]);

            return $this->process_response($response);
        } catch (Exception $e) {
            throw new AIGenerationException(
                "Azure OpenAI generation failed: " . $e->getMessage(),
                ErrorCodes::API_GENERATION_FAILED
            );
        }
    }

    private function process_response($response) {
        if (empty($response->choices)) {
            throw new Exception("Empty response from Azure OpenAI");
        }
        return $response->choices[0]->message->content;
    }
} 
