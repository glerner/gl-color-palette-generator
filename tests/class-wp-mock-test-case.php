<?php
/**
 * Base Test Case for WordPress Mock Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use WP_Mock;

/**
 * Base Test Case class that provides WP_Mock integration
 * Extends our base Test_Case to maintain Mockery support
 */
class WP_Mock_Test_Case extends Unit_Test_Case {

    /**
     * Set up the test environment
     * Initializes WP_Mock for each test
     */
    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    /**
     * Clean up the test environment
     * Tears down WP_Mock after each test
     */
    protected function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * Assert that all expected filters were called
     * This method should be called at the end of each test
     */
    protected function assertHooksWereCalled() {
        $this->assertTrue(WP_Mock::expectedFiltersCalled());
        $this->assertTrue(WP_Mock::expectedActionsCalled());
    }
}
