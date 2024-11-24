<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteExporter;

class ColorPaletteExporterTest extends TestCase {
    private $exporter;

    protected function setUp(): void {
        $this->exporter = $this->createMock(ColorPaletteExporter::class);
    }

    public function test_export_to_file_creates_file(): void {
        / Arrange
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
                'created_at' => '2024-01-20T12:00:00Z'
            ],
            'validation' => [
                'valid' => true,
                'format' => 'valid'
            ],
            'metadata' => [
                'exported_at' => '2024-01-20T12:00:00Z',
                'format' => 'json'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_file')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        / Act
        $result = $this->exporter->export_to_file($palette, $format, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_export_to_code_generates_code(): void {
        / Arrange
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
                'generated_at' => '2024-01-20T12:00:00Z',
                'language' => 'scss'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_code')
            ->with($palette, $language, $options)
            ->willReturn($expected);

        / Act
        $result = $this->exporter->export_to_code($palette, $language, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('variables', $result);
        $this->assertArrayHasKey('documentation', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_export_to_tool_creates_tool_file(): void {
        / Arrange
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
                'exported_at' => '2024-01-20T12:00:00Z',
                'tool' => 'sketch'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_tool')
            ->with($palette, $tool, $options)
            ->willReturn($expected);

        / Act
        $result = $this->exporter->export_to_tool($palette, $tool, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file', $result);
        $this->assertArrayHasKey('swatches', $result);
        $this->assertArrayHasKey('compatibility', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_validate_export_checks_format(): void {
        / Arrange
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
                'validated_at' => '2024-01-20T12:00:00Z',
                'rules_applied' => ['format', 'structure']
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('validate_export')
            ->with($export, $rules)
            ->willReturn($expected);

        / Act
        $result = $this->exporter->validate_export($export, $rules);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
    }

    /**
     * @dataProvider invalidPaletteDataProvider
     */
    public function test_export_to_file_validates_palette(array $palette): void {
        $format = 'json';

        $this->exporter
            ->expects($this->once())
            ->method('export_to_file')
            ->with($palette, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_to_file($palette, $format);
    }

    public function invalidPaletteDataProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_name' => [['colors' => ['#FF0000']]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['name' => 'Test', 'colors' => 'not-array']]
        ];
    }

    /**
     * @dataProvider invalidExportFormatProvider
     */
    public function test_export_to_file_validates_format(string $format): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];

        $this->exporter
            ->expects($this->once())
            ->method('export_to_file')
            ->with($palette, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_to_file($palette, $format);
    }

    public function invalidExportFormatProvider(): array {
        return [
            'empty_format' => [''],
            'invalid_format' => ['invalid'],
            'unknown_format' => ['doc'],
            'numeric_format' => ['123']
        ];
    }
}
