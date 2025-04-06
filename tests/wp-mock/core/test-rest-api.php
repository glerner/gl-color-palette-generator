<?php
/**
 * Test REST API Class
 *
 * Tests for the REST API functionality of the plugin.
 * Ensures proper registration and handling of REST routes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Core;

use GL_Color_Palette_Generator\Core\REST_API;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use Mockery;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_REST_Request;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_REST_Response;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Error;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;

/**
 * Test class for REST_API
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Core\REST_API
 */
class Test_REST_API extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
	/**
	 * Set up test environment
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @return void
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test route registration
	 *
	 * Ensures that all required REST API routes are properly registered
	 * with WordPress.
	 *
	 * @covers ::register_routes
	 * @return void
	 */
	public function test_register_routes(): void {
		WP_Mock::userFunction(
			'register_rest_route',
			array(
				'times' => 3,
			)
		);

		REST_API::register_routes();
	}

	/**
	 * Test successful palette generation
	 *
	 * Ensures that a color palette is successfully generated and returned
	 * via the REST API.
	 *
	 * @covers ::generate_palette
	 * @return void
	 */
	public function test_generate_palette_success(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'base_color', '#ff0000' );
		$request->set_param( 'palette_type', 'analogous' );
		$request->set_param( 'num_colors', 5 );

		$mock_palette = Mockery::mock( '\GL_Color_Palette_Generator\Color_Management\Color_Palette' );
		$mock_palette->shouldReceive( 'get_colors' )
			->once()
			->andReturn( array( '#ff0000', '#ff3300', '#ff6600' ) );

		$mock_generator = Mockery::mock( '\GL_Color_Palette_Generator\Generators\ML_Color_Engine' );
		$mock_generator->shouldReceive( 'generate_palette' )
			->once()
			->with( '#ff0000', 'analogous', 5 )
			->andReturn( $mock_palette );

		WP_Mock::userFunction(
			'GL_Color_Palette_Generator\Generators\ML_Color_Engine::__construct',
			array(
				'return' => $mock_generator,
			)
		);

		$response = REST_API::generate_palette( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
	}

	/**
	 * Test successful retrieval of palettes
	 *
	 * Ensures that a list of color palettes is successfully retrieved and
	 * returned via the REST API.
	 *
	 * @covers ::get_palettes
	 * @return void
	 */
	public function test_get_palettes_success(): void {
		$request = new WP_REST_Request();

		$mock_storage = Mockery::mock( '\GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage' );
		$mock_storage->shouldReceive( 'get_all_palettes' )
			->once()
			->andReturn(
				array(
					array(
						'id'     => 1,
						'name'   => 'Test Palette',
						'colors' => array( '#ff0000', '#00ff00' ),
					),
				)
			);

		WP_Mock::userFunction(
			'GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage::__construct',
			array(
				'return' => $mock_storage,
			)
		);

		$response = REST_API::get_palettes( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
	}

	/**
	 * Test retrieval of non-existent palette
	 *
	 * Ensures that a 404 error is returned when attempting to retrieve a
	 * non-existent color palette via the REST API.
	 *
	 * @covers ::get_palette
	 * @return void
	 */
	public function test_get_palette_not_found(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'id', 999 );

		$mock_storage = Mockery::mock( '\GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage' );
		$mock_storage->shouldReceive( 'get_palette' )
			->once()
			->with( 999 )
			->andReturn( null );

		WP_Mock::userFunction(
			'GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage::__construct',
			array(
				'return' => $mock_storage,
			)
		);

		WP_Mock::userFunction(
			'__',
			array(
				'return' => 'Palette not found',
			)
		);

		$response = REST_API::get_palette( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 404, $response->get_error_data()['status'] );
	}

	/**
	 * Test successful saving of palette
	 *
	 * Ensures that a color palette is successfully saved and returned via
	 * the REST API.
	 *
	 * @covers ::save_palette
	 * @return void
	 */
	public function test_save_palette_success(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'name', 'Test Palette' );
		$request->set_param( 'colors', array( '#ff0000', '#00ff00' ) );

		$mock_storage = Mockery::mock( '\GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage' );
		$mock_storage->shouldReceive( 'save_palette' )
			->once()
			->with( 'Test Palette', array( '#ff0000', '#00ff00' ) )
			->andReturn( 1 );

		WP_Mock::userFunction(
			'GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage::__construct',
			array(
				'return' => $mock_storage,
			)
		);

		$response = REST_API::save_palette( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 201, $response->get_status() );
		$this->assertTrue( $response->get_data()['success'] );
		$this->assertEquals( 1, $response->get_data()['data']['id'] );
	}

	/**
	 * Test color validation
	 *
	 * Ensures that color validation is working correctly.
	 *
	 * @covers ::validate_color
	 * @return void
	 */
	public function test_validate_color(): void {
		$this->assertTrue( REST_API::validate_color( '#ff0000' ) );
		$this->assertTrue( REST_API::validate_color( '#fff' ) );
		$this->assertFalse( REST_API::validate_color( 'ff0000' ) );
		$this->assertFalse( REST_API::validate_color( '#ff00' ) );
		$this->assertFalse( REST_API::validate_color( '#ff000g' ) );
	}

	/**
	 * Test permission checks
	 *
	 * Ensures that permission checks are working correctly.
	 *
	 * @covers ::check_generate_permission
	 * @covers ::check_create_permission
	 * @covers ::check_update_permission
	 * @covers ::check_delete_permission
	 * @covers ::check_read_permission
	 * @return void
	 */
	public function test_check_permissions(): void {
		WP_Mock::userFunction(
			'current_user_can',
			array(
				'times'  => 4,
				'args'   => array( 'edit_posts' ),
				'return' => true,
			)
		);

		$this->assertTrue( REST_API::check_generate_permission() );
		$this->assertTrue( REST_API::check_create_permission() );
		$this->assertTrue( REST_API::check_update_permission() );
		$this->assertTrue( REST_API::check_delete_permission() );
		$this->assertTrue( REST_API::check_read_permission() ); // Always true
	}
}
