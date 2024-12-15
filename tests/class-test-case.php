<?php declare(strict_types=1);

/**
 * Base Test Case
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

/**
 * Base test case class that all test classes should extend
 */
class Test_Case extends PHPUnit_TestCase {
    /**
     * Set up the test case
     */
    public function setUp(): void {
        parent::setUp();
    }

    /**
     * Tear down after the test
     */
    public function tearDown(): void {
        parent::tearDown();
    }
}
