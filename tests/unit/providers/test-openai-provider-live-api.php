<?php
/**
 * Integration tests for the OpenAI provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Integration;
use GL_Color_Palette_Generator\Providers\OpenAI_Provider;

/**
 * Test OpenAI integration
 */
class Test_OpenAI_Integration extends Test_Provider_Integration {
    /**
     * Returns the test credentials for the OpenAI provider
     *
     * @return array
     */
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('OPENAI_API_KEY')
        ];
    }

    /**
     * Test that we can create a valid provider instance
     */
    public function test_create_provider() {
        $provider = new OpenAI_Provider($this->get_test_credentials());
        $this->assertInstanceOf(OpenAI_Provider::class, $provider);
    }

    /**
     * Test that we can generate a color palette
     */
    public function test_generate_palette() {
        $provider = new OpenAI_Provider($this->get_test_credentials());
        $result = $provider->generate_palette('A sunset over the ocean');
        $this->assertNotWPError($result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
