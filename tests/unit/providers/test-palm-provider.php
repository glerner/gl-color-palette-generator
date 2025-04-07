<?php
/**
 * Tests for the Palm Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Providers
 */
namespace GL_Color_Palette_Generator\Tests\Unit\Providers;

use GL_Color_Palette_Generator\Tests\Test_Provider_Mock;
use GL_Color_Palette_Generator\Providers\Palm_Provider;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;

/**
 * Tests for the Palm Provider
 */
class Test_Palm_Provider extends Test_Provider_Mock {
	protected Provider $provider;

	public function setUp(): void {
		parent::setUp();

		$this->provider = new Palm_Provider( new Provider_Config( $this->get_test_credentials() ) );
	}

	public function tearDown(): void {

		parent::tearDown();
	}

	protected function get_test_credentials(): array {
		return array(
			'api_key' => 'test_key_123',
			'model'   => 'text-bison-001',
		);
	}

	/**
	 * Test generating a palette
	 */
	public function test_generate_palette(): void {
		$params = array(
			'prompt' => 'Modern tech company',
			'count'  => 5,
			'format' => 'hex',
		);

		// Mock the API response
		WP_Mock::userFunction( 'wp_remote_post' )->andReturn(
			array(
				'response' => array( 'code' => 200 ),
				'body'     => json_encode(
					array(
						'candidates' => array(
							array(
								'content' => json_encode(
									array(
										'#2C3E50',
										'#E74C3C',
										'#ECF0F1',
										'#3498DB',
										'#2ECC71',
									)
								),
							),
						),
					)
				),
			)
		);

		$colors = $this->provider->generate_palette( $params );
		$this->assertIsArray( $colors );
		$this->assertCount( 5, $colors );
		foreach ( $colors as $color ) {
			$this->assertMatchesRegularExpression( '/^#[0-9A-F]{6}$/i', $color );
		}
	}

	public function test_validate_credentials(): void {
		$provider = new Palm_Provider( array() );
		$this->assertInstanceOf( \WP_Error::class, $provider->validate_credentials() );

		$this->assertTrue( $this->provider->validate_credentials() );
	}

	public function test_generate_palette_with_invalid_params(): void {
		$result = $this->provider->generate_palette(
			array(
				'mode'  => 'invalid',
				'count' => 0,
			)
		);
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_get_requirements(): void {
		$requirements = $this->provider->get_requirements();
		$this->assertIsArray( $requirements );
		$this->assertArrayHasKey( 'api_key', $requirements );
	}

	public function test_api_url_format(): void {
		$reflection = new \ReflectionClass( $this->provider );
		$property   = $reflection->getProperty( 'api_url' );
		$property->setAccessible( true );

		$this->assertStringContainsString( 'generativelanguage.googleapis.com', $property->getValue( $this->provider ) );
	}
}
