<?php
/**
 * Tests for Color_Scheme_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Color_Management
 * @author George Lerner
 * @since 1.0.0
 *
 * @covers GL_Color_Palette_Generator\Color_Management\Color_Scheme_Generator
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Color_Management;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Scheme_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;
use Mockery;

/**
 * Class Test_Color_Scheme_Generator
 */
class Test_Color_Scheme_Generator extends WP_Mock_Test_Case implements Color_Constants {
	/**
	 * Test instance
	 *
	 * @var Color_Scheme_Generator
	 */
	private $instance;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->instance = new Color_Scheme_Generator();
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test generate_scheme method
	 */
	public function test_generate_scheme() {
		$base_color     = '#ff0000';
		$expected_roles = array_keys( Color_Constants::COLOR_ROLES );

		// Test default options
		$result = $this->instance->generate_scheme( $base_color );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );

		foreach ( $expected_roles as $role ) {
			$this->assertArrayHasKey( $role, $result );
			$this->assertMatchesRegularExpression( '/^#[0-9a-f]{6}$/i', $result[ $role ] );
		}

		// Test with specific scheme type
		$result = $this->instance->generate_scheme(
			$base_color,
			array(
				'type'  => 'complementary',
				'count' => 4,
			)
		);
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );

		// Test invalid color
		$result = $this->instance->generate_scheme( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );

		// Test invalid scheme type
		$result = $this->instance->generate_scheme( $base_color, array( 'type' => 'invalid' ) );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_monochromatic method
	 */
	public function test_generate_monochromatic() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_monochromatic( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result );

		// Test custom count
		$result = $this->instance->generate_monochromatic( $base_color, 3 );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test invalid color
		$result = $this->instance->generate_monochromatic( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_analogous method
	 */
	public function test_generate_analogous() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_analogous( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result );

		// Test custom count
		$result = $this->instance->generate_analogous( $base_color, 3 );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test invalid color
		$result = $this->instance->generate_analogous( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_complementary method
	 */
	public function test_generate_complementary() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_complementary( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );

		// Test custom count
		$result = $this->instance->generate_complementary( $base_color, 2 );
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );

		// Test invalid color
		$result = $this->instance->generate_complementary( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_split_complementary method
	 */
	public function test_generate_split_complementary() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_split_complementary( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test custom count
		$result = $this->instance->generate_split_complementary( $base_color, 2 );
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );

		// Test invalid color
		$result = $this->instance->generate_split_complementary( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_triadic method
	 */
	public function test_generate_triadic() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_triadic( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test custom count
		$result = $this->instance->generate_triadic( $base_color, 2 );
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );

		// Test invalid color
		$result = $this->instance->generate_triadic( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_tetradic method
	 */
	public function test_generate_tetradic() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_tetradic( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );

		// Test custom count
		$result = $this->instance->generate_tetradic( $base_color, 2 );
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );

		// Test invalid color
		$result = $this->instance->generate_tetradic( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_square method
	 */
	public function test_generate_square() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_square( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );

		// Test custom count
		$result = $this->instance->generate_square( $base_color, 2 );
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );

		// Test invalid color
		$result = $this->instance->generate_square( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_compound method
	 */
	public function test_generate_compound() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_compound( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test custom count
		$result = $this->instance->generate_compound( $base_color, 2 );
		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );

		// Test invalid color
		$result = $this->instance->generate_compound( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_monochromatic_accent method
	 */
	public function test_generate_monochromatic_accent() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_monochromatic_accent( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( self::MONOCHROMATIC_ACCENT_COLORS, $result );

		// Test invalid color
		$result = $this->instance->generate_monochromatic_accent( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_dual_tone method
	 */
	public function test_generate_dual_tone() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_dual_tone( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( self::DUAL_TONE_COLORS, $result );

		// Test invalid color
		$result = $this->instance->generate_dual_tone( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_neutral_pop method
	 */
	public function test_generate_neutral_pop() {
		$base_color = '#ff0000';

		// Test default count
		$result = $this->instance->generate_neutral_pop( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( self::NEUTRAL_POP_COLORS, $result );

		// Test invalid color
		$result = $this->instance->generate_neutral_pop( 'invalid' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}


	/**
	 * Test generate_custom_scheme method
	 */
	public function test_generate_custom_scheme() {
		$base_color = '#ff0000';
		$rules      = array(
			array(
				'type'  => 'hue_shift',
				'value' => 30,
			),
			array(
				'type'  => 'saturation_shift',
				'value' => -20,
			),
			array(
				'type'  => 'value_shift',
				'value' => 10,
			),
		);

		// Test valid rules
		$result = $this->instance->generate_custom_scheme( $base_color, $rules );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result ); // Base color + 3 rules

		// Test empty rules
		$result = $this->instance->generate_custom_scheme( $base_color, array() );
		$this->assertInstanceOf( WP_Error::class, $result );

		// Test invalid color
		$result = $this->instance->generate_custom_scheme( 'invalid', $rules );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate_from_image method
	 */
	public function test_generate_from_image() {
		// Create a test image
		$image_path = sys_get_temp_dir() . '/test_image.png';
		$image      = imagecreatetruecolor( 100, 100 );
		imagefilledrectangle( $image, 0, 0, 50, 100, imagecolorallocate( $image, 255, 0, 0 ) );
		imagefilledrectangle( $image, 51, 0, 100, 100, imagecolorallocate( $image, 0, 0, 255 ) );
		imagepng( $image, $image_path );
		imagedestroy( $image );

		// Test with valid image
		$result = $this->instance->generate_from_image( $image_path );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result ); // Default count

		// Test with custom count
		$result = $this->instance->generate_from_image( $image_path, array( 'count' => 3 ) );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test with invalid path
		$result = $this->instance->generate_from_image( 'invalid/path' );
		$this->assertInstanceOf( WP_Error::class, $result );

		// Clean up
		unlink( $image_path );
	}

	/**
	 * Test generate_theme_scheme method
	 */
	public function test_generate_theme_scheme() {
		// Test valid themes
		$themes = array( 'warm', 'cool', 'natural', 'elegant', 'vibrant', 'pastel' );
		foreach ( $themes as $theme ) {
			$result = $this->instance->generate_theme_scheme( $theme );
			$this->assertIsArray( $result );
			$this->assertCount( 5, $result ); // Default count
		}

		// Test with custom count
		$result = $this->instance->generate_theme_scheme( 'warm', array( 'count' => 3 ) );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );

		// Test with custom type
		$result = $this->instance->generate_theme_scheme( 'cool', array( 'type' => 'complementary' ) );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );

		// Test invalid theme
		$result = $this->instance->generate_theme_scheme( 'invalid_theme' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}
}
