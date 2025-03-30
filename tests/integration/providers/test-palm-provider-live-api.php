<?php
/**
 * Integration tests for the PaLM provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration\Providers
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration\Providers;
use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;

use GL_Color_Palette_Generator\Tests\Test_Provider_Integration;
use GL_Color_Palette_Generator\Providers\Palm_Provider;

/**
 * Test PaLM integration
 */
class Test_Palm_Integration extends Test_Provider_Integration {
    /**
     * Returns the test credentials for the Palm provider
     *
     * @return array
     */
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('PALM_API_KEY')
        ];
    }

    /**
     * Test that we can create a valid provider instance
     */
    public function test_create_provider() {
        $provider = new Palm_Provider($this->get_test_credentials());
        $this->assertInstanceOf(Palm_Provider::class, $provider);
    }

    /**
     * Test that we can generate a color palette
     */
    public function test_generate_palette() {
        $provider = new Palm_Provider($this->get_test_credentials());
        $result = $provider->generate_palette('A sunset over the ocean');
        $this->assertNotWPError($result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
