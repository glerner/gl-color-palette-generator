<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\ColorExporter;

class ColorExporterTest extends TestCase {
    private $exporter;

    public function setUp(): void {
        $this->exporter = $this->createMock(ColorExporter::class);
    }

    public function test_export_palette_returns_valid_structure(): void {
        // Arrange
        $palette = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'names' => ['Primary Red', 'Accent Green', 'Background Blue']
        ];
        $format = 'css';
        $options = [
            'variable_prefix' => 'gl',
            'include_metadata' => true
        ];

        $expected = [
            'content' => ':root { --gl-primary-red: #FF0000; }',
            'filename' => 'palette-export.css',
            'mime_type' => 'text/css',
            'metadata' => [
                'export_date' => '2024-01-20',
                'format_version' => '1.0'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('export_palette')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->export_palette($palette, $format, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_get_supported_formats_returns_format_list(): void {
        // Arrange
        $expected = [
            'css' => [
                'name' => 'CSS Variables',
                'extension' => '.css',
                'mime_type' => 'text/css',
                'options' => ['variable_prefix', 'minify']
            ],
            'scss' => [
                'name' => 'SCSS Variables',
                'extension' => '.scss',
                'mime_type' => 'text/x-scss',
                'options' => ['variable_prefix', 'minify']
            ],
            'json' => [
                'name' => 'JSON',
                'extension' => '.json',
                'mime_type' => 'application/json',
                'options' => ['pretty_print']
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('get_supported_formats')
            ->willReturn($expected);

        // Act
        $result = $this->exporter->get_supported_formats();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('scss', $result);
        $this->assertArrayHasKey('json', $result);
        $this->assertArrayHasKey('options', $result['css']);
    }

    public function test_validate_for_export_returns_validation_results(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $format = 'css';
        $expected = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => ['Color names not provided']
        ];

        $this->exporter
            ->expects($this->once())
            ->method('validate_for_export')
            ->with($palette, $format)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->validate_for_export($palette, $format);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertIsBool($result['is_valid']);
    }

    public function test_generate_preview_returns_preview_content(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $format = 'css';
        $options = ['variable_prefix' => 'gl'];
        $expected = ':root { --gl-color-1: #FF0000; }';

        $this->exporter
            ->expects($this->once())
            ->method('generate_preview')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->generate_preview($palette, $format, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_export_palette_throws_exception_for_invalid_format(
        array $palette,
        string $format
    ): void {
        $this->exporter
            ->expects($this->once())
            ->method('export_palette')
            ->with($palette, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_palette($palette, $format);
    }

    public function invalidFormatProvider(): array {
        return [
            'empty_format' => [['#FF0000'], ''],
            'invalid_format' => [['#FF0000'], 'invalid'],
            'unsupported_format' => [['#FF0000'], 'docx']
        ];
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_validate_for_export_identifies_invalid_palettes(
        array $palette,
        string $format
    ): void {
        $expected = [
            'is_valid' => false,
            'errors' => ['Invalid color format'],
            'warnings' => []
        ];

        $this->exporter
            ->expects($this->once())
            ->method('validate_for_export')
            ->with($palette, $format)
            ->willReturn($expected);

        $result = $this->exporter->validate_for_export($palette, $format);
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty_palette' => [[], 'css'],
            'invalid_colors' => [['not-a-color'], 'css'],
            'mixed_invalid' => [['#FF0000', 'invalid'], 'css']
        ];
    }
} 
