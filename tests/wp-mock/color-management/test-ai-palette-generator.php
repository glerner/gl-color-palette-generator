<?php
/**
 * Tests for the AI Color Palette Generator class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Color_Management
 * @bootstrap wp-mock
 *
 * @covers GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator
 * @group color-management
 * @group ai-generation
 */

namespace GL_Color_Palette_Generator\Tests\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Utils\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;

class Test_AI_Palette_Generator extends WP_Mock_Test_Case {
	private $generator;

	protected function setUp(): void {
		WP_Mock::setUp();
		$this->color_utility = $this->createMock( Color_Utility::class );
		$this->generator     = new Color_Palette_Generator( $this->color_utility );
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_generate_ai_palette_requires_context() {
		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_palette( array( 'scheme_type' => Color_Constants::SCHEME_AI_GENERATED ) );
	}

	public function test_generate_ai_palette_with_business_context() {
		$criteria = array(
			'scheme_type'      => Color_Constants::SCHEME_AI_GENERATED,
			'business_context' => array(
				'description'     => 'Professional law firm specializing in corporate law',
				'industry'        => 'legal',
				'target_audience' => 'Corporate executives and business owners',
				'mood'            => 'Professional, trustworthy, established',
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		$this->assertIsArray( $palette );
		$this->assertArrayHasKey( 'colors', $palette );
		$this->assertArrayHasKey( 'primary', $palette['colors'] );
		$this->assertArrayHasKey( 'hex', $palette['colors']['primary'] );
		$this->assertArrayHasKey( 'name', $palette['colors']['primary'] );
		$this->assertArrayHasKey( 'emotion', $palette['colors']['primary'] );
		$this->assertEquals( 'business', $palette['inspiration']['type'] );
	}

	public function test_generate_palette_from_image_extract() {
		$criteria = array(
			'scheme_type' => Color_Constants::SCHEME_AI_GENERATED,
			'image_data'  => array(
				'image_path'   => __DIR__ . '/fixtures/desert-sunset.jpg',
				'context_type' => 'extract',
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		$this->assertIsArray( $palette );
		$this->assertArrayHasKey( 'colors', $palette );
		$this->assertArrayHasKey( 'primary', $palette['colors'] );
		$this->assertArrayHasKey( 'hex', $palette['colors']['primary'] );
		$this->assertArrayHasKey( 'name', $palette['colors']['primary'] );
		$this->assertEquals( 'Extracted from image', $palette['colors']['primary']['emotion'] );
		$this->assertEquals( 'image', $palette['inspiration']['type'] );
	}

	public function test_generate_palette_from_image_ai_enhance() {
		$criteria = array(
			'scheme_type'      => Color_Constants::SCHEME_AI_GENERATED,
			'image_data'       => array(
				'image_path'   => __DIR__ . '/fixtures/desert-sunset.jpg',
				'context_type' => 'ai-enhance',
			),
			'business_context' => array(
				'description' => 'Professional law firm in Arizona',
				'industry'    => 'legal',
				'mood'        => 'Professional yet warm and approachable',
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		$this->assertIsArray( $palette );
		$this->assertArrayHasKey( 'colors', $palette );
		$this->assertArrayHasKey( 'primary', $palette['colors'] );
		$this->assertArrayHasKey( 'hex', $palette['colors']['primary'] );
		$this->assertArrayHasKey( 'name', $palette['colors']['primary'] );
		$this->assertArrayHasKey( 'emotion', $palette['colors']['primary'] );
		$this->assertEquals( 'image', $palette['inspiration']['type'] );
	}

	public function test_get_available_algorithms() {
		$algorithms = $this->generator->get_available_algorithms();

		$this->assertIsArray( $algorithms );
		$this->assertContains( Color_Constants::SCHEME_MONOCHROMATIC, $algorithms );
		$this->assertContains( Color_Constants::SCHEME_AI_GENERATED, $algorithms );
	}

	public function test_analogous_with_complement_scheme() {
		$criteria = array(
			'scheme_type' => Color_Constants::SCHEME_ANALOGOUS_COMPLEMENT,
			'base_color'  => '#4A1259', // Deep purple from desert sunset
			'constraints' => array(
				'analogous_count'    => 2,
				'include_complement' => true,
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		$this->assertCount( 4, $palette['colors'] ); // Base + 2 analogous + complement
		$this->assertArrayHasKey( 'primary', $palette['colors'] );
		$this->assertArrayHasKey( 'secondary', $palette['colors'] );
		$this->assertArrayHasKey( 'accent', $palette['colors'] );
	}

	public function test_monochromatic_with_accent_scheme() {
		$criteria = array(
			'scheme_type' => Color_Constants::SCHEME_MONOCHROMATIC_ACCENT,
			'base_color'  => '#C76E2D', // Desert sand orange
			'constraints' => array(
				'shade_count'    => 3,
				'include_accent' => true,
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		$this->assertArrayHasKey( 'primary', $palette['colors'] );
		$this->assertArrayHasKey( 'secondary', $palette['colors'] );
		$this->assertArrayHasKey( 'accent', $palette['colors'] );
		// Check that primary and secondary are same hue
		$this->assertEquals(
			$this->color_utility->get_hue( $palette['colors']['primary']['hex'] ),
			$this->color_utility->get_hue( $palette['colors']['secondary']['hex'] )
		);
	}

	public function test_dual_tone_scheme() {
		$criteria = array(
			'scheme_type' => Color_Constants::SCHEME_DUAL_TONE,
			'base_color'  => '#E85D04', // Vivid orange
			'constraints' => array(
				'include_neutrals' => true,
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		$this->assertArrayHasKey( 'primary', $palette['colors'] );
		$this->assertArrayHasKey( 'secondary', $palette['colors'] );
		$this->assertArrayHasKey( 'neutral', $palette['colors'] );
		// Check that neutral colors are actually neutral (low saturation)
		$this->assertLessThan(
			20,
			$this->color_utility->get_saturation( $palette['colors']['neutral']['hex'] )
		);
	}

	public function test_60_30_10_distribution() {
		$criteria = array(
			'scheme_type' => Color_Constants::SCHEME_ANALOGOUS_COMPLEMENT,
			'base_color'  => '#942911', // Deep rust red
			'constraints' => array(
				'apply_60_30_10_rule' => true,
			),
		);

		$palette = $this->generator->generate_palette( $criteria );

		// Check color distribution metadata
		$this->assertArrayHasKey( 'distribution', $palette );
		$this->assertEquals( 60, $palette['distribution']['primary'] );
		$this->assertEquals( 30, $palette['distribution']['secondary'] );
		$this->assertEquals( 10, $palette['distribution']['accent'] );
	}

	public function test_wcag_compliance_for_all_schemes() {
		$schemes = array(
			Color_Constants::SCHEME_ANALOGOUS_COMPLEMENT,
			Color_Constants::SCHEME_MONOCHROMATIC_ACCENT,
			Color_Constants::SCHEME_DUAL_TONE,
			Color_Constants::SCHEME_NEUTRAL_POP,
		);

		foreach ( $schemes as $scheme ) {
			$criteria = array(
				'scheme_type'   => $scheme,
				'base_color'    => '#4A1259', // Deep purple
				'accessibility' => true,
			);

			$palette = $this->generator->generate_palette( $criteria );

			// Test all text colors against background
			foreach ( $palette['colors'] as $role => $color ) {
				if ( $role !== 'background' ) {
					$contrast = $this->color_utility->check_contrast_ratio(
						$color['hex'],
						$palette['colors']['background']['hex']
					);
					$this->assertGreaterThanOrEqual(
						4.5,
						$contrast,
						"Scheme $scheme: Color $role should meet WCAG AA contrast requirements"
					);
				}
			}
		}
	}
}
