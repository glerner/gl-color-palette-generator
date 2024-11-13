<?php
namespace GLColorPalette\Tests\Providers;

use GLColorPalette\Providers\Azure_OpenAI_Provider;
use WP_UnitTestCase;

class Azure_OpenAI_Provider_Test extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new Azure_OpenAI_Provider([
            'api_key' => 'test_key',
            'resource_name' => 'test_resource',
            'deployment_id' => 'test_deployment'
        ]);
    }

    public function test_validate_credentials() {
        $provider = new Azure_OpenAI_Provider([]);
        $this->assertWPError($provider->validate_credentials());

        $this->assertTrue($this->provider->validate_credentials());
    }

    public function test_missing_credentials() {
        $provider = new Azure_OpenAI_Provider(['api_key' => 'test_key']);
        $this->assertWPError($provider->validate_credentials());
    }
} 
