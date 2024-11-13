<?php
namespace GLColorPalette\Tests\Integration\Providers;

use GLColorPalette\Providers\CohereProvider;
use WP_UnitTestCase;

class CohereProviderTest extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new CohereProvider();
    }

    public function testGeneratePalette() {
        if (!getenv('COHERE_API_KEY')) {
            $this->markTestSkipped('Cohere API key not configured');
        }

        $colors = $this->provider->generatePalette('forest in autumn');

        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
