<?php
namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Test_Case;
use GL_Color_Palette_Generator\Providers\Provider;
use WP_Error;

/**
 * Base class for provider integration tests
 */
abstract class Test_Provider_Integration extends Test_Case {
    /**
     * The provider instance being tested
     *
     * @var Provider
     */
    protected Provider $provider;

    /**
     * Default test parameters
     *
     * @var array
     */
    protected array $test_params = [
        'prompt' => 'Modern tech company',
        'count' => 5,
        'format' => 'hex'
    ];

    /**
     * Skips the test if API credentials are not available
     */
    protected function maybe_skip_test() {
        $creds = $this->get_test_credentials();
        if (empty($creds['api_key'])) {
            $this->markTestSkipped('API credentials not available');
        }
    }

    /**
     * Retrieves test credentials
     *
     * @return array
     */
    abstract protected function get_test_credentials(): array;

    /**
     * Asserts that the given value is not a WP_Error instance
     *
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotWPError($actual, $message = '') {
        $this->assertFalse(is_wp_error($actual),
            $message ?: ($actual instanceof WP_Error ? $actual->get_error_message() : ''));
    }
}
