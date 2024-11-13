<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\AI_Provider_Factory;
use WP_UnitTestCase;

class AI_Provider_Factory_Test extends WP_UnitTestCase {
    private $factory;

    public function setUp(): void {
        parent::setUp();
        $this->factory = new AI_Provider_Factory();
    }

    public function test_get_available_providers() {
        $providers = $this->factory->get_available_providers();
        $this->assertIsArray($providers);
        $this->assertContains('azure-openai', $providers);
        $this->assertContains('openai', $providers);
        $this->assertContains('anthropic', $providers);
        $this->assertContains('cohere', $providers);
        $this->assertContains('huggingface', $providers);
        $this->assertContains('palm', $providers);
    }

    public function test_create_provider() {
        $provider = $this->factory->create_provider('azure-openai', [
            'api_key' => 'test_key',
            'resource_name' => 'test_resource',
            'deployment_id' => 'test_deployment'
        ]);
        $this->assertInstanceOf('GLColorPalette\Providers\Azure_OpenAI_Provider', $provider);

        $provider = $this->factory->create_provider('openai', ['api_key' => 'test_key']);
        $this->assertInstanceOf('GLColorPalette\Providers\OpenAI_Provider', $provider);
    }

    public function test_invalid_provider() {
        $this->expectException('\InvalidArgumentException');
        $this->factory->create_provider('invalid-provider', []);
    }

    public function test_get_provider_requirements() {
        $requirements = $this->factory->get_provider_requirements('azure-openai');
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('api_key', $requirements);
        $this->assertArrayHasKey('resource_name', $requirements);
        $this->assertArrayHasKey('deployment_id', $requirements);
    }
} 
