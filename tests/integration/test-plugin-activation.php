<?php
/**
 * Test plugin activation
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;

/**
 * Test plugin activation and deactivation
 */
class Plugin_Activation_Test extends Integration_Test_Case {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		// Include plugin.php for is_plugin_active function
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	/**
	 * Test plugin activation
	 */
	public function test_plugin_activation() {
		// Check if the plugin is active by checking for an option that would be set during activation
		$options = get_option('gl_color_palette_generator_options');
		$this->assertNotFalse($options, 'Plugin options should be set after activation');
	}

	/**
	 * Test plugin options are set
	 */
	public function test_plugin_options() {
		$version = get_option( 'gl_color_palette_generator_version' );
		$this->assertNotEmpty( $version, 'Plugin version should be set' );
	}
}
