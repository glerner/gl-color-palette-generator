<?php

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Providers\OpenAI_Provider;

class Test_OpenAI_Integration extends Test_Provider_Integration {
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('OPENAI_API_KEY')
        ];
    }

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->maybe_skip_test();
        $this->provider = new OpenAI_Provider($this->get_test_credentials());
    }

    public function test_generate_palette() {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
    }
} 
