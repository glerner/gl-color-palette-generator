<?php
/**
 * Integration tests for the Azure OpenAI provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Integration\Providers
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration\Providers;

use GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case;

use GL_Color_Palette_Generator\Tests\Test_Provider_Integration;
use GL_Color_Palette_Generator\Providers\Azure_OpenAI_Provider;

/**
 * Test Azure OpenAI integration
 */
class Test_Azure_OpenAI_Integration extends Test_Provider_Integration {
	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->maybe_skip_test();
	}

	/**
	 * Skips the test if API credentials are not available
	 */
	protected function maybe_skip_test(): void {
		$credentials = $this->get_test_credentials();
		foreach ( $credentials as $key => $value ) {
			if ( empty( $value ) ) {
				$this->markTestSkipped( "Missing required credential: $key" );
			}
		}
	}

	/**
	 * Returns the test credentials for the Azure OpenAI provider
	 *
	 * @return array
	 */
	protected function get_test_credentials(): array {
		return array(
			'api_key'    => getenv( 'AZURE_OPENAI_API_KEY' ),
			'endpoint'   => getenv( 'AZURE_OPENAI_ENDPOINT' ),
			'deployment' => getenv( 'AZURE_OPENAI_DEPLOYMENT' ),
		);
	}

	/**
	 * Test that we can create a valid provider instance
	 */
	public function test_create_provider() {
		$provider = new Azure_OpenAI_Provider( $this->get_test_credentials() );
		$this->assertInstanceOf( Azure_OpenAI_Provider::class, $provider );
	}

	/**
	 * Test that we can generate a color palette
	 */
	public function test_generate_palette() {
		$provider = new Azure_OpenAI_Provider( $this->get_test_credentials() );
		$result   = $provider->generate_palette( 'A sunset over the ocean' );
		$this->assertNotWPError( $result );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
	}
}
