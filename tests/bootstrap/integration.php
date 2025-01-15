<?php
/**
 * Bootstrap file for integration tests
 *
 * This file sets up the WordPress testing environment specifically for integration tests.
 * It leverages the main WordPress test framework bootstrap (wp.php) to ensure consistent
 * test environment setup while adding any integration-test specific configurations.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Bootstrap;

// Load the main WordPress test framework bootstrap
require_once __DIR__ . '/wp.php';

// Add any integration-test specific setup here
