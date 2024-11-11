<?php

class OpenAIProvider implements AIProviderInterface {
    private $api_key;
    private $settings;
    private $client;

    public function __construct($api_key, $settings = []) {
        $this->api_key = $api_key;
        $this->settings = $settings;
        $this->initialize_client();
    }

    public function generate($prompt, $parameters) {
        try {
            $response = $this->client->chat->create([
                'model' => $this->settings['model'] ?? 'gpt-4',
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
                "OpenAI generation failed: " . $e->getMessage(),
                ErrorCodes::API_GENERATION_FAILED
            );
        }
    }

    private function initialize_client() {
        $this->client = OpenAI::client($this->api_key);
    }

    private function process_response($response) {
        if (empty($response->choices)) {
            throw new Exception("Empty response from OpenAI");
        }

        return $response->choices[0]->message->content;
    }
} 
