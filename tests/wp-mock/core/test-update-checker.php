<?php
/**
 * Test Update Checker Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Core;

use GL_Color_Palette_Generator\Core\Update_Checker;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use Mockery;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

class Test_Update_Checker extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
	private $update_checker;
	private $update_url  = 'https://example.com/updates.json';
	private $plugin_file = '/path/to/plugin.php';
	private $plugin_slug = 'test-plugin/test-plugin.php';

	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();

		$this->update_checker = new Update_Checker(
			$this->update_url,
			$this->plugin_file,
			$this->plugin_slug
		);
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	public function test_check_for_updates_with_empty_transient(): void {
		$transient = (object) array( 'checked' => array() );

		$result = $this->update_checker->check_for_updates( $transient );

		$this->assertEquals( $transient, $result );
	}

	public function test_check_for_updates_with_newer_version(): void {
		$transient = (object) array(
			'checked' => array(
				$this->plugin_slug => '1.0.0',
			),
		);

		$remote_data = (object) array(
			'version'      => '1.1.0',
			'homepage'     => 'https://example.com',
			'download_url' => 'https://example.com/download',
			'tested'       => '6.2',
			'requires_php' => '8.0',
		);

		WP_Mock::userFunction(
			'get_plugin_data',
			array(
				'args'   => array( $this->plugin_file ),
				'return' => array( 'Version' => '1.0.0' ),
			)
		);

		WP_Mock::userFunction(
			'wp_remote_get',
			array(
				'args'   => array( $this->update_url, Mockery::any() ),
				'return' => array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode( $remote_data ),
				),
			)
		);

		WP_Mock::userFunction(
			'wp_remote_retrieve_response_code',
			array(
				'return' => 200,
			)
		);

		WP_Mock::userFunction(
			'wp_remote_retrieve_body',
			array(
				'return' => json_encode( $remote_data ),
			)
		);

		$result = $this->update_checker->check_for_updates( $transient );

		$this->assertObjectHasAttribute( 'response', $result );
		$this->assertArrayHasKey( $this->plugin_slug, $result->response );
		$this->assertEquals( '1.1.0', $result->response[ $this->plugin_slug ]->new_version );
	}

	public function test_plugin_info(): void {
		$args = (object) array( 'slug' => $this->plugin_slug );

		$remote_data = (object) array(
			'name'           => 'Test Plugin',
			'version'        => '1.1.0',
			'author'         => 'Test Author',
			'author_profile' => 'https://example.com',
			'requires'       => '6.2',
			'tested'         => '6.2',
			'requires_php'   => '8.0',
			'sections'       => (object) array(
				'description'  => 'Test description',
				'installation' => 'Test installation',
				'changelog'    => 'Test changelog',
			),
			'download_url'   => 'https://example.com/download',
		);

		WP_Mock::userFunction(
			'wp_remote_get',
			array(
				'return' => array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode( $remote_data ),
				),
			)
		);

		WP_Mock::userFunction(
			'wp_remote_retrieve_response_code',
			array(
				'return' => 200,
			)
		);

		WP_Mock::userFunction(
			'wp_remote_retrieve_body',
			array(
				'return' => json_encode( $remote_data ),
			)
		);

		$result = $this->update_checker->plugin_info( false, 'plugin_information', $args );

		$this->assertIsObject( $result );
		$this->assertEquals( 'Test Plugin', $result->name );
		$this->assertEquals( '1.1.0', $result->version );
		$this->assertObjectHasAttribute( 'sections', $result );
	}

	public function test_plugin_info_wrong_action(): void {
		$result = $this->update_checker->plugin_info(
			false,
			'wrong_action',
			(object) array( 'slug' => $this->plugin_slug )
		);

		$this->assertFalse( $result );
	}

	public function test_plugin_info_wrong_slug(): void {
		$result = $this->update_checker->plugin_info(
			false,
			'plugin_information',
			(object) array( 'slug' => 'wrong-slug' )
		);

		$this->assertFalse( $result );
	}
}
