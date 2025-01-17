<?php
/**
 * Integration tests for the Anthropic provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Integration;
use GL_Color_Palette_Generator\Providers\Anthropic_Provider;

/**
 * Test Anthropic integration
 */
class Test_Anthropic_Integration extends Test_Provider_Integration {
    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
    }

    /**
     * Returns the test credentials for the Anthropic provider
     *
     * @return array
     */
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('ANTHROPIC_API_KEY')
        ];
    }

    /**
     * Test that we can create a valid provider instance
     */
    public function test_create_provider() {
        $provider = new Anthropic_Provider($this->get_test_credentials());
        $this->assertInstanceOf(Anthropic_Provider::class, $provider);
    }

    /**
     * Test that we can generate a color palette
     */
    public function test_generate_palette() {
        $provider = new Anthropic_Provider($this->get_test_credentials());
        $result = $provider->generate_palette('A sunset over the ocean');
        $this->assertNotInstanceOf(\WP_Error::class, $result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
