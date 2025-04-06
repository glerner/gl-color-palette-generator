<?php
/**
 * Theme Generator Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\ThemeGenerator;

class Test_Theme_Generator extends Unit_Test_Case {
	private $generator;

	public function setUp(): void {
		$this->generator = $this->createMock( ThemeGenerator::class );
	}

	public function test_generate_theme_returns_array(): void {
		// Arrange
		$base_color = '#FF0000';
		$options    = array(
			'style'    => 'modern',
			'contrast' => 'high',
			'scheme'   => 'complementary',
		);
		$expected   = array(
			'primary'    => '#FF0000',
			'secondary'  => '#00FFFF',
			'accent'     => '#FF8800',
			'background' => '#FFFFFF',
			'text'       => '#333333',
			'metadata'   => array(
				'style'     => 'modern',
				'generated' => '2024-12-08 18:30:50',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'generate_theme' )
			->with( $base_color, $options )
			->willReturn( $expected );

		// Act
		$result = $this->generator->generate_theme( $base_color, $options );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'primary', $result );
		$this->assertArrayHasKey( 'secondary', $result );
		$this->assertArrayHasKey( 'accent', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_get_theme_variations_returns_array(): void {
		// Arrange
		$theme    = array(
			'primary'   => '#FF0000',
			'secondary' => '#00FFFF',
		);
		$expected = array(
			'light' => array(
				'primary'   => '#FF3333',
				'secondary' => '#33FFFF',
			),
			'dark'  => array(
				'primary'   => '#CC0000',
				'secondary' => '#00CCCC',
			),
		);

		$this->generator
			->expects( $this->once() )
			->method( 'get_theme_variations' )
			->with( $theme )
			->willReturn( $expected );

		// Act
		$result = $this->generator->get_theme_variations( $theme );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'light', $result );
		$this->assertArrayHasKey( 'dark', $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_export_theme_returns_string(): void {
		// Arrange
		$theme    = array(
			'primary'   => '#FF0000',
			'secondary' => '#00FFFF',
		);
		$format   = 'css';
		$expected = ":root {\n  --primary: #FF0000;\n  --secondary: #00FFFF;\n}";

		$this->generator
			->expects( $this->once() )
			->method( 'export_theme' )
			->with( $theme, $format )
			->willReturn( $expected );

		// Act
		$result = $this->generator->export_theme( $theme, $format );

		// Assert
		$this->assertIsString( $result );
		$this->assertEquals( $expected, $result );
	}

	public function test_validate_theme_returns_true_for_valid_theme(): void {
		// Arrange
		$theme = array(
			'primary'   => '#FF0000',
			'secondary' => '#00FFFF',
		);

		$this->generator
			->expects( $this->once() )
			->method( 'validate_theme' )
			->with( $theme )
			->willReturn( true );

		// Act
		$result = $this->generator->validate_theme( $theme );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider invalidBaseColorProvider
	 */
	public function test_generate_theme_throws_exception_for_invalid_base_color( $base_color ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'generate_theme' )
			->with( $base_color, array() )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->generate_theme( $base_color, array() );
	}

	/**
	 * @dataProvider invalidThemeProvider
	 */
	public function test_validate_theme_throws_exception_for_invalid_theme( $theme ): void {
		$this->generator
			->expects( $this->once() )
			->method( 'validate_theme' )
			->with( $theme )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->validate_theme( $theme );
	}

	/**
	 * @dataProvider invalidFormatProvider
	 */
	public function test_export_theme_throws_exception_for_invalid_format( $format ): void {
		$theme = array( 'primary' => '#FF0000' );

		$this->generator
			->expects( $this->once() )
			->method( 'export_theme' )
			->with( $theme, $format )
			->willThrowException( new \InvalidArgumentException() );

		$this->expectException( \InvalidArgumentException::class );
		$this->generator->export_theme( $theme, $format );
	}

	public function invalidBaseColorProvider(): array {
		return array(
			'empty string'   => array( '' ),
			'invalid hex'    => array( '#GG0000' ),
			'rgb format'     => array( 'rgb(255,0,0)' ),
			'no hash'        => array( 'FF0000' ),
			'invalid length' => array( '#F00' ),
		);
	}

	public function invalidThemeProvider(): array {
		return array(
			'empty array'             => array( array() ),
			'missing required colors' => array( array( 'accent' => '#FF0000' ) ),
			'invalid color values'    => array( array( 'primary' => 'red' ) ),
			'non-array theme'         => array( 'invalid' ),
			'null theme'              => array( null ),
		);
	}

	public function invalidFormatProvider(): array {
		return array(
			'empty string'       => array( '' ),
			'invalid format'     => array( 'invalid' ),
			'unsupported format' => array( 'pdf' ),
			'non-string format'  => array( 42 ),
			'null format'        => array( null ),
		);
	}
}
