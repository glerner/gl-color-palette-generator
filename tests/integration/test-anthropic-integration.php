<?php

namespace GLColorPalette\Tests\Integration;

use GLColorPalette\Providers\Anthropic_Provider;

class Test_Anthropic_Integration extends Test_Provider_Integration {
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('ANTHROPIC_API_KEY')
        ];
    }

    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->maybe_skip_test();
        $this->provider = new Anthropic_Provider($this->get_test_credentials());
    }

    public function test_generate_palette() {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
    }
}
