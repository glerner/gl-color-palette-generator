<?php
/**
 * Harmony Generator Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\HarmonyGenerator;

/**
 * Test class for HarmonyGenerator interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\HarmonyGenerator
 */
class Test_Harmony_Generator extends Unit_Test_Case {
	private $generator;

	public function setUp(): void {
		$this->generator = $this->createMock( HarmonyGenerator::class );
	}

	public function test_generate_complementary_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$expected   = array(
			'base'             => '#FF0000',
			'complement'       => '#00FFFF',
			'harmony_type'     => 'complementary',
			'color_properties' => array(
				'base'       => array(
					'hue'        => 0,
					'saturation' => 100,
					'value'      => 100,
				),
				'complement' => array(
					'hue'        => 180,
					'saturation' => 100,
					'value'      => 100,
				),
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_complementary' )
			->with( $base_color )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_complementary( $base_color );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'base', $result );
		$this->assertArrayHasKey( 'complement', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_analogous_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$count      = 3;
		$expected   = array(
			'base'         => '#FF0000',
			'colors'       => array( '#FF3300', '#FF0033' ),
			'harmony_type' => 'analogous',
			'angle'        => 30,
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_analogous' )
			->with( $base_color, $count )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_analogous( $base_color, $count );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'base', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_triadic_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$expected   = array(
			'base'         => '#FF0000',
			'colors'       => array( '#00FF00', '#0000FF' ),
			'harmony_type' => 'triadic',
			'angle'        => 120,
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_triadic' )
			->with( $base_color )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_triadic( $base_color );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'base', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_split_complementary_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$angle      = 30;
		$expected   = array(
			'base'         => '#FF0000',
			'colors'       => array( '#00FF33', '#33FF00' ),
			'harmony_type' => 'split_complementary',
			'split_angle'  => $angle,
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_split_complementary' )
			->with( $base_color, $angle )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_split_complementary( $base_color, $angle );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'base', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_tetradic_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$expected   = array(
			'base'         => '#FF0000',
			'colors'       => array( '#00FF00', '#0000FF', '#FF00FF' ),
			'harmony_type' => 'tetradic',
			'angles'       => array( 90, 180, 270 ),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_tetradic' )
			->with( $base_color )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_tetradic( $base_color );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'base', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_generate_custom_harmony_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$angles     = array( 60, 120, 240 );
		$expected   = array(
			'base'         => '#FF0000',
			'colors'       => array( '#FFFF00', '#00FF00', '#0000FF' ),
			'harmony_type' => 'custom',
			'angles'       => $angles,
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_custom_harmony' )
			->with( $base_color, $angles )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_custom_harmony( $base_color, $angles );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'base', $result );
		$this->assertArrayHasKey( 'colors', $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider invalidColorProvider
	 */
	public function test_generate_complementary_throws_exception_for_invalid_color( $color ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'generate_complementary' )
			->with( $color )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_complementary( $color );
	}

	/**
	 * @dataProvider invalidCountProvider
	 */
	public function test_generate_analogous_throws_exception_for_invalid_count( $count ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'generate_analogous' )
			->with( '#FF0000', $count )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_analogous( '#FF0000', $count );
	}

	/**
	 * @dataProvider invalidAngleProvider
	 */
	public function test_generate_split_complementary_throws_exception_for_invalid_angle( $angle ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'generate_split_complementary' )
			->with( '#FF0000', $angle )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_split_complementary( '#FF0000', $angle );
	}

	public function invalidColorProvider(): array {
		return array(
			'empty string'  => array( '' ),
			'invalid hex'   => array( '#GG0000' ),
			'wrong format'  => array( 'rgb(0,255,0)' ),
			'numeric input' => array( 42 ),
			'null input'    => array( null ),
		);
	}

	public function invalidCountProvider(): array {
		return array(
			'zero count'      => array( 0 ),
			'negative count'  => array( -1 ),
			'too large count' => array( 10 ),
			'non-numeric'     => array( 'invalid' ),
			'null count'      => array( null ),
		);
	}

	public function invalidAngleProvider(): array {
		return array(
			'negative angle'  => array( -30 ),
			'zero angle'      => array( 0 ),
			'too large angle' => array( 361 ),
			'non-numeric'     => array( 'invalid' ),
			'null angle'      => array( null ),
		);
	}
}
