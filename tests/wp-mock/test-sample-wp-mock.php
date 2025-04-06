<?php
namespace GL_Color_Palette_Generator\Tests\WP_Mock;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

class Sample_WP_Mock_Test extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
	public function setUp(): void {
		parent::setUp();
	}

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
