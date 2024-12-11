<?php
namespace GLColorPalette\Tests\Integration;

use GLColorPalette\Providers\Azure_OpenAI_Provider;

class Test_Azure_OpenAI_Integration extends Test_Provider_Integration {
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('AZURE_OPENAI_API_KEY'),
            'resource_name' => getenv('AZURE_OPENAI_RESOURCE'),
            'deployment_id' => getenv('AZURE_OPENAI_DEPLOYMENT')
        ];
    }

    public function setUp(): void {
        parent::setUp();
        $this->maybe_skip_test();
        $this->provider = new Azure_OpenAI_Provider($this->get_test_credentials());
    }

    public function test_generate_palette() {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
        $this->validate_palette_response($colors);
    }
} 
