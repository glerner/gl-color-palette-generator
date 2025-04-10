<?php
/**
 * Tests for Settings Migrator
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Settings
 */
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Migrator;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;

class Test_Settings_Migrator extends WP_Mock_Test_Case {
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

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
