<?php
/**
 * Base class for unit tests
 *
 * This is an abstract base class for unit tests and is not meant to contain
 * any tests itself. It provides common functionality for all unit test classes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 * @codeCoverageIgnore
 */

 /**
  * This will be for pure unit tests with Mockery support but no WordPress dependencies.
  * Create three base test classes:
  * tests/
  * ├── class-unit-test-case.php (Base PHPUnit + Mockery for pure unit tests)
  * ├── class-wp-mock-test-case.php (For WP_Mock tests)
  * └── class-integration-test-case.php (For integration tests)
  * Then we can gradually move the misplaced test files to their correct locations.
  */
namespace GL_Color_Palette_Generator\Tests\Base;

use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

/**
 * Base Test Case class that provides Mockery integration
 */
class Unit_Test_Case extends PHPUnit_TestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Set up the test environment
     */
    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Clean up the test environment
     */
    protected function tearDown(): void {
        \Mockery::close();
        parent::tearDown();
    }
}
