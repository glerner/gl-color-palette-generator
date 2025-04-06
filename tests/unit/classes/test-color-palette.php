<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Palette;

class Color_Palette_Test extends Unit_Test_Case {
	private Color_Palette $palette;

	public function setUp(): void {
		$this->palette = new Color_Palette(
			array(
				'name'     => 'Test Palette',
				'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
				'metadata' => array( 'source' => 'test' ),
			)
		);
	}

	public function test_constructor_with_default_values(): void {
		$palette = new Color_Palette();
		$this->assertEquals( 'Untitled Palette', $palette->getName() );
		$this->assertEmpty( $palette->getColors() );
		$this->assertEmpty( $palette->getMetadata() );
	}

	public function test_constructor_with_custom_values(): void {
		$this->assertEquals( 'Test Palette', $this->palette->getName() );
		$this->assertEquals( array( '#FF0000', '#00FF00', '#0000FF' ), $this->palette->getColors() );
		$this->assertEquals( array( 'source' => 'test' ), $this->palette->getMetadata() );
	}

	public function test_setters_and_getters(): void {
		$this->palette
			->setName( 'New Name' )
			->setColors( array( '#FFFFFF' ) )
			->setMetadata( array( 'key' => 'value' ) );

		$this->assertEquals( 'New Name', $this->palette->getName() );
		$this->assertEquals( array( '#FFFFFF' ), $this->palette->getColors() );
		$this->assertEquals( array( 'key' => 'value' ), $this->palette->getMetadata() );
	}

	public function test_add_and_remove_color(): void {
		$this->palette->addColor( '#FFFFFF' );
		$this->assertContains( '#FFFFFF', $this->palette->getColors() );

		$this->palette->removeColor( '#FFFFFF' );
		$this->assertNotContains( '#FFFFFF', $this->palette->getColors() );
	}

	public function test_color_normalization(): void {
		$this->palette->setColors( array( '#fff', ' #AAA ', '123456' ) );
		$expected = array( '#FFFFFF', '#AAAAAA', '#123456' );
		$this->assertEquals( $expected, $this->palette->getColors() );
	}

	public function test_invalid_color_throws_exception(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->palette->addColor( 'invalid' );
	}

	public function test_to_array(): void {
		$array = $this->palette->toArray();
		$this->assertArrayHasKey( 'name', $array );
		$this->assertArrayHasKey( 'colors', $array );
		$this->assertArrayHasKey( 'metadata', $array );
	}

	/**
	 * @dataProvider colorFormatProvider
	 */
	public function test_various_color_formats( string $input, string $expected ): void {
		$this->palette->addColor( $input );
		$colors = $this->palette->getColors();
		$this->assertEquals( $expected, end( $colors ) );
	}

	public function colorFormatProvider(): array {
		return array(
			'6-digit hex' => array( '#123456', '#123456' ),
			'3-digit hex' => array( '#123', '#112233' ),
			'no hash'     => array( '123456', '#123456' ),
			'lowercase'   => array( '#abcdef', '#ABCDEF' ),
			'with spaces' => array( ' #123456 ', '#123456' ),
		);
	}
}
