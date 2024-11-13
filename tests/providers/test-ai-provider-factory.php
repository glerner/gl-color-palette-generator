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

    public function test_create_invalid_provider() {
        $result = $this->factory->create('invalid', []);
        $this->assertWPError($result);
    }

    public function test_create_valid_provider() {
        $result = $this->factory->create('openai', ['api_key' => 'test_key']);
        $this->assertInstanceOf('GLColorPalette\Interfaces\AI_Provider', $result);
    }

    public function test_get_available_providers() {
        $providers = $this->factory->get_available_providers();
        $this->assertIsArray($providers);
        $this->assertArrayHasKey('openai', $providers);
        $this->assertArrayHasKey('anthropic', $providers);
    }
} 
