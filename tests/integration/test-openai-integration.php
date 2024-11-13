<?php
namespace GLColorPalette\Tests\Integration;

use GLColorPalette\Providers\OpenAI_Provider;

class OpenAI_Integration_Test extends Provider_Integration_Test_Case {
    protected function get_test_credentials() {
        return [
            'api_key' => getenv('OPENAI_API_KEY')
        ];
    }

    public function setUp(): void {
        parent::setUp();
        $this->maybe_skip_test();
        $this->provider = new OpenAI_Provider($this->get_test_credentials());
    }

    public function test_generate_palette() {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
        $this->validate_palette_response($colors);
    }
} 
