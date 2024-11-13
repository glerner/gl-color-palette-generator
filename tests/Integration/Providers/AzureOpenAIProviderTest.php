<?php
namespace GLColorPalette\Tests\Integration\Providers;

use GLColorPalette\Providers\AzureOpenAIProvider;
use WP_UnitTestCase;

class AzureOpenAIProviderTest extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new AzureOpenAIProvider();
    }

    public function testGeneratePalette() {
        if (!getenv('AZURE_OPENAI_API_KEY')) {
            $this->markTestSkipped('Azure OpenAI API key not configured');
        }

        $colors = $this->provider->generatePalette('sunset over ocean');

        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
