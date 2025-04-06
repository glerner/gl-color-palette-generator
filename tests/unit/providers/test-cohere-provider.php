<?php declare(strict_types=1);

/**
 * Cohere Provider Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Cohere_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;

/**
 * Tests for the Cohere Provider
 */
class Test_Cohere_Provider extends Test_Provider_Mock {
	protected Provider $provider;

	public function setUp(): void {
		parent::setUp();
		$this->provider = new Cohere_Provider( new Provider_Config( $this->get_test_credentials() ) );
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	protected function get_test_credentials(): array {
		return array(
			'api_key' => 'test_key_123',
			'model'   => 'command',
		);
	}

	public function test_validate_credentials() {
		$provider = new Cohere_Provider( array() );
		$this->assertInstanceOf( \WP_Error::class, $provider->validate_credentials() );

		$this->assertTrue( $this->provider->validate_credentials() );
	}

	public function test_generate_palette_validates_params() {
		$result = $this->provider->generate_palette(
			array(
				'base_color' => 'invalid',
				'mode'       => 'invalid',
				'count'      => 0,
			)
		);
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_generate_palette() {
		$params = array(
			'prompt'     => 'Modern tech company',
			'num_colors' => 4,
			'options'    => array(
				'temperature' => 0.7,
				'max_tokens'  => 500,
			),
		);

		// Mock the API response
		$mock_response = $this->get_mock_palette_response();
		$this->mock_http_response(
			json_encode(
				array(
					'generations' => array(
						array(
							'text' => json_encode( $mock_response ),
						),
					),
				)
			)
		);

		$result = $this->provider->generate_palette( $params );
		$this->assert_palette_structure( $result );
	}

	public function test_handle_invalid_response() {
		$params = array(
			'prompt'     => 'Test prompt',
			'num_colors' => 4,
		);

		// Mock an invalid response
		$this->mock_http_response( '{"generations": [{"text": "invalid"}]}' );

		$result = $this->provider->generate_palette( $params );
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_handle_api_error() {
		$params = array(
			'prompt'     => 'Test prompt',
			'num_colors' => 4,
		);

		// Mock an error response
		$this->mock_http_error( 'API Error' );

		$result = $this->provider->generate_palette( $params );
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_custom_model() {
		$config = new Provider_Config(
			array(
				'api_key' => 'test_key_123',
				'model'   => 'command-nightly',
			)
		);

		$provider = new Cohere_Provider( $config );

		// Mock successful response with custom model
		$mock_response = $this->get_mock_palette_response();
		WP_Mock::userFunction( 'wp_remote_post' )
			->with(
				\Mockery::any(),
				\Mockery::on(
					function ( $args ) {
						$body = json_decode( $args['body'], true );
						return $body['model'] === 'command-nightly';
					}
				)
			)
			->andReturn(
				array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode(
						array(
							'generations' => array(
								array(
									'text' => json_encode( $mock_response ),
								),
							),
						)
					),
				)
			);

		$result = $provider->generate_palette(
			array(
				'prompt'     => 'Test prompt',
				'num_colors' => 4,
			)
		);

		$this->assert_palette_structure( $result );
	}

	public function test_get_requirements() {
		$requirements = $this->provider->get_requirements();
		$this->assertIsArray( $requirements );
		$this->assertArrayHasKey( 'api_key', $requirements );
	}
}
