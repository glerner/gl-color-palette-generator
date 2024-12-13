<?php

namespace GLColorPalette\Tests\Integration;

use GLColorPalette\Providers\HuggingFace_Provider;

class Test_HuggingFace_Integration extends Test_Provider_Integration {
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('HUGGINGFACE_API_KEY'),
            'model_id' => getenv('HUGGINGFACE_MODEL_ID')
        ];
    }

    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->maybe_skip_test();
        $this->provider = new HuggingFace_Provider($this->get_test_credentials());
    }

    public function test_generate_palette() {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
        $this->validate_palette_response($colors);
    }
} 
