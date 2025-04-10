<?php
/**
 * Tests for the Palette_Generator class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Generators
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Generators;

use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use GL_Color_Palette_Generator\Generators\Palette_Generator;
use GL_Color_Palette_Generator\Providers\Provider;
use GL_Color_Palette_Generator\Color_Management\Color_Validator;
use PHPUnit\Framework\MockObject\MockObject;
use WP_Mock;

/**
 * Test case for the Palette_Generator class.
 *
 * @covers \GL_Color_Palette_Generator\Generators\Palette_Generator
 */
class Test_Palette_Generator extends WP_Mock_Test_Case {
	/**
	 * The Palette_Generator instance being tested.
	 *
	 * @var Palette_Generator
	 */
	protected Palette_Generator $generator;

	/**
	 * Mock provider instance.
	 *
	 * @var Provider
	 */
	protected $mock_provider;

	/**
	 * Mock color validator instance.
	 *
	 * @var Color_Validator
	 */
	protected $mock_validator;

	/**
	 * Set up the test environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Create mock provider
		$this->mock_provider  = $this->createMock( Provider::class );
		$this->mock_validator = $this->createMock( Color_Validator::class );

		$this->generator = new Palette_Generator( $this->mock_provider, $this->mock_validator );
	}

	/**
	 * Tests the generate method with default parameters.
	 *
	 * @return void
	 */
	public function test_generate_palette() {
		$prompt          = 'Modern tech company';
		$expected_colors = array( '#2C3E50', '#E74C3C', '#ECF0F1', '#3498DB', '#2ECC71' );

		// Configure mock provider
		$this->mock_provider->expects( $this->once() )
			->method( 'generate_palette' )
			->with( $prompt )
			->willReturn( $expected_colors );

		// Configure mock validator
		$this->mock_validator->expects( $this->exactly( count( $expected_colors ) ) )
			->method( 'is_valid_color' )
			->willReturn( true );

		$palette = $this->generator->generate( $prompt );

		$this->assertEquals( $expected_colors, $palette );
	}

	/**
	 * Tests the generate method with invalid colors.
	 *
	 * @return void
	 */
	public function test_generate_palette_with_invalid_colors() {
		$prompt         = 'Modern tech company';
		$invalid_colors = array( '#2C3E50', 'invalid', '#ECF0F1', 'not-a-color', '#2ECC71' );

		// Configure mock provider
		$this->mock_provider->expects( $this->once() )
			->method( 'generate_palette' )
			->with( $prompt )
			->willReturn( $invalid_colors );

		// Configure mock validator to fail for invalid colors
		$this->mock_validator->expects( $this->exactly( count( $invalid_colors ) ) )
			->method( 'is_valid_color' )
			->willReturnCallback(
				function ( $color ) {
					return strpos( $color, '#' ) === 0;
				}
			);

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate( $prompt );
	}

	/**
	 * Tests the generate method with a specific count parameter.
	 *
	 * @return void
	 */
	public function test_generate_palette_with_count() {
		$prompt          = 'Modern tech company';
		$count           = 3;
		$expected_colors = array( '#2C3E50', '#E74C3C', '#ECF0F1' );

		// Configure mock provider
		$this->mock_provider->expects( $this->once() )
			->method( 'generate_palette' )
			->with( $prompt, $count )
			->willReturn( $expected_colors );

		// Configure mock validator
		$this->mock_validator->expects( $this->exactly( $count ) )
			->method( 'is_valid_color' )
			->willReturn( true );

		$palette = $this->generator->generate( $prompt, $count );

		$this->assertCount( $count, $palette );
		$this->assertEquals( $expected_colors, $palette );
	}

	/**
	 * Tests the generate method with a specific format parameter.
	 *
	 * @return void
	 */
	public function test_generate_palette_with_format() {
		$prompt          = 'Modern tech company';
		$format          = 'rgb';
		$expected_colors = array(
			'rgb(44, 62, 80)',
			'rgb(231, 76, 60)',
			'rgb(236, 240, 241)',
		);

		// Configure mock provider
		$this->mock_provider->expects( $this->once() )
			->method( 'generate_palette' )
			->with( $prompt, 3, $format )
			->willReturn( $expected_colors );

		// Configure mock validator
		$this->mock_validator->expects( $this->exactly( count( $expected_colors ) ) )
			->method( 'is_valid_color' )
			->willReturn( true );

		$palette = $this->generator->generate( $prompt, 3, $format );

		$this->assertEquals( $expected_colors, $palette );
	}
}
