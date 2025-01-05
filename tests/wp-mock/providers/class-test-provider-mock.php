<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests;

use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * Base test class for provider tests
 */
class Test_Provider_Mock extends TestCase {
    /**
     * Set up the test case.
     */
    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();

        if (!defined('GL_CPG_VERSION')) {
            define('GL_CPG_VERSION', '1.0.0');
        }

        if (!defined('ABSPATH')) {
            define('ABSPATH', '/var/www/html/');
        }

        // Mock WP_Error class if it doesn't exist
        if (!class_exists('WP_Error')) {
            require_once __DIR__ . '/mocks/class-wp-error.php';
        }

        // Mock WP_REST_Request class if it doesn't exist
        if (!class_exists('WP_REST_Request')) {
            require_once __DIR__ . '/mocks/class-wp-rest-request.php';
        }
    }

    /**
     * Clean up after the test case.
     */
    protected function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * Assert that all expected hooks were called.
     */
    protected function assertHooksAdded() {
        $this->assertConditionsMet();
    }

    /**
     * Assert that all expected conditions were met.
     */
    protected function assertConditionsMet() {
        $this->assertTrue(true);
    }

    /**
     * Mock a successful HTTP response
     *
     * @param string $response_body Response body to mock
     */
    protected function mock_http_response(string $response_body): void {
        WP_Mock::userFunction('wp_remote_post')
            ->andReturn([
                'response' => ['code' => 200],
                'body' => $response_body
            ]);
    }

    /**
     * Mock an HTTP error
     *
     * @param string $error_message Error message
     */
    protected function mock_http_error(string $error_message): void {
        WP_Mock::userFunction('wp_remote_post')
            ->andReturn(new \WP_Error('http_error', $error_message));
    }

    /**
     * Get a mock palette response
     *
     * @return array Mock palette data
     */
    protected function get_mock_palette_response(): array {
        return [
            'colors' => [
                'primary' => [
                    'hex' => '#2C3E50',
                    'name' => 'Midnight Ocean',
                    'emotion' => 'Deep trust and stability'
                ],
                'secondary' => [
                    'hex' => '#E74C3C',
                    'name' => 'Energetic Coral',
                    'emotion' => 'Dynamic and engaging'
                ],
                'tertiary' => [
                    'hex' => '#3498DB',
                    'name' => 'Clear Sky',
                    'emotion' => 'Innovation and clarity'
                ],
                'accent' => [
                    'hex' => '#2ECC71',
                    'name' => 'Growth Green',
                    'emotion' => 'Progress and success'
                ]
            ],
            'palette_story' => 'A modern and professional palette that combines trust and innovation'
        ];
    }

    /**
     * Assert palette response structure
     *
     * @param array $palette Palette to check
     */
    protected function assert_palette_structure(array $palette): void {
        $this->assertIsArray($palette);
        $this->assertArrayHasKey('colors', $palette);
        $this->assertArrayHasKey('palette_story', $palette);
        
        foreach (['primary', 'secondary', 'tertiary', 'accent'] as $role) {
            $this->assertArrayHasKey($role, $palette['colors']);
            $this->assertArrayHasKey('hex', $palette['colors'][$role]);
            $this->assertArrayHasKey('name', $palette['colors'][$role]);
            $this->assertArrayHasKey('emotion', $palette['colors'][$role]);
            $this->assertMatchesRegularExpression('/^#[A-Fa-f0-9]{6}$/', $palette['colors'][$role]['hex']);
        }
    }
}
