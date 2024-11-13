<?php
namespace GLColorPalette\Tests\Integration\Providers;

use GLColorPalette\Providers\HuggingFaceProvider;
use WP_UnitTestCase;

class HuggingFaceProviderTest extends WP_UnitTestCase {
    private $provider;

    public function setUp(): void {
        parent::setUp();
        $this->provider = new HuggingFaceProvider();
    }

    public function testGeneratePalette() {
        if (!getenv('HUGGINGFACE_API_KEY') || !getenv('HUGGINGFACE_MODEL_ID')) {
            $this->markTestSkipped('HuggingFace credentials not configured');
        }

        $colors = $this->provider->generatePalette('spring garden');

        $this->assertCount(5, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/#[a-fA-F0-9]{6}/', $color);
        }
    }
} 
