<?php
/**
 * Tests for Settings Migrator
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Settings
 */
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Migrator;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;

/**
 * Tests for the Settings_Migrator class which handles plugin settings migrations between versions.
 *
 * The Settings_Migrator is responsible for:
 * 1. Tracking the current settings version via 'gl_cpg_settings_version' option
 * 2. Detecting when settings need to be migrated (when version in DB differs from current version)
 * 3. Converting settings from older formats to newer formats during plugin updates
 * 4. Ensuring data type consistency (strings to integers, booleans, etc.)
 * 5. Restructuring settings as needed (e.g., moving nested settings to top level)
 *
 * This migration system allows the plugin to evolve its settings structure over time
 * while preserving existing user configurations during updates. The system supports
 * incremental migrations, so if a user updates from a very old version to the latest,
 * all necessary migrations will run in sequence to ensure proper data transformation.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Settings
 * @covers GL_Color_Palette_Generator\Settings\Settings_Migrator
 */
class Test_Settings_Migrator extends WP_Mock_Test_Case {
	/**
	 * Set up the test environment before each test.
	 * 
	 * Initializes WP_Mock for WordPress function mocking.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
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
	 * Test migration when no version is set in the database.
	 * 
	 * Verifies that when no settings version exists, the migration process:
	 * 1. Checks the current version
	 * 2. Gets the current options
	 * 3. Updates the options with default values
	 * 4. Updates the settings version to the current version
	 *
	 * @return void
	 */
	public function test_maybe_migrate_no_version(): void {
		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_settings_version', '0.0.0' ),
				'return' => '0.0.0',
				'times'  => 1,
			)
		);

		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_options', array() ),
				'return' => array(),
				'times'  => 1,
			)
		);

		WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'gl_cpg_options', $this->anything() ),
				'return' => true,
				'times'  => 2,
			)
		);

		WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'gl_cpg_settings_version', '1.0.0' ),
				'return' => true,
				'times'  => 1,
			)
		);

		Settings_Migrator::maybe_migrate();
		$this->assertConditionsMet();
	}

	/**
	 * Test migration when an old version is set in the database.
	 * 
	 * Verifies that when an outdated settings version exists (0.5.0), the migration process:
	 * 1. Properly converts old format settings to the new format
	 * 2. Maintains existing values where appropriate
	 * 3. Updates the settings version to the current version
	 *
	 * @return void
	 */
	public function test_maybe_migrate_old_version(): void {
		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_settings_version', '0.0.0' ),
				'return' => '0.5.0',
				'times'  => 1,
			)
		);

		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_options', array() ),
				'return' => array(
					'cache_enabled' => true,
					'api_settings'  => array(
						'provider' => 'openai',
						'key'      => 'test-key',
					),
				),
				'times'  => 1,
			)
		);

		WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array(
					'gl_cpg_options',
					$this->callback(
						function ( $options ) {
							return isset( $options['cache_duration'] ) &&
								$options['cache_duration'] === 3600 &&
								isset( $options['ai_provider'] ) &&
								$options['ai_provider'] === 'openai' &&
								isset( $options['api_key'] ) &&
								$options['api_key'] === 'test-key';
						}
					),
				),
				'return' => true,
				'times'  => 1,
			)
		);

		WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array( 'gl_cpg_settings_version', '1.0.0' ),
				'return' => true,
				'times'  => 1,
			)
		);

		Settings_Migrator::maybe_migrate();
		$this->assertConditionsMet();
	}

	/**
	 * Test that no migration occurs when the current version is already set.
	 * 
	 * Verifies that when the settings version is already at the current version (1.0.0),
	 * no migration actions are performed.
	 *
	 * @return void
	 */
	public function test_maybe_migrate_current_version(): void {
		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_settings_version', '0.0.0' ),
				'return' => '1.0.0',
				'times'  => 1,
			)
		);

		Settings_Migrator::maybe_migrate();
		$this->assertConditionsMet();
	}

	/**
	 * Test specific migration to version 1.0.0.
	 * 
	 * Verifies that the migration to version 1.0.0 properly converts:
	 * 1. String values to their appropriate types (int, bool)
	 * 2. Properly handles all settings that need type conversion
	 * 3. Updates the options in the database
	 *
	 * @return void
	 */
	public function test_migrate_to_1_0_0(): void {
		$old_options = array(
			'cache_duration'   => '3600',
			'max_colors'       => '10',
			'default_colors'   => '5',
			'enable_analytics' => '1',
			'rate_limit'       => '60',
			'debug_mode'       => '0',
		);

		WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'gl_cpg_options', array() ),
				'return' => $old_options,
				'times'  => 1,
			)
		);

		WP_Mock::userFunction(
			'update_option',
			array(
				'args'   => array(
					'gl_cpg_options',
					$this->callback(
						function ( $options ) {
							return is_int( $options['cache_duration'] ) &&
								is_int( $options['max_colors'] ) &&
								is_int( $options['default_colors'] ) &&
								is_bool( $options['enable_analytics'] ) &&
								is_int( $options['rate_limit'] ) &&
								is_bool( $options['debug_mode'] );
						}
					),
				),
				'return' => true,
				'times'  => 1,
			)
		);

		Settings_Migrator::maybe_migrate();
		$this->assertConditionsMet();
	}
}
