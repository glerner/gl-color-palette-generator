<?php
/**
 * Base test case for integration tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use WP_UnitTestCase;

/**
 * Base test case for integration tests
 */
class Test_Case_Integration extends WP_UnitTestCase {
    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        // Prevent header output during tests
        if (!headers_sent()) {
            header_remove();
        }
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void {
        parent::tearDown();
    }
}
