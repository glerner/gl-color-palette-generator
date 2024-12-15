<?php
namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Providers\Provider;
use WP_Mock\Tools\TestCase;

/**
 * Base class for provider mock tests
 */
abstract class Test_Provider_Mock extends TestCase {
    protected Provider $provider;

    protected array $test_params = [
        'prompt' => 'Modern tech company',
        'count' => 5,
        'format' => 'hex'
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->maybe_skip_test();
    }

    protected function maybe_skip_test(): void {
        $creds = $this->get_test_credentials();
        if (empty($creds['api_key'])) {
            $this->markTestSkipped('API credentials not available');
        }
    }

    abstract protected function get_test_credentials(): array;
}
