<?php
namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Providers\Cohere_Provider;

/**
 * Integration tests for the Cohere provider
 */
class Test_Cohere_Integration extends Test_Provider_Integration {
    /**
     * Returns the test credentials for the Cohere provider
     *
     * @return array
     */
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('COHERE_API_KEY')
        ];
    }

    /**
     * Sets up the test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->maybe_skip_test();
        $this->provider = new Cohere_Provider($this->get_test_credentials());
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
