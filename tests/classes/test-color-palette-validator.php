<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteValidator;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteValidatorTest extends TestCase {
    private $validator;
    private $formatter;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->validator = new ColorPaletteValidator($this->formatter);
    }

    public function test_validate_palette_accepts_valid_palette(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
        $this->assertEmpty($result['warnings']);
    }

    public function test_validate_palette_catches_missing_name(): void {
        // Arrange
        $palette = new ColorPalette([
            'colors' => ['#FF0000']
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertStringContainsString('required', $result['errors']['name'][0]);
    }

    public function test_validate_palette_enforces_name_length(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => str_repeat('a', 101),
            'colors' => ['#FF0000']
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertStringContainsString('100 characters', $result['errors']['name'][0]);
    }

    public function test_validate_palette_requires_colors(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => []
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('colors', $result['errors']);
        $this->assertStringContainsString('at least 1', $result['errors']['colors'][0]);
    }

    public function test_validate_palette_limits_color_count(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => array_fill(0, 21, '#FF0000')
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('colors', $result['errors']);
        $this->assertStringContainsString('20 items', $result['errors']['colors'][0]);
    }

    public function test_validate_palette_checks_color_formats(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', 'invalid-color', 'rgb(256,0,0)']
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('colors', $result['errors']);
        $this->assertStringContainsString('Invalid color format', $result['errors']['colors'][0]);
    }

    public function test_validate_palette_warns_about_duplicates(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#FF0000', '#00FF00']
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertTrue($result['valid']); // Duplicates are warnings, not errors
        $this->assertEmpty($result['errors']);
        $this->assertArrayHasKey('colors', $result['warnings']);
        $this->assertStringContainsString('duplicate', $result['warnings']['colors'][0]);
    }

    public function test_validate_palette_checks_contrast_ratio(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FFFFFF', '#FEFEFE'] // Very low contrast
        ]);

        $rules = array_merge($this->validator->get_default_rules(), [
            'colors' => [
                'min_contrast' => 4.5 // WCAG AA standard
            ]
        ]);

        // Act
        $result = $this->validator->validate_palette($palette, $rules);

        // Assert
        $this->assertTrue($result['valid']); // Low contrast is a warning
        $this->assertEmpty($result['errors']);
        $this->assertArrayHasKey('colors', $result['warnings']);
        $this->assertStringContainsString('Low contrast ratio', $result['warnings']['colors'][0]);
    }

    public function test_validate_palette_accepts_custom_rules(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000']
        ]);

        $custom_rules = [
            'name' => [
                'required' => true,
                'max_length' => 10
            ]
        ];

        // Act
        $result = $this->validator->validate_palette($palette, $custom_rules);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertStringContainsString('10 characters', $result['errors']['name'][0]);
    }

    public function test_validate_palette_handles_metadata(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000'],
            'metadata' => 'invalid-metadata' // Should be array
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('metadata', $result['errors']);
        $this->assertStringContainsString('type array', $result['errors']['metadata'][0]);
    }

    /**
     * @dataProvider contrastRatioProvider
     */
    public function test_contrast_ratio_calculation(
        string $color1,
        string $color2,
        float $expected_ratio,
        float $delta = 0.01
    ): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => [$color1, $color2]
        ]);

        $rules = [
            'colors' => [
                'min_contrast' => $expected_ratio - $delta
            ]
        ];

        // Act
        $result = $this->validator->validate_palette($palette, $rules);

        // Assert
        $this->assertTrue($result['valid']);
        if ($expected_ratio < $rules['colors']['min_contrast']) {
            $this->assertArrayHasKey('colors', $result['warnings']);
        } else {
            $this->assertEmpty($result['warnings']);
        }
    }

    public function contrastRatioProvider(): array {
        return [
            'black_white' => ['#000000', '#FFFFFF', 21.0],
            'red_white' => ['#FF0000', '#FFFFFF', 4.0],
            'blue_yellow' => ['#0000FF', '#FFFF00', 8.0],
            'similar_grays' => ['#777777', '#888888', 1.2]
        ];
    }

    public function test_validate_palette_handles_empty_palette(): void {
        // Arrange
        $palette = new ColorPalette();

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertArrayHasKey('colors', $result['errors']);
    }

    public function test_validate_palette_accepts_all_color_formats(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => [
                '#FF0000',
                'rgb(0, 255, 0)',
                'rgba(0, 0, 255, 1)',
                'hsl(0, 100%, 50%)',
                'hsla(120, 100%, 50%, 1)'
            ]
        ]);

        // Act
        $result = $this->validator->validate_palette($palette);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }
} 
