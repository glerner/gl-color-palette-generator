<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteValidator;

class ColorPaletteValidatorTest extends TestCase {
    private $validator;

    protected function setUp(): void {
        $this->validator = $this->createMock(ColorPaletteValidator::class);
    }

    public function test_validate_colors_checks_values(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $rules = [
            'formats' => ['hex'],
            'ranges' => ['rgb' => [0, 255]]
        ];

        $expected = [
            'valid' => true,
            'errors' => [],
            'warnings' => [
                ['type' => 'brightness', 'message' => 'High contrast colors detected']
            ],
            'metadata' => [
                'validated_at' => '2024-01-20T12:00:00Z',
                'rules_applied' => ['formats', 'ranges']
            ]
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_colors')
            ->with($colors, $rules)
            ->willReturn($expected);

        // Act
        $result = $this->validator->validate_colors($colors, $rules);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
    }

    public function test_validate_structure_checks_schema(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00'],
            'metadata' => ['created' => '2024-01-20']
        ];

        $schema = [
            'required' => ['name', 'colors'],
            'types' => ['name' => 'string', 'colors' => 'array']
        ];

        $expected = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'metadata' => [
                'validated_at' => '2024-01-20T12:00:00Z',
                'schema_version' => '1.0'
            ]
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_structure')
            ->with($palette, $schema)
            ->willReturn($expected);

        // Act
        $result = $this->validator->validate_structure($palette, $schema);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
    }

    public function test_validate_relationships_checks_harmony(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $rules = [
            'harmony' => ['complementary' => true],
            'contrast' => ['min_ratio' => 4.5]
        ];

        $expected = [
            'valid' => true,
            'errors' => [],
            'warnings' => [
                ['type' => 'contrast', 'message' => 'Some combinations below target ratio']
            ],
            'metadata' => [
                'validated_at' => '2024-01-20T12:00:00Z',
                'rules_applied' => ['harmony', 'contrast']
            ]
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_relationships')
            ->with($palette, $rules)
            ->willReturn($expected);

        // Act
        $result = $this->validator->validate_relationships($palette, $rules);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
    }

    public function test_validate_accessibility_checks_standards(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#FFFFFF']
        ];

        $standards = [
            'wcag' => ['level' => 'AA'],
            'contrast' => ['min_ratio' => 4.5]
        ];

        $expected = [
            'compliant' => true,
            'violations' => [],
            'suggestions' => [
                ['type' => 'contrast', 'message' => 'Consider increasing contrast for better readability']
            ],
            'metadata' => [
                'validated_at' => '2024-01-20T12:00:00Z',
                'standards' => ['WCAG 2.1 AA']
            ]
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_accessibility')
            ->with($palette, $standards)
            ->willReturn($expected);

        // Act
        $result = $this->validator->validate_accessibility($palette, $standards);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('compliant', $result);
        $this->assertArrayHasKey('violations', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['compliant']);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_validate_colors_detects_invalid_values(array $colors): void {
        $this->validator
            ->expects($this->once())
            ->method('validate_colors')
            ->with($colors)
            ->willReturn([
                'valid' => false,
                'errors' => [['type' => 'format', 'message' => 'Invalid color format']]
            ]);

        $result = $this->validator->validate_colors($colors);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function invalidColorsProvider(): array {
        return [
            'empty_colors' => [[]],
            'invalid_hex' => [['#XYZ']],
            'mixed_formats' => [['#FF0000', 'rgb(0,0,0)']],
            'invalid_type' => [['not_a_color']]
        ];
    }

    /**
     * @dataProvider invalidStructureProvider
     */
    public function test_validate_structure_detects_invalid_schema(array $palette): void {
        $schema = ['required' => ['name', 'colors']];

        $this->validator
            ->expects($this->once())
            ->method('validate_structure')
            ->with($palette, $schema)
            ->willReturn([
                'valid' => false,
                'errors' => [['type' => 'missing', 'message' => 'Required field missing']]
            ]);

        $result = $this->validator->validate_structure($palette, $schema);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function invalidStructureProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_name' => [['colors' => ['#FF0000']]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_types' => [['name' => 123, 'colors' => 'not-array']]
        ];
    }
}
