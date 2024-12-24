<?php
/**
 * Integration tests for the Cohere provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Test_Provider_Integration;
use GL_Color_Palette_Generator\Providers\Cohere_Provider;

/**
 * Test Cohere integration
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
     * Test that we can create a valid provider instance
     */
    public function test_create_provider() {
        $provider = new Cohere_Provider($this->get_test_credentials());
        $this->assertInstanceOf(Cohere_Provider::class, $provider);
    }

    /**
     * Test that we can generate a color palette
     */
    public function test_generate_palette() {
        $provider = new Cohere_Provider($this->get_test_credentials());
        $result = $provider->generate_palette('A sunset over the ocean');
        $this->assertNotWPError($result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
