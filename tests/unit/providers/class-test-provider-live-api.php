<?php
/**
 * Provider Integration Test Base Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 * @since 1.0.0
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests;

use WP_Error;
use GL_Color_Palette_Generator\Providers\Provider;

/**
 * Base class for provider integration tests
 *
 * This abstract class provides common functionality for testing
 * different AI provider integrations. It includes methods for
 * credential validation and basic palette generation testing.
 *
 * @since 1.0.0
 */
abstract class Test_Provider_Integration extends Test_Case_Integration {
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
        $this->provider = null;
        parent::tearDown();
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
        $credentials = $this->get_test_credentials();
        foreach ($credentials as $key => $value) {
            if (empty($value)) {
                $this->markTestSkipped("Missing required credential: $key");
            }
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
     * @param mixed  $actual  The value to check
     * @param string $message Optional error message
     */
    public function assertNotWPError($actual, $message = '') {
        $this->assertNotInstanceOf(
            WP_Error::class,
            $actual,
            $message ?: 'Value is an instance of WP_Error'
        );
    }

    /**
     * Assert that a palette has valid colors
     *
     * @param array $palette The palette to check
     */
    protected function assertValidPalette($palette) {
        $this->assertIsArray($palette, 'Palette should be an array');
        $this->assertNotEmpty($palette, 'Palette should not be empty');

        foreach ($palette as $color) {
            $this->assertMatchesRegularExpression(
                '/^#[0-9A-F]{6}$/i',
                $color,
                "Color '$color' is not a valid hex color"
            );
        }
    }

    /**
     * Assert that colors in a palette meet WCAG contrast requirements
     *
     * @param array $palette The palette to check
     */
    protected function assertPaletteAccessible($palette) {
        $checker = new \GL_Color_Palette_Generator\Accessibility\Accessibility_Checker();

        // Check each color against others for WCAG AA compliance
        $colors_count = count($palette);
        for ($i = 0; $i < $colors_count; $i++) {
            for ($j = $i + 1; $j < $colors_count; $j++) {
                $this->assertTrue(
                    $checker->meets_wcag_aa($palette[$i], $palette[$j], 'normal'),
                    sprintf(
                        'Colors %s and %s should meet WCAG AA contrast requirements',
                        $palette[$i],
                        $palette[$j]
                    )
                );
            }
        }
    }
}
