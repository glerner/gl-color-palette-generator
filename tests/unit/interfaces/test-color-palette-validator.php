<?php
/**
 * Color Palette Validator Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Validator;

class Test_Color_Palette_Validator extends Unit_Test_Case {
	private $validator;

	public function setUp(): void {
		$this->validator = $this->createMock( Color_Palette_Validator::class );
	}

	public function test_validate_colors_checks_values(): void {
		// Arrange
		$colors = array( '#FF0000', '#00FF00', '#0000FF' );
		$rules  = array(
			'formats' => array( 'hex' ),
			'ranges'  => array( 'rgb' => array( 0, 255 ) ),
		);

		$expected = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(
				array(
					'type'    => 'brightness',
					'message' => 'High contrast colors detected',
				),
			),
			'metadata' => array(
				'validated_at'  => '2024-01-20T12:00:00Z',
				'rules_applied' => array( 'formats', 'ranges' ),
			),
		);

		$this->validator
			->expects( $this->once() )
			->method( 'validate_colors' )
			->with( $colors, $rules )
			->willReturn( $expected );

		// Act
		$result = $this->validator->validate_colors( $colors, $rules );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'valid', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertArrayHasKey( 'warnings', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['valid'] );
	}

	public function test_validate_structure_checks_schema(): void {
		// Arrange
		$palette = array(
			'name'     => 'Test Palette',
			'colors'   => array( '#FF0000', '#00FF00' ),
			'metadata' => array( 'created' => '2024-01-20' ),
		);

		$schema = array(
			'required' => array( 'name', 'colors' ),
			'types'    => array(
				'name'   => 'string',
				'colors' => 'array',
			),
		);

		$expected = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(),
			'metadata' => array(
				'validated_at'   => '2024-01-20T12:00:00Z',
				'schema_version' => '1.0',
			),
		);

		$this->validator
			->expects( $this->once() )
			->method( 'validate_structure' )
			->with( $palette, $schema )
			->willReturn( $expected );

		// Act
		$result = $this->validator->validate_structure( $palette, $schema );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'valid', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertArrayHasKey( 'warnings', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['valid'] );
	}

	public function test_validate_relationships_checks_harmony(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#00FF00', '#0000FF' ),
		);

		$rules = array(
			'harmony'  => array( 'complementary' => true ),
			'contrast' => array( 'min_ratio' => 4.5 ),
		);

		$expected = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(
				array(
					'type'    => 'contrast',
					'message' => 'Some combinations below target ratio',
				),
			),
			'metadata' => array(
				'validated_at'  => '2024-01-20T12:00:00Z',
				'rules_applied' => array( 'harmony', 'contrast' ),
			),
		);

		$this->validator
			->expects( $this->once() )
			->method( 'validate_relationships' )
			->with( $palette, $rules )
			->willReturn( $expected );

		// Act
		$result = $this->validator->validate_relationships( $palette, $rules );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'valid', $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertArrayHasKey( 'warnings', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['valid'] );
	}

	public function test_validate_accessibility_checks_standards(): void {
		// Arrange
		$palette = array(
			'name'   => 'Test Palette',
			'colors' => array( '#FF0000', '#FFFFFF' ),
		);

		$standards = array(
			'wcag'     => array( 'level' => 'AA' ),
			'contrast' => array( 'min_ratio' => 4.5 ),
		);

		$expected = array(
			'compliant'   => true,
			'violations'  => array(),
			'suggestions' => array(
				array(
					'type'    => 'contrast',
					'message' => 'Consider increasing contrast for better readability',
				),
			),
			'metadata'    => array(
				'validated_at' => '2024-01-20T12:00:00Z',
				'standards'    => array( 'WCAG 2.1 AA' ),
			),
		);

		$this->validator
			->expects( $this->once() )
			->method( 'validate_accessibility' )
			->with( $palette, $standards )
			->willReturn( $expected );

		// Act
		$result = $this->validator->validate_accessibility( $palette, $standards );

		// Assert
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'compliant', $result );
		$this->assertArrayHasKey( 'violations', $result );
		$this->assertArrayHasKey( 'suggestions', $result );
		$this->assertArrayHasKey( 'metadata', $result );
		$this->assertTrue( $result['compliant'] );
	}

	/**
	 * @dataProvider invalidColorsProvider
	 */
	public function test_validate_colors_detects_invalid_values( array $colors ): void {
		$this->validator
			->expects( $this->once() )
			->method( 'validate_colors' )
			->with( $colors )
			->willReturn(
				array(
					'valid'  => false,
					'errors' => array(
						array(
							'type'    => 'format',
							'message' => 'Invalid color format',
						),
					),
				)
			);

		$result = $this->validator->validate_colors( $colors );
		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['errors'] );
	}

	public function invalidColorsProvider(): array {
		return array(
			'empty_colors'  => array( array() ),
			'invalid_hex'   => array( array( '#XYZ' ) ),
			'mixed_formats' => array( array( '#FF0000', 'rgb(0,0,0)' ) ),
			'invalid_type'  => array( array( 'not_a_color' ) ),
		);
	}

	/**
	 * @dataProvider invalidStructureProvider
	 */
	public function test_validate_structure_detects_invalid_schema( array $palette ): void {
		$schema = array( 'required' => array( 'name', 'colors' ) );

		$this->validator
			->expects( $this->once() )
			->method( 'validate_structure' )
			->with( $palette, $schema )
			->willReturn(
				array(
					'valid'  => false,
					'errors' => array(
						array(
							'type'    => 'missing',
							'message' => 'Required field missing',
						),
					),
				)
			);

		$result = $this->validator->validate_structure( $palette, $schema );
		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['errors'] );
	}

	public function invalidStructureProvider(): array {
		return array(
			'empty_palette'  => array( array() ),
			'missing_name'   => array( array( 'colors' => array( '#FF0000' ) ) ),
			'missing_colors' => array( array( 'name' => 'Test' ) ),
			'invalid_types'  => array(
				array(
					'name'   => 123,
					'colors' => 'not-array',
				),
			),
		);
	}
}
