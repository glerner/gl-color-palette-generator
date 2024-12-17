<?php

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Providers\Palm_Provider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the PaLM provider
 */
class Test_Palm_Integration extends Test_Provider_Integration {
    /**
     * Returns the test credentials for the PaLM provider
     *
     * @return array
     */
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('PALM_API_KEY')
        ];
    }

    /**
     * Sets up the test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->maybe_skip_test();
        $this->provider = new Palm_Provider($this->get_test_credentials());
    }

    /**
     * Test that the provider can generate a palette
     */
    public function test_generate_palette(): void {
        $colors = $this->provider->generate_palette($this->test_params);
        $this->assertNotWPError($colors);
        $this->assertIsArray($colors);
        $this->assertCount($this->test_params['count'], $colors);
    }
} 
