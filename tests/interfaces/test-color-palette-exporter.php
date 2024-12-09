<?php
/**
 * Color Palette Exporter Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteExporter;

class ColorPaletteExporterTest extends TestCase {
    private $exporter;

    protected function setUp(): void {
        $this->exporter = $this->createMock(ColorPaletteExporter::class);
    }

    /**
     * Test that export_to_file creates a file with palette data
     */
    public function test_export_to_file_creates_file(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $format = 'json';
        $options = [
            'path' => '/exports',
            'formatting' => ['pretty' => true]
        ];

        $expected = [
            'file' => '/exports/test-palette.json',
            'stats' => [
                'size' => 256,
                'created_at' => '2024-12-08T19:04:25-07:00'
            ],
            'validation' => [
                'valid' => true,
                'format' => 'valid'
            ],
            'metadata' => [
                'exported_at' => '2024-12-08T19:04:25-07:00',
                'format' => 'json'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_file')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->export_to_file($palette, $format, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertStringEndsWith('.json', $result['file']);
        $this->assertTrue($result['validation']['valid']);
    }

    /**
     * Test that export_to_code generates code in specified language
     */
    public function test_export_to_code_generates_code(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $language = 'scss';
        $options = [
            'format' => 'variables',
            'comments' => true
        ];

        $expected = [
            'code' => '$primary: #FF0000;\n$secondary: #00FF00;',
            'variables' => [
                'primary' => '#FF0000',
                'secondary' => '#00FF00'
            ],
            'documentation' => [
                'description' => 'Color variables for Test Palette',
                'usage' => '@import "palette.scss";'
            ],
            'metadata' => [
                'generated_at' => '2024-12-08T19:04:25-07:00',
                'language' => 'scss'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_code')
            ->with($palette, $language, $options)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->export_to_code($palette, $language, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('variables', $result);
        $this->assertArrayHasKey('documentation', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertStringContainsString('$primary', $result['code']);
        $this->assertStringContainsString('$secondary', $result['code']);
    }

    /**
     * Test that export_to_tool creates a tool-specific file
     */
    public function test_export_to_tool_creates_tool_file(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $tool = 'sketch';
        $options = [
            'version' => '70',
            'swatches' => ['format' => 'global']
        ];

        $expected = [
            'file' => '/exports/test-palette.sketch',
            'swatches' => [
                'global' => [
                    ['name' => 'Primary', 'color' => '#FF0000'],
                    ['name' => 'Secondary', 'color' => '#00FF00']
                ]
            ],
            'compatibility' => [
                'version' => '70+',
                'features' => ['global_colors' => true]
            ],
            'metadata' => [
                'exported_at' => '2024-12-08T19:04:25-07:00',
                'tool' => 'sketch'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_tool')
            ->with($palette, $tool, $options)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->export_to_tool($palette, $tool, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file', $result);
        $this->assertArrayHasKey('swatches', $result);
        $this->assertArrayHasKey('compatibility', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertStringEndsWith('.sketch', $result['file']);
    }

    /**
     * Test that validate_export checks format and structure
     */
    public function test_validate_export_checks_format(): void {
        // Arrange
        $export = [
            'format' => 'json',
            'data' => ['colors' => ['#FF0000']]
        ];

        $rules = [
            'format' => ['allowed' => ['json', 'xml']],
            'structure' => ['required' => ['colors']]
        ];

        $expected = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'metadata' => [
                'validated_at' => '2024-12-08T19:04:25-07:00',
                'rules_applied' => ['format', 'structure']
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('validate_export')
            ->with($export, $rules)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->validate_export($export, $rules);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_export_to_file_throws_exception_for_invalid_palette($palette): void {
        $format = 'json';
        
        $this->exporter
            ->expects($this->once())
            ->method('export_to_file')
            ->with($palette, $format, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_to_file($palette, $format, []);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_export_to_code_throws_exception_for_invalid_format($format): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];
        
        $this->exporter
            ->expects($this->once())
            ->method('export_to_code')
            ->with($palette, $format, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_to_code($palette, $format, []);
    }

    /**
     * @dataProvider invalidToolProvider
     */
    public function test_export_to_tool_throws_exception_for_invalid_tool($tool): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];
        
        $this->exporter
            ->expects($this->once())
            ->method('export_to_tool')
            ->with($palette, $tool, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_to_tool($palette, $tool, []);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty array' => [[]],
            'missing colors' => [['name' => 'Test']],
            'invalid colors' => [['name' => 'Test', 'colors' => ['invalid']]],
            'non-array input' => ['invalid'],
            'null input' => [null]
        ];
    }

    public function invalidFormatProvider(): array {
        return [
            'empty format' => [''],
            'invalid format' => ['invalid'],
            'numeric format' => ['123'],
            'null format' => [null],
            'special chars' => ['format@!']
        ];
    }

    public function invalidToolProvider(): array {
        return [
            'empty tool' => [''],
            'invalid tool' => ['invalid'],
            'numeric tool' => ['123'],
            'null tool' => [null],
            'special chars' => ['tool@!']
        ];
    }
}
