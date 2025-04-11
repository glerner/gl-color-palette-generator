<?php
/**
 * Tests for the Settings Manager class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Settings
 */
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Settings\Settings_Types;
use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;
use WP_Mock;

class Test_Settings_Manager extends WP_Mock_Test_Case {
	/**
	 * The Settings_Manager instance being tested.
	 *
	 * @var Settings_Manager
	 */
	private Settings_Manager $settings_manager;

	/**
	 * Set up the test environment before each test.
	 * 
	 * Initializes WP_Mock and creates a new Settings_Manager instance.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
		$this->settings_manager = new Settings_Manager();
	}

	/**
	 * Clean up the test environment after each test.
	 * 
	 * Tears down WP_Mock to ensure a clean state for the next test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test that the init method properly adds the register_settings method to the admin_init hook.
	 *
	 * @return void
	 */
	public function test_init(): void {
		WP_Mock::expectActionAdded( 'admin_init', array( $this->settings_manager, 'register_settings' ) );
		$this->settings_manager->init();
		$this->assertConditionsMet();
	}

	/**
	 * Test that the register_settings method properly registers settings, sections, and fields.
	 * 
	 * Verifies that WordPress functions are called with the correct arguments to register
	 * the plugin settings, create a settings section, and add all the required settings fields.
	 *
	 * @return void
	 */
	public function test_register_settings(): void {
		WP_Mock::userFunction(
			'register_setting',
			array(
				'times' => 1,
				'args'  => array(
					'gl_cpg_settings',
					'gl_cpg_options',
					array(
						'type'              => 'array',
						'sanitize_callback' => array( $this->settings_manager, 'sanitize_settings' ),
						'default'           => Settings_Types::DEFAULT_SETTINGS,
					),
				),
			)
		);

		WP_Mock::userFunction(
			'add_settings_section',
			array(
				'times' => 1,
				'args'  => array(
					'gl_cpg_main_section',
					$this->anything(),
					array( $this->settings_manager, 'render_section' ),
					'gl_cpg_settings',
				),
			)
		);

		WP_Mock::userFunction(
			'add_settings_field',
			array(
				'times' => '>=' . count( Settings_Types::get_field_definitions() ),
			)
		);

		$this->settings_manager->register_settings();
		$this->assertConditionsMet();
	}

	/**
	 * Test that the render_section method outputs the expected HTML content.
	 * 
	 * Verifies that the section description is properly escaped and displayed.
	 *
	 * @return void
	 */
	public function test_render_section(): void {
		WP_Mock::userFunction(
			'esc_html__',
			array(
				'args'   => array( 'Configure your color palette generator settings below.', 'gl-color-palette-generator' ),
				'return' => 'Configure your color palette generator settings below.',
			)
		);

		ob_start();
		$this->settings_manager->render_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Configure your color palette generator settings below.', $output );
	}

	/**
	 * Test that the render_field method correctly renders a select field.
	 * 
	 * Verifies that the select field contains all expected options from the AI_PROVIDERS list
	 * and that proper escaping and selection functions are called.
	 *
	 * @return void
	 */
	public function test_render_field_select(): void {
		$args = array(
			'key'   => 'ai_provider',
			'field' => array(
				'type'    => 'select',
				'title'   => 'AI Provider',
				'options' => Settings_Types::AI_PROVIDERS,
			),
		);

		WP_Mock::userFunction(
			'esc_attr',
			array(
				'return_arg' => true,
			)
		);

		WP_Mock::userFunction(
			'esc_html',
			array(
				'return_arg' => true,
			)
		);

		WP_Mock::userFunction(
			'selected',
			array(
				'return' => 'selected="selected"',
			)
		);

		ob_start();
		$this->settings_manager->render_field( $args );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<select', $output );
		$this->assertStringContainsString( '</select>', $output );
		foreach ( Settings_Types::AI_PROVIDERS as $key => $label ) {
			$this->assertStringContainsString( $key, $output );
			$this->assertStringContainsString( $label, $output );
		}
	}

	/**
	 * Test that the render_field method correctly renders a password field.
	 * 
	 * Verifies that the password input has the correct type and CSS classes.
	 *
	 * @return void
	 */
	public function test_render_field_password(): void {
		$args = array(
			'key'   => 'api_key',
			'field' => array(
				'type'  => 'password',
				'title' => 'API Key',
			),
		);

		WP_Mock::userFunction(
			'esc_attr',
			array(
				'return_arg' => true,
			)
		);

		ob_start();
		$this->settings_manager->render_field( $args );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<input type="password"', $output );
		$this->assertStringContainsString( 'class="regular-text"', $output );
	}

