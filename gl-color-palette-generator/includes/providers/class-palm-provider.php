<?php
namespace GLColorPalette\Providers;
class PalmProvider implements AIProviderInterface {
    private $client;
    private $config;

    public function __construct($config) {
        $this->config = $config;
        $this->client = new Google\Cloud\VertexAI\VertexAIClient([
            'credentials' => $config['credentials_path']
        ]);
    }

    public function generate($prompt, $parameters) {
        try {
            $model = $this->client->textModel($this->config['model'] ?? 'text-bison');

            $response = $model->predict(
                $this->format_palm_prompt($prompt),
                $this->format_palm_parameters($parameters)
            );

            return $this->process_response($response);
        } catch (Exception $e) {
            throw new AIGenerationException(
                "PaLM generation failed: " . $e->getMessage(),
                ErrorCodes::API_GENERATION_FAILED
            );
        }
    }

    private function format_palm_prompt($prompt) {
        return $prompt['system'] . "\n\n" . $prompt['user'];
    }

    private function format_palm_parameters($parameters) {
        return [
            'temperature' => $parameters['temperature'],
            'maxOutputTokens' => $parameters['max_tokens'],
            'topP' => $parameters['top_p'],
            'topK' => 40
        ];
    }

    private function process_response($response) {
        if (empty($response->predictions())) {
            throw new Exception("Empty response from PaLM");
        }
        return $response->predictions()[0]->content;
    }
}
