<?php
/**
 * Tests for Settings Validator
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Settings
 */
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Validator;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;
use WP_Mock;

class Test_Settings_Validator extends WP_Mock_Test_Case {
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	public function test_validate_settings_success(): void {
		$settings = array(
			'ai_provider'      => 'openai',
			'api_key'          => 'sk-1234567890abcdef1234567890abcdef1234567890abcdef',
			'cache_duration'   => 3600,
			'max_colors'       => 10,
			'default_colors'   => 5,
			'enable_analytics' => true,
			'rate_limit'       => 60,
			'debug_mode'       => false,
		);

		$result = Settings_Validator::validate_settings( $settings );
		$this->assertTrue( $result );
	}

	public function test_validate_default_colors_exceeding_max(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Default colors cannot be greater than maximum colors' );

		$settings = array(
			'max_colors'     => 5,
			'default_colors' => 10,
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_provider_settings_missing_api_key(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'API key is required for openai provider' );

		$settings = array(
			'ai_provider' => 'openai',
			'api_key'     => '',
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_openai_settings_invalid_key(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid OpenAI API key format' );

		$settings = array(
			'ai_provider' => 'openai',
			'api_key'     => 'invalid-key',
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_anthropic_settings_invalid_key(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid Anthropic API key format' );

		$settings = array(
			'ai_provider' => 'anthropic',
			'api_key'     => 'invalid-key',
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_palm_settings_invalid_key(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid PaLM API key format' );

		$settings = array(
			'ai_provider' => 'palm',
			'api_key'     => 'invalid-key',
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_cohere_settings_invalid_key(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Invalid Cohere API key format' );

		$settings = array(
			'ai_provider' => 'cohere',
			'api_key'     => 'invalid-key',
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_cache_settings_analytics_enabled(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Cache duration must be at least 1 hour when analytics is enabled' );

		$settings = array(
			'enable_analytics' => true,
			'cache_duration'   => 1800, // 30 minutes
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_rate_limit_settings_exceeded(): void {
		$this->expectException( Palette_Generation_Exception::class );
		$this->expectExceptionMessage( 'Maximum rate limit for openai is 60 requests per minute' );

		$settings = array(
			'ai_provider' => 'openai',
			'rate_limit'  => 70,
		);

		Settings_Validator::validate_settings( $settings );
	}

	public function test_validate_provider_specific_rate_limits(): void {
		$providers = array(
			'openai'    => 60,
			'anthropic' => 50,
			'palm'      => 40,
			'cohere'    => 30,
		);

		foreach ( $providers as $provider => $max_limit ) {
			$settings = array(
				'ai_provider' => $provider,
				'rate_limit'  => $max_limit,
				'api_key'     => 'sk-1234567890abcdef1234567890abcdef1234567890abcdef',
			);

			$result = Settings_Validator::validate_settings( $settings );
			$this->assertTrue( $result, "Failed for provider $provider with max limit $max_limit" );
		}
	}
}
