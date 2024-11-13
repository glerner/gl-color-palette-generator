<?php
namespace GLColorPalette\Tests\Providers;

use WP_UnitTestCase;
use GLColorPalette\Tests\TestHelpers;

abstract class Provider_Test_Case extends WP_UnitTestCase {
    protected $provider;

    /**
     * Get the provider class name
     *
     * @return string
     */
    abstract protected function get_provider_class();

    /**
     * Get test credentials for the provider
     *
     * @return array
     */
    abstract protected function get_test_credentials();

    /**
     * Set up the test case
     */
    public function setUp(): void {
        parent::setUp();
        $provider_class = $this->get_provider_class();
        $this->provider = TestHelpers::create_mock_provider($provider_class, $this->get_test_credentials());
    }

    /**
     * Test that the provider validates credentials
     */
    public function test_validate_credentials() {
        $provider_class = $this->get_provider_class();
        $empty_provider = new $provider_class([]);
        $this->assertWPError($empty_provider->validate_credentials());

        $this->assertTrue($this->provider->validate_credentials());
    }

    /**
     * Test that the provider validates palette generation parameters
     */
    public function test_generate_palette_validates_params() {
        $result = $this->provider->generate_palette([
            'theme' => '',
            'count' => 0
        ]);
        $this->assertWPError($result);

        $result = $this->provider->generate_palette([
            'theme' => 'test theme',
            'count' => 5
        ]);
        $this->assertNotWPError($result);
    }

    /**
     * Test that the provider returns requirements
     */
    public function test_get_requirements() {
        $requirements = $this->provider->get_requirements();
        $this->assertIsArray($requirements);
        $this->assertNotEmpty($requirements);
    }
} 
