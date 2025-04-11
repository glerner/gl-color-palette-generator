<?php
/**
 * Sample WP_Mock test file demonstrating WordPress function and hook mocking
 *
 * This file serves as an example of how to properly use WP_Mock to test
 * code that interacts with WordPress functions and hooks.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock
 */
namespace GL_Color_Palette_Generator\Tests\WP_Mock;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

/**
 * Example test class demonstrating WP_Mock usage patterns
 *
 * This sample test shows how to:
 * 1. Mock WordPress functions
 * 2. Mock WordPress hooks and filters
 * 3. Verify that hooks were called as expected
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock
 */
class Sample_WP_Mock_Test extends WP_Mock_Test_Case {
	/**
	 * Set up the test environment before each test
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Test WordPress function and hook mocking capabilities
	 *
	 * Demonstrates:
	 * - How to mock a WordPress function (wp_get_theme)
	 * - How to mock a WordPress filter (the_title)
	 * - How to verify hooks were called as expected
	 *
	 * @return void
	 */
	public function test_wordpress_function_mocking(): void {
		// Test basic function mocking
		$this->mock_function( 'wp_get_theme', array( 'Name' => 'Test Theme' ) );
		$theme = wp_get_theme();
		$this->assertEquals( array( 'Name' => 'Test Theme' ), $theme );

		// Test WordPress hooks
		$this->mock_hook( 'the_title', 'Modified Title' );
		$modified_title = apply_filters( 'the_title', 'Original Title' );
		$this->assertEquals( 'Modified Title', $modified_title );

		// Verify all hooks were called
		$this->assertHooksWereCalled();
	}
}