	/**
	 * Test that the render_field method correctly renders a number field.
	 * 
	 * Verifies that the number input has the correct type, min/max attributes, and CSS classes.
	 *
	 * @return void
	 */
	public function test_render_field_number(): void {
		$args = array(
			'key'   => 'cache_duration',
			'field' => array(
				'type'  => 'number',
				'title' => 'Cache Duration',
				'min'   => 0,
				'max'   => 86400,
			),
		);

		WP_Mock::userFunction(
			'esc_attr',
			array(
				'return_arg' => true,
			)
		);

		ob_start();
		$this->settings_manager->render_field( $args );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<input type="number"', $output );
		$this->assertStringContainsString( 'min="0"', $output );
		$this->assertStringContainsString( 'max="86400"', $output );
		$this->assertStringContainsString( 'class="small-text"', $output );
	}

	/**
	 * Test that the sanitize_settings method correctly validates and returns settings when all inputs are valid.
	 * 
	 * Verifies that each setting is properly sanitized and returned in the expected format.
	 *
	 * @return void
	 */
	public function test_sanitize_settings_success(): void {
		$input = array(
			'ai_provider'      => 'openai',
			'api_key'          => 'sk-1234567890abcdef1234567890abcdef1234567890abcdef',
			'cache_duration'   => 3600,
			'max_colors'       => 10,
			'default_colors'   => 5,
			'enable_analytics' => true,
			'rate_limit'       => 60,
			'debug_mode'       => false,
		);

		$output = $this->settings_manager->sanitize_settings( $input );

		$this->assertEquals( 'openai', $output['ai_provider'] );
		$this->assertEquals( 'sk-1234567890abcdef1234567890abcdef1234567890abcdef', $output['api_key'] );
		$this->assertEquals( 3600, $output['cache_duration'] );
		$this->assertEquals( 10, $output['max_colors'] );
		$this->assertEquals( 5, $output['default_colors'] );
		$this->assertTrue( $output['enable_analytics'] );
		$this->assertEquals( 60, $output['rate_limit'] );
		$this->assertFalse( $output['debug_mode'] );
	}

	/**
	 * Test that the sanitize_settings method throws an exception when a required field is missing.
	 * 
	 * Verifies that an exception is thrown with the appropriate message when the AI provider
	 * setting (which is required) is not provided.
	 *
	 * @return void
	 */
	public function test_sanitize_settings_missing_required(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Field AI Provider is required' );

		$input = array(
			'cache_duration' => 3600,
		);

		$this->settings_manager->sanitize_settings( $input );
	}

	/**
	 * Test that the sanitize_settings method throws an exception when a setting has an invalid value.
	 * 
	 * Verifies that an exception is thrown with the appropriate message when the cache_duration
	 * setting has a negative value, which is outside its valid range.
	 *
	 * @return void
	 */
	public function test_sanitize_settings_invalid_value(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid value for Cache Duration' );

		$input = array(
			'ai_provider'    => 'openai',
			'api_key'        => 'sk-1234567890abcdef1234567890abcdef1234567890abcdef',
			'cache_duration' => -1,
		);

		$this->settings_manager->sanitize_settings( $input );
	}

	/**
	 * Test that the get_option method correctly retrieves options from WordPress.
	 * 
	 * Verifies that:
	 * 1. Existing options are correctly returned
	 * 2. Default values are returned for non-existent options
	 *
	 * @return void
	 */
	public function test_get_option(): void {
		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_options', array() ),
				'return' => array( 'ai_provider' => 'openai' ),
			)
		);

		$value = $this->settings_manager->get_option( 'ai_provider' );
		$this->assertEquals( 'openai', $value );

		$default_value = $this->settings_manager->get_option( 'nonexistent_key' );
		$this->assertEquals( Settings_Types::DEFAULT_SETTINGS['nonexistent_key'] ?? null, $default_value );
	}

	/**
	 * Test that the update_option method successfully updates a valid setting.
	 * 
	 * Verifies that the WordPress update_option function is called with the correct arguments
	 * and that the method returns true on success.
	 *
	 * @return void
	 */
	public function test_update_option_success(): void {
		WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'gl_cpg_options', $this->anything() ),
				'return' => true,
			)
		);

		$result = $this->settings_manager->update_option( 'cache_duration', 7200 );
		$this->assertTrue( $result );
	}

	/**
	 * Test that the update_option method throws an exception when given an invalid setting key.
	 * 
	 * Verifies that an exception is thrown with the appropriate message when attempting
	 * to update a setting that doesn't exist in the defined settings.
	 *
	 * @return void
	 */
	public function test_update_option_invalid_key(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid setting key: invalid_key' );

		$this->settings_manager->update_option( 'invalid_key', 'value' );
	}

	/**
	 * Test that the update_option method throws an exception when given an invalid setting value.
	 * 
	 * Verifies that an exception is thrown with the appropriate message when attempting
	 * to update the cache_duration setting with a negative value.
	 *
	 * @return void
	 */
	public function test_update_option_invalid_value(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid value for Cache Duration' );

		$this->settings_manager->update_option( 'cache_duration', -1 );
	}
}
