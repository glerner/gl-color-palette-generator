<?php

namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\AI_Provider_Factory;
use WP_Mock;

class AI_Provider_Factory_Test extends \WP_Mock\Tools\TestCase {
    protected $factory;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->factory = new AI_Provider_Factory();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_get_provider() {
        $provider = $this->factory->get_provider('openai', ['api_key' => 'test_key']);
        $this->assertInstanceOf('GLColorPalette\Providers\OpenAI_Provider', $provider);
    }

    public function test_get_invalid_provider() {
        $provider = $this->factory->get_provider('invalid', ['api_key' => 'test_key']);
        $this->assertInstanceOf(\WP_Error::class, $provider);
    }

    public function test_get_all_providers() {
        $providers = $this->factory->get_all_providers();
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
