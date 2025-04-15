<?php
/**
 * Tests for Color_Palette_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Color_Management
 * @author George Lerner
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Color_Management;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\AI\AI_Provider_Factory;
use GL_Color_Palette_Generator\AI\AI_Provider_Interface;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;
use Mockery;

/**
 * Class Test_Color_Palette_Generator
 *
 * @covers GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator
 */
class Test_Color_Palette_Generator extends WP_Mock_Test_Case implements Color_Constants {
	/**
	 * Test instance
	 *
	 * @var Color_Palette_Generator
	 */
	private $instance;

	/**
	 * Mock color utility
	 *
	 * @var Color_Utility|Mockery\MockInterface
	 */
	private $color_util_mock;

	/**
	 * Mock settings manager
	 *
	 * @var Settings_Manager|Mockery\MockInterface
	 */
	private $settings_mock;

	/**
	 * Mock AI provider
	 *
	 * @var AI_Provider_Interface|Mockery\MockInterface
	 */
	private $ai_provider_mock;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Create mocks
		$this->color_util_mock  = Mockery::mock( 'GL_Color_Palette_Generator\Color_Management\Color_Utility' );
		$this->settings_mock    = Mockery::mock( 'GL_Color_Palette_Generator\Settings\Settings_Manager' );
		$this->ai_provider_mock = Mockery::mock( 'GL_Color_Palette_Generator\AI\AI_Provider_Interface' );

		// Create instance
		$this->instance = new Color_Palette_Generator();
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
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#ff3333', '#ff6666', '#ff9999', '#ffcccc' );

