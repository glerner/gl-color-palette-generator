<?php
/**
 * Test plugin activation
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

/**
 * Test plugin activation and deactivation
 */
class Plugin_Activation_Test extends Integration_Test_Case {

	/**
	 * Test plugin activation
	 */
	public function test_plugin_activation() {
		$this->assertTrue( is_plugin_active( 'gl-color-palette-generator/gl-color-palette-generator.php' ) );
	}

	/**
	 * Test plugin options are set
	 */
	public function test_plugin_options() {
		$version = get_option( 'gl_color_palette_generator_version' );
		$this->assertNotEmpty( $version, 'Plugin version should be set' );
	}
}
