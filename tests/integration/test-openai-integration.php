<?php

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Providers\OpenAI_Provider;

/**
 * OpenAI Integration Test Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @since 1.0.0
 */
class Test_OpenAI_Integration extends Test_Provider_Integration {
    /**
     * Set up the test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        $creds = $this->get_test_credentials();
        $this->provider = new OpenAI_Provider($creds);
    }

    /**
     * Get test credentials for OpenAI
     *
     * @return array Test credentials
     */
    protected function get_test_credentials(): array {
        return [
            'api_key' => getenv('OPENAI_API_KEY') ?: '',
            'model' => getenv('OPENAI_MODEL') ?: 'gpt-4'
        ];
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
