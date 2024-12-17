<?php
/**
 * Provider Integration Test Base Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Providers\Provider;
use WP_Error;

/**
 * Base class for provider integration tests
 *
 * This abstract class provides common functionality for testing
 * different AI provider integrations. It includes methods for
 * credential validation and basic palette generation testing.
 *
 * @since 1.0.0
 */
abstract class Test_Provider_Integration extends TestCase {
    /**
     * The provider instance being tested
     *
     * @var Provider The AI provider instance
     */
    protected Provider $provider;

    /**
     * Default test parameters
     *
     * @var array Parameters for testing palette generation
     */
    protected array $test_params = [
        'prompt' => 'Modern tech company',
        'count' => 5,
        'format' => 'hex'
    ];

    /**
     * Set up the test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        $this->maybe_skip_test();
    }

    /**
     * Tear down the test environment after each test
     */
    public function tearDown(): void {
        parent::tearDown();
        $this->provider = null;
    }

    /**
     * Skips the test if API credentials are not available
     *
     * This method should be called in setUp() to prevent test failures
     * when API credentials are not configured in the environment.
     *
     * @return void
     */
    protected function maybe_skip_test(): void {
        $creds = $this->get_test_credentials();
        if (empty($creds['api_key'])) {
            $this->markTestSkipped('API credentials not available');
        }
    }

    /**
     * Retrieves test credentials
     *
     * This method should be implemented by child classes to provide
     * the necessary API credentials for testing.
     *
     * @return array API credentials
     */
    abstract protected function get_test_credentials(): array;

    /**
     * Asserts that the given value is not a WP_Error instance
     *
     * @param mixed $actual The value to check
     * @param string $message Optional error message
     */
    public function assertNotWPError($actual, $message = '') {
        $this->assertFalse(
            is_wp_error($actual),
            $message ?: ($actual instanceof WP_Error ? $actual->get_error_message() : '')
        );
    }
}
