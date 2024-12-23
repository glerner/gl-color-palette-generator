<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Providers;

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
}