		// Test monochromatic scheme
		$result = $this->instance->generate_scheme( $base_color, array( 'type' => 'monochromatic' ) );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result );

		// Test invalid scheme type
		$result = $this->instance->generate_scheme( $base_color, array( 'type' => 'invalid_type' ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'invalid_scheme_type', $result->get_error_code() );
	}

	/**
	 * Test generate_monochromatic method
	 */
	public function test_generate_monochromatic() {
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#ff3333', '#ff6666', '#ff9999', '#ffcccc' );

		$this->color_util_mock->shouldReceive( 'generate_monochromatic' )
			->once()
			->with( $base_color, 5 )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_monochromatic( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_analogous method
	 */
	public function test_generate_analogous() {
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#ff3300', '#ff6600', '#ff9900', '#ffcc00' );

		$this->color_util_mock->shouldReceive( 'generate_analogous' )
			->once()
			->with( $base_color, 5 )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_analogous( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_complementary method
	 */
	public function test_generate_complementary() {
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#00ffff', '#ff3333', '#33ffff' );

		$this->color_util_mock->shouldReceive( 'generate_complementary' )
			->once()
			->with( $base_color, 4 )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_complementary( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_split_complementary method
	 */
	public function test_generate_split_complementary() {
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#00ff66', '#0066ff' );

		$this->color_util_mock->shouldReceive( 'generate_split_complementary' )
			->once()
			->with( $base_color, 3 )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_split_complementary( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_triadic method
	 */
	public function test_generate_triadic() {
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#00ff00', '#0000ff' );

		$this->color_util_mock->shouldReceive( 'generate_triadic' )
			->once()
			->with( $base_color, 3 )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_triadic( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_tetradic method
	 */
	public function test_generate_tetradic() {
		$base_color      = '#ff0000';
		$expected_colors = array( '#ff0000', '#ffff00', '#00ff00', '#0000ff' );

		$this->color_util_mock->shouldReceive( 'generate_tetradic' )
			->once()
			->with( $base_color, 4 )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_tetradic( $base_color );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_custom_scheme method
	 */
	public function test_generate_custom_scheme() {
		$base_color      = '#ff0000';
		$rules           = array(
			'harmony'    => 'complementary',
			'saturation' => 'vibrant',
		);
		$expected_colors = array( '#ff0000', '#00ffff' );

		$this->color_util_mock->shouldReceive( 'generate_custom_scheme' )
			->once()
			->with( $base_color, $rules )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_custom_scheme( $base_color, $rules );
		$this->assertIsArray( $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_from_image method
	 */
	public function test_generate_from_image() {
		$image_path      = '/path/to/image.jpg';
		$options         = array( 'count' => 5 );
		$expected_colors = array( '#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff' );

		$this->color_util_mock->shouldReceive( 'extract_colors_from_image' )
			->once()
			->with( $image_path, $options )
			->andReturn( $expected_colors );

		$result = $this->instance->generate_from_image( $image_path, $options );
		$this->assertIsArray( $result );
		$this->assertCount( 5, $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test generate_themed_scheme method
	 */
	public function test_generate_themed_scheme() {
		$theme           = 'ocean';
		$options         = array( 'style' => 'modern' );
		$expected_colors = array( '#003366', '#0066cc', '#0099ff', '#66ccff', '#99ffff' );

		$this->instance->shouldReceive( 'generate_from_prompt' )
			->once()
			->andReturn( array( 'colors' => $expected_colors ) );

		$result = $this->instance->generate_themed_scheme( $theme, $options );
		$this->assertIsArray( $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test adjust_scheme_contrast method
	 */
	public function test_adjust_scheme_contrast() {
		$colors          = array( '#ff0000', '#00ff00', '#0000ff' );
		$options         = array( 'min_contrast' => 4.5 );
		$expected_colors = array( '#ff0000', '#00dd00', '#0000dd' );

		$this->color_util_mock->shouldReceive( 'adjust_contrast' )
			->once()
			->with( $colors, $options )
			->andReturn( $expected_colors );

		$result = $this->instance->adjust_scheme_contrast( $colors, $options );
		$this->assertIsArray( $result );
		$this->assertEquals( $expected_colors, $result );
	}

	/**
	 * Test get_available_schemes method
	 */
	public function test_get_available_schemes() {
		$result = $this->instance->get_available_schemes();
		$this->assertIsArray( $result );
		$this->assertContains( 'monochromatic', $result );
		$this->assertContains( 'analogous', $result );
		$this->assertContains( 'complementary', $result );
		$this->assertContains( 'split_complementary', $result );
		$this->assertContains( 'triadic', $result );
		$this->assertContains( 'tetradic', $result );
	}

	/**
	 * Test get_color_theory_rules method
	 */
	public function test_get_color_theory_rules() {
		$result = $this->instance->get_color_theory_rules();
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'harmony', $result );
		$this->assertArrayHasKey( 'contrast', $result );
		$this->assertArrayHasKey( 'saturation', $result );
		$this->assertArrayHasKey( 'brightness', $result );
	}

	/**
	 * Test validate_scheme method
	 */
	public function test_validate_scheme() {
		$base_colors  = array( '#ff0000', '#00ff00', '#0000ff' );
		$found_colors = array();

		foreach ( self::REQUIRED_ROLES['triadic'] as $role ) {
			$found_colors[ $role ] = true;
		}

		$this->color_util_mock->shouldReceive( 'validate_colors' )
			->once()
			->with( $base_colors )
			->andReturn( true );

		$result = $this->instance->validate_scheme( $base_colors, 'triadic', $found_colors );
		$this->assertTrue( $result );
	}

	/**
	 * Test generate_from_prompt method with valid input
	 */
	public function test_generate_from_prompt_with_valid_input() {
		$context = array(
			'business_type'   => 'Modern tech startup focused on AI solutions',
			'target_audience' => 'Tech-savvy professionals aged 25-45',
			'desired_mood'    => 'Professional, innovative, and trustworthy',
		);

		$expected_colors      = array( '#336699', '#66CC99', '#FF9966', '#9966CC' );
		$expected_description = array(
			'palette_story' => 'Test story',
			'colors'        => $expected_colors,
		);

		$this->ai_provider_mock->shouldReceive( 'generate_palette' )
			->once()
			->with( $context )
			->andReturn( $expected_colors );

		$this->settings_mock->shouldReceive( 'save_palette_description' )
			->once()
			->with( $expected_description );

		$result = $this->instance->generate_from_prompt( $context );
		$this->assertIsArray( $result );
		$this->assertEquals( $expected_colors, $result['colors'] );
	}

		/**
		 * Test verify_wcag_compliance method
		 */
	public function test_verify_wcag_compliance() {
		$colors = array(
			self::COLOR_PRIMARY_BLUE[0],
			self::COLOR_PRIMARY_RED[0],
			self::COLOR_PRIMARY_GREEN[0],
			self::COLOR_PRIMARY_YELLOW[0],
		);

		$this->color_util_mock->shouldReceive( 'get_contrast_ratio' )
			->times( 4 )
			->andReturn( 5.0 );  // Return a valid contrast ratio above WCAG minimum

		$result = $this->instance->verify_wcag_compliance( $colors );
		$this->assertTrue( $result );
	}

	/**
	 * Test adjust_for_wcag_compliance method
	 */
	public function test_adjust_for_wcag_compliance() {
		$colors = array(
			'#000000',    // Pure black
			'#FFFFFF',    // Pure white
			'#808080',    // Mid gray
			'#FFD700',     // Gold
		);

		$this->color_util_mock->shouldReceive( 'get_contrast_ratio' )
			->times( 8 )  // Each color tested against both light and dark backgrounds
			->andReturn( 4.0, 12.0 );  // Alternating contrast ratios

		$this->color_util_mock->shouldReceive( 'adjust_color_for_contrast' )
			->times( 2 )  // Only colors with insufficient contrast need adjustment
			->andReturn( '#606060', '#FFB700' );

		$result = $this->instance->adjust_for_wcag_compliance( $colors );
		$this->assertIsArray( $result );
		$this->assertCount( 4, $result );
	}
}
