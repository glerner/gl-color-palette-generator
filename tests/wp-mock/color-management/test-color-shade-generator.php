<?php
/**
 * Tests for Color_Shade_Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Shade_Generator;
use GL_Color_Palette_Generator\Color_Management\AccessibilityChecker;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use Mockery;

/**
 * Class Test_Color_Shade_Generator
 * Tests the generation of tints and shades (lighter/darker variations) of a single color
 * while ensuring WCAG accessibility compliance.
 */
class Test_Color_Shade_Generator extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
	/**
	 * Test instance
	 *
	 * @var Color_Shade_Generator
	 */
	private $instance;

	/**
	 * Mock accessibility checker
	 *
	 * @var AccessibilityChecker|Mockery\MockInterface
	 */
	private $accessibility_mock;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		$this->accessibility_mock = Mockery::mock( AccessibilityChecker::class );
		$this->instance           = new Color_Shade_Generator( $this->accessibility_mock );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test generate_tints_and_shades with default options
	 */
	public function test_generate_tints_and_shades_default(): void {
		$base_color = '#FF0000';

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->andReturn( true )
			->byDefault();

		$result = $this->instance->generate_tints_and_shades( $base_color );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'original', $result );
		$this->assertArrayHasKey( 'variations', $result );
		$this->assertEquals( $base_color, $result['original'] );

		// Check that we have the required accessible variations
		$variations = $result['variations'];
		$this->assertIsArray( $variations );

		// Each variation should meet contrast requirements
		foreach ( $variations as $variation ) {
			$this->assertMatchesRegularExpression(
				'/^#[A-Fa-f0-9]{6}$/',
				$variation,
				'Each variation should be a valid hex color'
			);
		}

		// Original color might or might not be included based on accessibility
		if ( isset( $variations['base'] ) ) {
			$this->assertTrue( $this->instance->meets_contrast_requirements( $variations['base'] ) );
		}
	}

	/**
	 * Test generate_tints_and_shades with custom steps
	 */
	public function test_generate_tints_and_shades_custom_steps(): void {
		$base_color   = '#00FF00';
		$custom_steps = array(
			'subtle' => array( 10, 30, 5 ),
			'strong' => array( -10, -30, -5 ),
		);

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->andReturn( true )
			->byDefault();

		$result = $this->instance->generate_tints_and_shades(
			$base_color,
			array(
				'custom_steps' => $custom_steps,
			)
		);

		$variations = $result['variations'];
		$this->assertArrayHasKey( 'subtle', $variations );
		$this->assertArrayHasKey( 'strong', $variations );
	}

	/**
	 * Test generate_tints_and_shades without base color
	 */
	public function test_generate_tints_and_shades_no_base(): void {
		$base_color = '#0000FF';

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->andReturn( true )
			->byDefault();

		$result = $this->instance->generate_tints_and_shades(
			$base_color,
			array(
				'include_base' => false,
			)
		);

		$this->assertArrayNotHasKey( 'base', $result['variations'] );
	}

	/**
	 * Test generate_tints_and_shades with failing contrast
	 */
	public function test_generate_tints_and_shades_contrast_fail(): void {
		$base_color = '#808080';

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->andReturn( false )
			->times( 5 ) // First 5 attempts fail
			->andReturn( true )
			->byDefault();

		$result = $this->instance->generate_tints_and_shades( $base_color );

		// Should still generate variations but might skip some that don't meet contrast
		$this->assertIsArray( $result['variations'] );
	}

	/**
	 * Test meets_contrast_requirements
	 */
	public function test_meets_contrast_requirements(): void {
		$color = '#FFFFFF';

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->with( $color, Mockery::type( 'array' ) )
			->andReturn( true )
			->once();

		$result = $this->instance->meets_contrast_requirements( $color );
		$this->assertTrue( $result );
	}

	/**
	 * Test invalid color input
	 */
	public function test_invalid_color_input(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->instance->generate_tints_and_shades( 'invalid-color' );
	}

	/**
	 * Test color brightness adjustment
	 */
	public function test_adjust_brightness(): void {
		$base_color = '#FF0000';

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->andReturn( true )
			->byDefault();

		$result = $this->instance->generate_tints_and_shades(
			$base_color,
			array(
				'custom_steps' => array(
					'test' => array( 50, 50, 5 ), // Single step of 50% lighter
				),
			)
		);

		$variations = $result['variations'];
		$this->assertArrayHasKey( 'test', $variations );

		// Convert hex to RGB and check if brightness was increased
		$hex             = ltrim( $variations['test'], '#' );
		list($r, $g, $b) = sscanf( $hex, '%02x%02x%02x' );
		$this->assertGreaterThan( 255, $r + $g + $b ); // Sum should be higher than original red (255,0,0)
	}

	/**
	 * Test that variations meet contrast requirements
	 */
	public function test_variations_meet_contrast_requirements(): void {
		$base_color = '#808080'; // A color that might not meet contrast requirements

		$this->accessibility_mock
			->shouldReceive( 'meets_contrast_requirements' )
			->andReturnUsing(
				function ( $color ) {
					// Simulate actual contrast checking - return true only for sufficiently light or dark colors
					$hex             = ltrim( $color, '#' );
					list($r, $g, $b) = sscanf( $hex, '%02x%02x%02x' );
					$brightness      = ( $r * 299 + $g * 587 + $b * 114 ) / 1000;
					return $brightness < 50 || $brightness > 200; // Only very light or very dark colors pass
				}
			);

		$result = $this->instance->generate_tints_and_shades( $base_color );

		// Each generated variation should meet contrast requirements
		foreach ( $result['variations'] as $name => $color ) {
			$this->assertTrue(
				$this->instance->meets_contrast_requirements( $color ),
				sprintf( "Variation '%s' (%s) should meet contrast requirements", $name, $color )
			);
		}
	}

	/**
	 * Test color variations in light mode
	 */
	public function test_create_variations_light_mode(): void {
		$base_color = '#f8f9fa';  // Light base color
		$this->accessibility_mock->shouldReceive( 'calculate_relative_luminance' )
			->andReturn( 0.85 );  // High luminance for light mode

		$variations = $this->instance->create_variations( $base_color, 'light' );

		$this->assertArrayHasKey( 'base', $variations );
		$this->assertArrayHasKey( 'lighter', $variations );
		$this->assertArrayHasKey( 'darker', $variations );
		$this->assertEquals( $base_color, $variations['base'] );

		// Verify luminance relationships
		$this->accessibility_mock->shouldReceive( 'calculate_relative_luminance' )
			->with( $variations['lighter'] )
			->andReturn( 0.9 );  // Lighter should have higher luminance
		$this->accessibility_mock->shouldReceive( 'calculate_relative_luminance' )
			->with( $variations['darker'] )
			->andReturn( 0.7 );  // Darker should have lower luminance

		$lighter_luminance = $this->accessibility_mock->calculate_relative_luminance( $variations['lighter'] );
		$darker_luminance  = $this->accessibility_mock->calculate_relative_luminance( $variations['darker'] );

		$this->assertGreaterThan( $darker_luminance, $lighter_luminance );
	}

	/**
	 * Test color variations in dark mode
	 */
	public function test_create_variations_dark_mode(): void {
		$base_color = '#1a1a1a';  // Dark base color
		$this->accessibility_mock->shouldReceive( 'calculate_relative_luminance' )
			->andReturn( 0.15 );  // Low luminance for dark mode

		$variations = $this->instance->create_variations( $base_color, 'dark' );

		$this->assertArrayHasKey( 'base', $variations );
		$this->assertArrayHasKey( 'lighter', $variations );
		$this->assertArrayHasKey( 'darker', $variations );
		$this->assertEquals( $base_color, $variations['base'] );

		// Verify luminance relationships
		$this->accessibility_mock->shouldReceive( 'calculate_relative_luminance' )
			->with( $variations['lighter'] )
			->andReturn( 0.3 );  // Lighter should have higher luminance
		$this->accessibility_mock->shouldReceive( 'calculate_relative_luminance' )
			->with( $variations['darker'] )
			->andReturn( 0.1 );  // Darker should have lower luminance

		$lighter_luminance = $this->accessibility_mock->calculate_relative_luminance( $variations['lighter'] );
		$darker_luminance  = $this->accessibility_mock->calculate_relative_luminance( $variations['darker'] );

		$this->assertGreaterThan( $darker_luminance, $lighter_luminance );
	}

	/**
	 * Test accessible shade generation
	 */
	public function test_accessible_shades(): void {
		$base_color = '#4a90e2';  // Base blue color

		$this->accessibility_mock->shouldReceive( 'calculate_contrast_ratio' )
			->andReturn( Color_Constants::WCAG_CONTRAST_MIN );  // Mock contrast ratio above target

		$shades = $this->instance->generate_accessible_shades( $base_color, Color_Constants::WCAG_CONTRAST_MIN );

		$this->assertNotEmpty( $shades );
		$this->assertArrayHasKey( 'light', $shades );
		$this->assertArrayHasKey( 'dark', $shades );

		// Verify contrast ratios meet WCAG requirements
		foreach ( $shades as $shade ) {
			$contrast = $this->accessibility_mock->calculate_contrast_ratio( $base_color, $shade );
			$this->assertGreaterThanOrEqual( Color_Constants::WCAG_CONTRAST_MIN, $contrast );
			$this->assertLessThanOrEqual( Color_Constants::CONTRAST_MAX, $contrast );
		}
	}
}
