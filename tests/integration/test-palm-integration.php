<?php

namespace GLColorPalette\Tests\Integration;

use GLColorPalette\Providers\Palm_Provider;
use PHPUnit\Framework\TestCase;

class Test_Palm_Integration extends Test_Provider_Integration {
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('PALM_API_KEY')
        ];
    }

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->maybe_skip_test();
        $this->provider = new Palm_Provider($this->get_test_credentials());
    }

    public function test_generate_palette() {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
        $this->validate_palette_response($colors);
    }
} 
