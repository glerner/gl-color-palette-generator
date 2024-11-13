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

    public function test_generate_palette_integration() {
        if (!getenv('AZURE_OPENAI_API_KEY')) {
            $this->markTestSkipped('Azure OpenAI API key not configured');
        }

        $live_provider = new Azure_OpenAI_Provider([
            'api_key' => getenv('AZURE_OPENAI_API_KEY'),
            'resource_name' => getenv('AZURE_RESOURCE_NAME'),
            'deployment_id' => getenv('AZURE_DEPLOYMENT_ID')
        ]);

        $colors = $live_provider->generate_palette([
            'theme' => 'sunset over ocean',
            'count' => 5
        ]);

        $this->assertIsArray($colors);
        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
