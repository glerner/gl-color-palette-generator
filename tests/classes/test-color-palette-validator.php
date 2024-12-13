<?php
/**
 * Color Palette Validator Tests
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteValidator;

class ColorPaletteValidatorTest extends TestCase {
    protected ColorPaletteValidator $validator;

    protected function setUp(): void {
        $this->validator = new ColorPaletteValidator();
    }

    public function test_validate_valid_palette(): void {
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => [
                'type' => 'custom',
                'tags' => ['test', 'rgb'],
                'created_at' => '2024-03-14T12:00:00Z'
            ]
        ]);

        $this->assertTrue($this->validator->validatePalette($palette));
        $this->assertEmpty($this->validator->getErrors());
    }

    public function test_validate_invalid_color_format(): void {
        $palette = new ColorPalette([
            'name' => 'Invalid Colors',
            'colors' => ['invalid', '#FF0000']
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_empty_name(): void {
        $palette = new ColorPalette([
            'name' => '',
            'colors' => ['#FF0000']
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_empty_colors(): void {
        $palette = new ColorPalette([
            'name' => 'Empty Colors',
            'colors' => []
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_invalid_metadata_type(): void {
        $palette = new ColorPalette([
            'name' => 'Invalid Metadata',
            'colors' => ['#FF0000'],
            'metadata' => [
                'type' => 'invalid'
            ]
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_invalid_metadata_datetime(): void {
        $palette = new ColorPalette([
            'name' => 'Invalid DateTime',
            'colors' => ['#FF0000'],
            'metadata' => [
                'created_at' => 'invalid-date'
            ]
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_invalid_metadata_version(): void {
        $palette = new ColorPalette([
            'name' => 'Invalid Version',
            'colors' => ['#FF0000'],
            'metadata' => [
                'version' => 'invalid'
            ]
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_color_format(): void {
        $this->assertTrue($this->validator->validateColorFormat('#FF0000'));
        $this->assertTrue($this->validator->validateColorFormat('#fff'));
        $this->assertFalse($this->validator->validateColorFormat('invalid'));
        $this->assertFalse($this->validator->validateColorFormat('#GGGGGG'));
    }

    public function test_validate_structure(): void {
        $valid_data = [
            'name' => 'Test',
            'colors' => ['#FF0000'],
            'metadata' => []
        ];

        $this->assertTrue($this->validator->validateStructure($valid_data));
        $this->assertEmpty($this->validator->getErrors());
    }

    public function test_get_validation_rules(): void {
        $rules = $this->validator->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('palette', $rules);
        $this->assertArrayHasKey('metadata', $rules);
    }

    public function test_validate_metadata_tags(): void {
        $valid_metadata = [
            'tags' => ['tag1', 'tag2']
        ];

        $invalid_metadata = [
            'tags' => ['tag1', 123]
        ];

        $this->assertTrue($this->validator->validateMetadata($valid_metadata));
        $this->assertFalse($this->validator->validateMetadata($invalid_metadata));
    }

    public function test_validate_too_many_colors(): void {
        $colors = array_fill(0, 101, '#FF0000');
        $palette = new ColorPalette([
            'name' => 'Too Many Colors',
            'colors' => $colors
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function test_validate_name_too_long(): void {
        $long_name = str_repeat('a', 101);
        $palette = new ColorPalette([
            'name' => $long_name,
            'colors' => ['#FF0000']
        ]);

        $this->assertFalse($this->validator->validatePalette($palette));
        $this->assertNotEmpty($this->validator->getErrors());
    }
}
