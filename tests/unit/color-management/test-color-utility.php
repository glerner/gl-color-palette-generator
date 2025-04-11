<?php
/**
 * Test Color Utility Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Color_Management
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;

/**
 * Class Test_Color_Utility
 */
class Test_Color_Utility extends Unit_Test_Case implements Color_Constants {
	/**
	 * Color utility instance
	 *
	 * @var Color_Utility
	 */
	private $color_util;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->color_util = new Color_Utility();
	}

	/**
	 * Test color difference calculation for similar colors
	 */
	public function test_similar_colors_have_small_difference() {
		$color1 = '#ff0000';  // Red
		$color2 = '#ff0505';  // Very slightly different red

		$difference = $this->color_util->get_color_difference( $color1, $color2 );

		$this->assertLessThan(
			10,
			$difference,
			'Similar colors should have a small difference value'
		);
	}

	/**
	 * Test color difference calculation for distinct colors
	 */
	public function test_distinct_colors_have_large_difference() {
		$color1 = '#ff0000';  // Red
		$color2 = '#00ff00';  // Green

		$difference = $this->color_util->get_color_difference( $color1, $color2 );

		$this->assertGreaterThan(
			50,
			$difference,
			'Distinct colors should have a large difference value'
		);
	}

	/**
	 * Test color distinctiveness check for similar colors
	 */
	public function test_similar_colors_are_not_distinct() {
		$colors = array(
			'#ff0000',  // Red
			'#ff0505',  // Very similar red
			'#ff0a0a',  // Similar red
			'#ff0f0f',  // Similar red
			'#ff1414',  // Similar red
		);

		$this->assertFalse(
			$this->color_util->are_colors_distinct( $colors ),
			'Similar colors should not be considered distinct'
		);
	}

	/**
	 * Test color distinctiveness check for distinct colors
	 */
	public function test_distinct_colors_are_distinct() {
		$colors = array(
			'#ff0000',  // Red
			'#00ff00',  // Green
			'#0000ff',  // Blue
			'#ffff00',  // Yellow
			'#ff00ff',  // Magenta
		);

		$this->assertTrue(
			$this->color_util->are_colors_distinct( $colors ),
			'Different colors should be considered distinct'
		);
	}

	/**
	 * Test distinctiveness score for similar colors
	 */
	public function test_similar_colors_have_low_distinctiveness_score() {
		$colors = array(
			'#ff0000',
			'#ff0505',
			'#ff0a0a',
			'#ff0f0f',
			'#ff1414',
		);

		$score = $this->color_util->get_distinctiveness_score( $colors );

		$this->assertLessThan(
			30,
			$score,
			'Similar colors should have a low distinctiveness score'
		);
	}

	/**
	 * Test distinctiveness score for distinct colors
	 */
	public function test_distinct_colors_have_high_distinctiveness_score() {
		$colors = array(
			'#ff0000',  // Red
			'#00ff00',  // Green
			'#0000ff',  // Blue
			'#ffff00',  // Yellow
			'#ff00ff',  // Magenta
		);

		$score = $this->color_util->get_distinctiveness_score( $colors );

		$this->assertGreaterThan(
			70,
			$score,
			'Different colors should have a high distinctiveness score'
		);
	}

	/**
	 * Test hex to Lab conversion maintains consistency
	 */
	public function test_hex_to_lab_conversion_consistency() {
		$reflection = new \ReflectionClass( $this->color_util );
		$method     = $reflection->getMethod( 'hex_to_lab' );
		$method->setAccessible( true );

		$color = '#ff0000';
		$lab1  = $method->invoke( $this->color_util, $color );
		$lab2  = $method->invoke( $this->color_util, $color );

		$this->assertEquals(
			$lab1,
			$lab2,
			'Same color should convert to same Lab values'
		);
	}

	/**
	 * Test color difference is symmetric
	 */
	public function test_color_difference_is_symmetric() {
		$color1 = '#ff0000';
		$color2 = '#00ff00';

		$diff1 = $this->color_util->get_color_difference( $color1, $color2 );
		$diff2 = $this->color_util->get_color_difference( $color2, $color1 );

		$this->assertEquals(
			$diff1,
			$diff2,
			'Color difference should be the same regardless of order'
		);
	}

	/**
	 * Test WCAG 2.1 AA contrast ratio requirements
	 * Tests both passing and failing contrast combinations
	 */
	public function test_wcag_aa_contrast_requirements() {
		$test_cases = array(
			// Should pass AA requirements
			array(
				'bg'          => self::COLOR_WHITE,
				'text'        => self::COLOR_NEAR_BLACK,
				'should_pass' => true,
				'description' => 'White background with near-black text',
			),
			array(
				'bg'          => self::COLOR_OFF_WHITE,
				'text'        => self::COLOR_DARK_GRAY,
				'should_pass' => true,
				'description' => 'Off-white background with dark gray text',
			),
			// Should fail AA requirements
			array(
				'bg'          => self::COLOR_WHITE,
				'text'        => self::COLOR_LIGHT_GRAY,
				'should_pass' => false,
				'description' => 'White background with light gray text',
			),
			array(
				'bg'          => self::COLOR_OFF_WHITE,
				'text'        => self::COLOR_MID_GRAY,
				'should_pass' => false,
				'description' => 'Off-white background with mid gray text',
			),
		);

		foreach ( $test_cases as $case ) {
			$contrast = $this->color_util->get_contrast_ratio( $case['bg'], $case['text'] );

			if ( $case['should_pass'] ) {
				$this->assertGreaterThanOrEqual(
					self::WCAG_CONTRAST_MIN,
					$contrast,
					sprintf(
						'%s should meet WCAG AA contrast requirements. Got ratio: %.2f:1',
						$case['description'],
						$contrast
					)
				);
			} else {
				$this->assertLessThan(
					self::WCAG_CONTRAST_MIN,
					$contrast,
					sprintf(
						'%s should not meet WCAG AA contrast requirements. Got ratio: %.2f:1',
						$case['description'],
						$contrast
					)
				);
			}
		}
	}

	/**
	 * Test WCAG 2.1 AAA contrast ratio requirements
	 * Tests both passing and failing contrast combinations
	 */
	public function test_wcag_aaa_contrast_requirements() {
		$test_cases = array(
			// Should pass AAA requirements
			array(
				'bg'          => self::COLOR_WHITE,
				'text'        => self::COLOR_NEAR_BLACK,
				'should_pass' => true,
				'description' => 'White background with near-black text',
			),
			array(
				'bg'          => self::COLOR_NEAR_BLACK,
				'text'        => self::COLOR_WHITE,
				'should_pass' => true,
				'description' => 'Near-black background with white text',
			),
			// Should fail AAA requirements but pass AA
			array(
				'bg'          => self::COLOR_OFF_WHITE,
				'text'        => self::COLOR_DARK_GRAY,
				'should_pass' => false,
				'description' => 'Off-white background with dark gray text',
			),
			array(
				'bg'          => self::COLOR_DARK_GRAY,
				'text'        => self::COLOR_OFF_WHITE,
				'should_pass' => false,
				'description' => 'Dark gray background with off-white text',
			),
		);

		foreach ( $test_cases as $case ) {
			$contrast = $this->color_util->get_contrast_ratio( $case['bg'], $case['text'] );

			if ( $case['should_pass'] ) {
				$this->assertGreaterThanOrEqual(
					self::WCAG_CONTRAST_TARGET,
					$contrast,
					sprintf(
						'%s should meet WCAG AAA contrast targets. Got ratio: %.2f:1',
						$case['description'],
						$contrast
					)
				);
				$this->assertLessThanOrEqual(
					self::CONTRAST_MAX,
					$contrast,
					sprintf(
						'%s should not exceed maximum contrast for visual comfort. Got ratio: %.2f:1',
						$case['description'],
						$contrast
					)
				);
			} else {
				$this->assertLessThan(
					self::WCAG_CONTRAST_TARGET,
					$contrast,
					sprintf(
						'%s should not meet WCAG AAA contrast targets. Got ratio: %.2f:1',
						$case['description'],
						$contrast
					)
				);
				$this->assertGreaterThanOrEqual(
					self::WCAG_CONTRAST_MIN,
					$contrast,
					sprintf(
						'%s should still meet WCAG AA minimum requirements. Got ratio: %.2f:1',
						$case['description'],
						$contrast
					)
				);
			}
		}
	}

	/**
	 * Test color scheme validation using COLOR_ROLES
	 */
	public function test_color_scheme_validation_with_roles() {
		$valid_scheme = array(
			self::COLOR_ROLES['primary']   => '#1E40AF',
			self::COLOR_ROLES['secondary'] => '#15803D',
			self::COLOR_ROLES['tertiary']  => '#B91C1C',
			self::COLOR_ROLES['accent']    => '#9333EA',
			self::COLOR_ROLES['base']      => '#FFFFFF',
			self::COLOR_ROLES['contrast']  => '#000000',
		);

		$this->assertTrue(
			$this->color_util->are_colors_distinct( array_values( $valid_scheme ) ),
			'Colors in valid scheme should be distinct'
		);

		$score = $this->color_util->get_distinctiveness_score( array_values( $valid_scheme ) );
		$this->assertGreaterThan(
			50,
			$score,
			'Valid color scheme should have high distinctiveness score'
		);
	}

	/**
	 * Test color accessibility for edge cases
	 */
	public function test_color_accessibility_edge_cases() {
		// Test nearly identical colors
		$color1 = '#000000';  // Black
		$color2 = '#010101';  // Nearly black

		$this->assertFalse(
			$this->color_util->are_colors_distinct( array( $color1, $color2 ) ),
			'Nearly identical colors should not be considered distinct'
		);

		// Test colors with same luminance but different hues
		$color3 = '#7F7F00';  // Olive
		$color4 = '#007F7F';  // Teal

		$this->assertTrue(
			$this->color_util->are_colors_distinct( array( $color3, $color4 ) ),
			'Colors with same luminance but different hues should be distinct'
		);

		// Test colors with minimal saturation difference
		$color5 = '#7F7F7F';  // Gray
		$color6 = '#808080';  // Slightly different gray

		$this->assertFalse(
			$this->color_util->are_colors_distinct( array( $color5, $color6 ) ),
			'Colors with minimal saturation difference should not be distinct'
		);
	}

	/**
	 * Test relative luminance calculation
	 */
	public function test_relative_luminance() {
		$colors = array(
			'#FFFFFF' => 1.0,      // White should have maximum luminance
			'#000000' => 0.0,      // Black should have minimum luminance
			'#808080' => 0.216,    // Mid-gray should have mid-range luminance (approximately)
			'#FF0000' => 0.2126,   // Red should match sRGB coefficient
			'#00FF00' => 0.7152,   // Green should match sRGB coefficient
			'#0000FF' => 0.0722,   // Blue should match sRGB coefficient
		);

		foreach ( $colors as $color => $expected ) {
			$luminance = $this->color_util->get_relative_luminance( $color );
			$this->assertEqualsWithDelta(
				$expected,
				$luminance,
				0.01,
				sprintf( 'Color %s should have luminance close to %f', $color, $expected )
			);
		}
	}

	/**
	 * Test batch color space conversion
	 */
	public function test_convert_colors() {
		$colors = array(
			'red'   => '#ff0000',
			'green' => '#00ff00',
			'blue'  => '#0000ff',
		);

		// Test hex to RGB conversion
		$result = $this->color_util->convert_colors( $colors, 'hex', 'rgb' );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertArrayHasKey( 'red', $result['converted'] );
		$this->assertEquals(
			array(
				'r' => 255,
				'g' => 0,
				'b' => 0,
			),
			$result['converted']['red']
		);

		// Test hex to HSL conversion
		$result = $this->color_util->convert_colors( $colors, 'hex', 'hsl' );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertArrayHasKey( 'red', $result['converted'] );
		$this->assertEquals(
			0, // Red hue
			$result['converted']['red']['h']
		);

		// Test precision option
		$result = $this->color_util->convert_colors( $colors, 'hex', 'hsl', array( 'precision' => 0 ) );
		$this->assertEquals(
			1, // 100% saturation rounded to 1
			$result['converted']['red']['s']
		);
	}

	/**
	 * Test color format conversion
	 */
	public function test_format_colors() {
		$colors = array(
			'primary'   => '#ff0000',
			'secondary' => '#00ff00',
		);

		// Test CSS format
		$result = $this->color_util->format_colors( $colors, 'css' );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertEquals(
			'#ff0000',
			$result['converted']['primary']
		);

		// Test SCSS format
		$result = $this->color_util->format_colors( $colors, 'scss' );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertEquals(
			'$primary: #ff0000;',
			$result['converted']['primary']
		);

		// Test JSON format
		$result = $this->color_util->format_colors( $colors, 'json' );
		$this->assertArrayHasKey( 'converted', $result );
		$this->assertEquals(
			array(
				'r' => 255,
				'g' => 0,
				'b' => 0,
			),
			$result['converted']['primary']
		);
	}

	/**
	 * Test invalid color space conversion
	 */
	public function test_convert_colors_invalid_space() {
		$this->expectException( \InvalidArgumentException::class );
		$this->color_util->convert_colors( array( 'test' => '#ff0000' ), 'invalid', 'rgb' );
	}

	/**
	 * Test invalid format conversion
	 */
	public function test_format_colors_invalid_format() {
		$this->expectException( \InvalidArgumentException::class );
		$this->color_util->format_colors( array( 'test' => '#ff0000' ), 'invalid' );
	}

	/**
	 * Tests from Color_Converter interface
	 */
	public function test_convert_rgb_to_hex_interface(): void {
		$rgb    = array(
			'r' => 255,
			'g' => 0,
			'b' => 0,
		);
		$result = $this->color_util->convert_rgb_to_hex( $rgb );

		$this->assertIsString( $result );
		$this->assertMatchesRegularExpression( '/^#[A-F0-9]{6}$/', $result );
		$this->assertEquals( '#FF0000', $result );
	}

	public function test_convert_hex_to_rgb_interface(): void {
		$hex    = '#FF0000';
		$result = $this->color_util->convert_hex_to_rgb( $hex );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'r', $result );
		$this->assertArrayHasKey( 'g', $result );
		$this->assertArrayHasKey( 'b', $result );
		$this->assertEquals(
			array(
				'r' => 255,
				'g' => 0,
				'b' => 0,
			),
			$result
		);
	}

	public function test_convert_rgb_to_hsl_interface(): void {
		$rgb    = array(
			'r' => 255,
			'g' => 0,
			'b' => 0,
		);
		$result = $this->color_util->convert_rgb_to_hsl( $rgb );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'h', $result );
		$this->assertArrayHasKey( 's', $result );
		$this->assertArrayHasKey( 'l', $result );

		// Pure red should be hue 0, full saturation, 50% lightness
		$this->assertEquals( 0, $result['h'] );
		$this->assertEquals( 100, $result['s'] );
		$this->assertEquals( 50, $result['l'] );
	}

	public function test_convert_hsl_to_rgb_interface(): void {
		$hsl    = array(
			'h' => 0,
			's' => 100,
			'l' => 50,
		);
		$result = $this->color_util->convert_hsl_to_rgb( $hsl );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'r', $result );
		$this->assertArrayHasKey( 'g', $result );
		$this->assertArrayHasKey( 'b', $result );

		// Pure red in HSL should convert back to RGB red
		$this->assertEquals(
			array(
				'r' => 255,
				'g' => 0,
				'b' => 0,
			),
			$result
		);
	}

	public function test_convert_rgb_to_hsv_interface(): void {
		$rgb    = array(
			'r' => 255,
			'g' => 0,
			'b' => 0,
		);
		$result = $this->color_util->convert_rgb_to_hsv( $rgb );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'h', $result );
		$this->assertArrayHasKey( 's', $result );
		$this->assertArrayHasKey( 'v', $result );

		// Pure red should be hue 0, full saturation, full value
		$this->assertEquals( 0, $result['h'] );
		$this->assertEquals( 100, $result['s'] );
		$this->assertEquals( 100, $result['v'] );
	}

	public function test_convert_hsv_to_rgb_interface(): void {
		$hsv    = array(
			'h' => 0,
			's' => 100,
			'v' => 100,
		);
		$result = $this->color_util->convert_hsv_to_rgb( $hsv );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'r', $result );
		$this->assertArrayHasKey( 'g', $result );
		$this->assertArrayHasKey( 'b', $result );

		// Pure red in HSV should convert back to RGB red
		$this->assertEquals(
			array(
				'r' => 255,
				'g' => 0,
				'b' => 0,
			),
			$result
		);
	}
}
