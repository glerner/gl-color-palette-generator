<?php

namespace GL_Color_Palette_Generator\Tests\Providers;

use GL_Color_Palette_Generator\Providers\AI_Provider_Factory;
use GL_Color_Palette_Generator\Tests\TestCase;

class Test_AI_Provider_Factory extends TestCase {
    protected $factory;

    public function setUp(): void {
        parent::setUp();
        $this->factory = new AI_Provider_Factory();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    public function test_get_provider() {
        $provider = $this->factory->get_provider('openai', ['api_key' => 'test_key']);
        $this->assertInstanceOf('GL_Color_Palette_Generator\Providers\OpenAI_Provider', $provider);
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
