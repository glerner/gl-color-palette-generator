<?php
/**
 * Tests for the Color_Education class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Education
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Education;

use GL_Color_Palette_Generator\Education\Color_Education;
use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use Brain\Monkey\Functions;

/**
 * Test case for the Color_Education class.
 *
 * @covers \GL_Color_Palette_Generator\Education\Color_Education
 */
class Test_Color_Education extends Unit_Test_Case {
	/**
	 * The Color_Education instance being tested.
	 *
	 * @var Color_Education
	 */
	private $education;

	/**
	 * Set up the test environment.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
		$this->education = new Color_Education();
	}

	/**
	 * Tests the get_color_relationships method.
	 *
	 * @return void
	 */
	public function test_get_color_relationships(): void {
		$relationships = $this->education->get_color_relationships();

		$this->assertIsArray( $relationships );
		$this->assertArrayHasKey( 'analogous', $relationships );
		$this->assertArrayHasKey( 'complementary', $relationships );
		$this->assertNotEmpty( $relationships['analogous'] );
	}

	/**
	 * Tests the get_color_meaning method.
	 *
	 * @return void
	 */
	public function test_get_color_meaning(): void {
		$blue_meaning = $this->education->get_color_meaning( 'blue' );

		$this->assertIsArray( $blue_meaning );
		$this->assertArrayHasKey( 'emotion', $blue_meaning );
		$this->assertArrayHasKey( 'business_types', $blue_meaning );
		$this->assertArrayHasKey( 'usage_tips', $blue_meaning );

		// Test case insensitivity
		$this->assertEquals(
			$this->education->get_color_meaning( 'BLUE' ),
			$this->education->get_color_meaning( 'blue' )
		);

		// Test non-existent color
		$this->assertNull( $this->education->get_color_meaning( 'nonexistent' ) );
	}

	/**
	 * Tests the get_palette_documentation method.
	 *
	 * @return void
	 */
	public function test_get_palette_documentation(): void {
		$palette = array(
			'primary'   => array(
				'name' => 'Blue Ocean',
				'hex'  => '#0066cc',
			),
			'secondary' => array(
				'name' => 'Green Forest',
				'hex'  => '#33cc33',
			),
		);

		$docs = $this->education->get_palette_documentation( $palette );

		$this->assertIsArray( $docs );
		$this->assertArrayHasKey( 'primary', $docs );
		$this->assertArrayHasKey( 'secondary', $docs );
		$this->assertArrayHasKey( 'meaning', $docs['primary'] );
		$this->assertArrayHasKey( 'usage_tips', $docs['primary'] );
	}
}
