<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Unit\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\AI_Provider_Factory;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;

/**
 * Tests for the AI Provider Factory
 * 
 * Note: This test extends Test_Provider_Mock rather than Test_Case because it tests
 * the factory that creates providers, not a provider implementation itself.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */
class Test_AI_Provider_Factory extends Test_Provider_Mock {
    protected AI_Provider_Factory $factory;

    public function setUp(): void {
        parent::setUp();
        $this->factory = new AI_Provider_Factory();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    protected function get_test_credentials(): array {
        return [
            'api_key' => 'test_key_123',
            'model' => 'gpt-4'
        ];
    }

    public function test_get_provider() {
        $config = new Provider_Config($this->get_test_credentials());

        $provider = $this->factory->get_provider('openai', $config);
        $this->assertInstanceOf(Provider::class, $provider);
    }

    public function test_get_invalid_provider() {
        $config = new Provider_Config($this->get_test_credentials());

        $this->expectException(\WP_Error::class);
        $this->factory->get_provider('invalid', $config);
    }

    public function test_get_all_providers() {
        $providers = $this->factory->get_registered_providers();
        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);
        
        // Check for some known providers
        $expected_providers = [
            'openai',
            'azure-openai',
            'anthropic',
            'palm',
            'huggingface',
            'cohere'
        ];
        
        foreach ($expected_providers as $provider) {
            $this->assertContains($provider, array_keys($providers));
        }
    }
}
