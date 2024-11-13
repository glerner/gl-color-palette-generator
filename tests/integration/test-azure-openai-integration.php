<?php
namespace GLColorPalette\Tests\Integration;

use GLColorPalette\Providers\Azure_OpenAI_Provider;

class Azure_OpenAI_Integration_Test extends Provider_Integration_Test_Case {
    protected function get_test_credentials() {
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
