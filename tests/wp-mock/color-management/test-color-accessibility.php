<?php
/**
 * Tests for Color Accessibility
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Accessibility\AccessibilityChecker;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;
use Mockery;

/**
 * Class Test_Color_Accessibility
 */
class Test_Color_Accessibility extends GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case {
	/**
	 * Test instance
	 *
	 * @var AccessibilityChecker
	 */
	private $accessibility_checker;

	/**
	 * Color utility instance
	 *
	 * @var Color_Utility
	 */
	private $color_utility;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->color_utility         = new Color_Utility();
		$this->accessibility_checker = new AccessibilityChecker();
	}

	/**
	 * Test contrast ratio calculation
	 */
	public function test_contrast_ratio_calculation() {
		$color1 = Color_Constants::COLOR_WHITE; // White
		$color2 = Color_Constants::COLOR_BLACK; // Black

		$contrast = $this->color_utility->get_contrast_ratio( $color1, $color2 );
		$this->assertGreaterThanOrEqual( Color_Constants::CONTRAST_MAX, $contrast, 'White and black should have maximum contrast ratio' );
	}

	/**
	 * Test WCAG AA compliance check
	 */
	public function test_wcag_aa_compliance() {
		$color1 = Color_Constants::COLOR_WHITE;
		$color2 = Color_Constants::COLOR_MID_GRAY;

		$contrast = $this->color_utility->get_contrast_ratio( $color1, $color2 );
		$this->assertTrue( $contrast >= Color_Constants::WCAG_CONTRAST_AA, 'Colors should meet WCAG AA standard for normal text' );
	}

	/**
	 * Test WCAG AAA compliance check
	 */
	public function test_wcag_aaa_compliance() {
		$color1 = Color_Constants::COLOR_WHITE;
		$color2 = Color_Constants::COLOR_BLACK;

		$contrast = $this->color_utility->get_contrast_ratio( $color1, $color2 );
		$this->assertTrue( $contrast >= Color_Constants::WCAG_CONTRAST_AAA, 'Colors should meet WCAG AAA standard' );
	}

	/**
	 * Test color readability
	 *
	 * Tests that colors either meet our target contrast ratio (AAA)
	 * or at minimum meet WCAG AA requirements
	 */
	public function test_color_readability() {
		$background = Color_Constants::COLOR_WHITE;
		$text_color = Color_Constants::COLOR_BLACK;

		$contrast = $this->color_utility->get_contrast_ratio( $text_color, $background );

		// First check if we meet our target contrast (AAA)
		if ( $contrast >= Color_Constants::WCAG_CONTRAST_TARGET ) {
			$this->assertTrue( true, 'Colors meet our target AAA contrast ratio' );
			return;
		}

		// If not meeting target, ensure we at least meet minimum AA requirement
		$this->assertTrue(
			$contrast >= Color_Constants::WCAG_CONTRAST_MIN,
			sprintf(
				'Colors should meet minimum contrast ratio. Got %.1f, need at least %.1f',
				$contrast,
				Color_Constants::WCAG_CONTRAST_MIN
			)
		);
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}
}
