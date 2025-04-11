<?php
/**
 * Admin Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\AdminInterface;

class Test_Admin_Interface extends Unit_Test_Case {
	private $admin;

	public function setUp(): void {
		$this->admin = $this->createMock( AdminInterface::class );
	}

	public function test_render_settings_page_returns_string(): void {
		// Arrange
		$expected = '<div class="wrap"><h1>Color Palette Settings</h1>...</div>';

		$this->admin
			->expects( $this->once() )
			->method( 'render_settings_page' )
			->willReturn( $expected );

		// Act
		$result = $this->admin->render_settings_page();

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_settings_fields_returns_array(): void {
		// Arrange
		$expected = array(
			'api_key'              => array(
				'label'    => 'API Key',
				'type'     => 'password',
				'required' => true,
			),
			'default_palette_size' => array(
				'label' => 'Default Palette Size',
				'type'  => 'number',
				'min'   => 3,
				'max'   => 10,
			),
		);

		$this->admin
			->expects( $this->once() )
			->method( 'get_settings_fields' )
			->willReturn( $expected );

		// Act
		$result = $this->admin->get_settings_fields();

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'api_key', $result );
		$this->assertArrayHasKey( 'default_palette_size', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_validate_settings_returns_array(): void {
		// Arrange
		$input    = array(
			'api_key'              => 'test_key_123',
			'default_palette_size' => 5,
		);
		$expected = array(
			'api_key'              => 'test_key_123',
			'default_palette_size' => 5,
			'validated'            => true,
		);

		$this->admin
			->expects( $this->once() )
			->method( 'validate_settings' )
			->with( $input )
			->willReturn( $expected );

		// Act
		$result = $this->admin->validate_settings( $input );

		// Assert
		$this->assertIsArray( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_admin_notices_returns_array(): void {
		// Arrange
		$expected = array(
			array(
				'type'        => 'error',
				'message'     => 'API key is required',
				'dismissible' => true,
			),
			array(
				'type'        => 'success',
				'message'     => 'Settings saved successfully',
				'dismissible' => true,
			),
		);

		$this->admin
			->expects( $this->once() )
			->method( 'get_admin_notices' )
			->willReturn( $expected );

		// Act
		$result = $this->admin->get_admin_notices();

		// Assert
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_register_settings_returns_true_on_success(): void {
		// Arrange
		$this->admin
			->expects( $this->once() )
			->method( 'register_settings' )
			->willReturn( true );

		// Act
		$result = $this->admin->register_settings();

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider invalidSettingsProvider
	 */
	public function test_validate_settings_throws_exception_for_invalid_input( $input ): void {
		$this->admin
			->expects( $this->once() )
			->method( 'validate_settings' )
			->with( $input )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->admin->validate_settings( $input );
	}

	public function invalidSettingsProvider(): array {
		return array(
			'empty array'      => array( array() ),
			'missing required' => array( array( 'default_palette_size' => 5 ) ),
			'invalid type'     => array( array( 'api_key' => array( 'invalid' ) ) ),
			'non-array input'  => array( 'invalid' ),
			'null input'       => array( null ),
		);
	}
}
