<?php
namespace GLColorPalette\Tests\Integration\Providers;

use GLColorPalette\Providers\PaLMProvider;
use WP_UnitTestCase;

class PaLMProviderTest extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new PaLMProvider();
    }

    public function testGeneratePalette() {
        if (!getenv('PALM_API_KEY')) {
            $this->markTestSkipped('PaLM API key not configured');
        }

        $colors = $this->provider->generatePalette('desert at night');

        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
