<?php

class HuggingFaceProvider implements AIProviderInterface {
    private $client;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->client = new HuggingFace\Client($config['api_key']);
    }

    public function generate($prompt, $parameters) {
        try {
            $response = $this->client->textGeneration([
                'model' => $this->config['model'] ?? 'gpt2',
                'inputs' => $this->format_huggingface_prompt($prompt),
                'parameters' => $this->format_huggingface_parameters($parameters)
            ]);

            return $this->process_response($response);
        } catch (Exception $e) {
            throw new AIGenerationException(
                "HuggingFace generation failed: " . $e->getMessage(),
                ErrorCodes::API_GENERATION_FAILED
            );
        }
    }

    private function format_huggingface_prompt($prompt) {
        return [
            'text' => "System: {$prompt['system']}\n\nUser: {$prompt['user']}"
        ];
    }

    private function format_huggingface_parameters($parameters) {
        return [
            'max_length' => $parameters['max_tokens'],
            'temperature' => $parameters['temperature'],
            'top_p' => $parameters['top_p'],
            'repetition_penalty' => 1.2,
            'do_sample' => true
        ];
    }

    private function process_response($response) {
        if (empty($response[0])) {
            throw new Exception("Empty response from HuggingFace");
        }
        return $response[0]['generated_text'];
    }
} 
