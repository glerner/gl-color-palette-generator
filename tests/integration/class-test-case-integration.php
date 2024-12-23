<?php
/**
 * Base class for integration tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use WP_Mock\Tools\TestCase as WP_Mock_TestCase;
use Brain\Monkey;

/**
 * Base test case class for integration tests
 */
class Test_Case_Integration extends WP_Mock_TestCase {
    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        WP_Mock::setUp();
    }

    /**
     * Clean up test environment
     */
    public function tearDown(): void {
        WP_Mock::tearDown();
        Monkey\tearDown();
        parent::tearDown();
    }
}
