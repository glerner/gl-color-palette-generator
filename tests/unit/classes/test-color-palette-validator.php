<?php
/**
 * Color Palette Validator Tests
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Palette;
use GL_Color_Palette_Generator\Color_Palette_Validator;

class Test_Color_Palette_Validator extends Unit_Test_Case {
	protected Color_Palette_Validator $validator;

	public function setUp(): void {
		$this->validator = new Color_Palette_Validator();
	}

	public function test_validate_valid_palette(): void {
		$palette = new Color_Palette(
			array(
				'name'     => 'Test Palette',
				'colors'   => array( '#FF0000', '#00FF00', '#0000FF' ),
				'metadata' => array(
					'type'       => 'custom',
					'tags'       => array( 'test', 'rgb' ),
					'created_at' => '2024-03-14T12:00:00Z',
				),
			)
		);

		$this->assertTrue( $this->validator->validatePalette( $palette ) );
		$this->assertEmpty( $this->validator->getErrors() );
	}

	public function test_validate_invalid_color_format(): void {
		$palette = new Color_Palette(
			array(
				'name'   => 'Invalid Colors',
				'colors' => array( 'invalid', '#FF0000' ),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_empty_name(): void {
		$palette = new Color_Palette(
			array(
				'name'   => '',
				'colors' => array( '#FF0000' ),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_empty_colors(): void {
		$palette = new Color_Palette(
			array(
				'name'   => 'Empty Colors',
				'colors' => array(),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_invalid_metadata_type(): void {
		$palette = new Color_Palette(
			array(
				'name'     => 'Invalid Metadata',
				'colors'   => array( '#FF0000' ),
				'metadata' => array(
					'type' => 'invalid',
				),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_invalid_metadata_datetime(): void {
		$palette = new Color_Palette(
			array(
				'name'     => 'Invalid DateTime',
				'colors'   => array( '#FF0000' ),
				'metadata' => array(
					'created_at' => 'invalid-date',
				),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_invalid_metadata_version(): void {
		$palette = new Color_Palette(
			array(
				'name'     => 'Invalid Version',
				'colors'   => array( '#FF0000' ),
				'metadata' => array(
					'version' => 'invalid',
				),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_color_format(): void {
		$this->assertTrue( $this->validator->validateColorFormat( '#FF0000' ) );
		$this->assertTrue( $this->validator->validateColorFormat( '#fff' ) );
		$this->assertFalse( $this->validator->validateColorFormat( 'invalid' ) );
		$this->assertFalse( $this->validator->validateColorFormat( '#GGGGGG' ) );
	}

	public function test_validate_structure(): void {
		$valid_data = array(
			'name'     => 'Test',
			'colors'   => array( '#FF0000' ),
			'metadata' => array(),
		);

		$this->assertTrue( $this->validator->validateStructure( $valid_data ) );
		$this->assertEmpty( $this->validator->getErrors() );
	}

	public function test_get_validation_rules(): void {
		$rules = $this->validator->getValidationRules();

		$this->assertIsArray( $rules );
		$this->assertArrayHasKey( 'palette', $rules );
		$this->assertArrayHasKey( 'metadata', $rules );
	}

	public function test_validate_metadata_tags(): void {
		$valid_metadata = array(
			'tags' => array( 'tag1', 'tag2' ),
		);

		$invalid_metadata = array(
			'tags' => array( 'tag1', 123 ),
		);

		$this->assertTrue( $this->validator->validateMetadata( $valid_metadata ) );
		$this->assertFalse( $this->validator->validateMetadata( $invalid_metadata ) );
	}

	public function test_validate_too_many_colors(): void {
		$colors  = array_fill( 0, 101, '#FF0000' );
		$palette = new Color_Palette(
			array(
				'name'   => 'Too Many Colors',
				'colors' => $colors,
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}

	public function test_validate_name_too_long(): void {
		$long_name = str_repeat( 'a', 101 );
		$palette   = new Color_Palette(
			array(
				'name'   => $long_name,
				'colors' => array( '#FF0000' ),
			)
		);

		$this->assertFalse( $this->validator->validatePalette( $palette ) );
		$this->assertNotEmpty( $this->validator->getErrors() );
	}
}
