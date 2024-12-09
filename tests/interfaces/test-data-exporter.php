<?php
/**
 * Data Exporter Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\DataExporter;

class DataExporterTest extends TestCase {
    private $exporter;

    protected function setUp(): void {
        $this->exporter = $this->createMock(DataExporter::class);
    }

    public function test_export_data_returns_string(): void {
        // Arrange
        $data = [
            'palettes' => [
                [
                    'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                    'name' => 'RGB Primary Colors'
                ]
            ],
            'metadata' => [
                'created' => '2024-12-08 18:38:25',
                'version' => '1.0'
            ]
        ];
        $format = 'json';
        $options = ['pretty_print' => true];
        $expected = json_encode($data, JSON_PRETTY_PRINT);

        $this->exporter
            ->expects($this->once())
            ->method('export_data')
            ->with($data, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->export_data($data, $format, $options);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_supported_formats_returns_array(): void {
        // Arrange
        $expected = [
            'json' => ['extension' => '.json', 'mime_type' => 'application/json'],
            'xml' => ['extension' => '.xml', 'mime_type' => 'application/xml'],
            'csv' => ['extension' => '.csv', 'mime_type' => 'text/csv']
        ];

        $this->exporter
            ->expects($this->once())
            ->method('get_supported_formats')
            ->willReturn($expected);

        // Act
        $result = $this->exporter->get_supported_formats();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('json', $result);
        $this->assertArrayHasKey('xml', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_validate_data_returns_true_for_valid_data(): void {
        // Arrange
        $data = [
            'colors' => ['#FF0000', '#00FF00'],
            'metadata' => ['name' => 'Test Palette']
        ];

        $this->exporter
            ->expects($this->once())
            ->method('validate_data')
            ->with($data)
            ->willReturn(true);

        // Act
        $result = $this->exporter->validate_data($data);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_export_options_returns_array(): void {
        // Arrange
        $format = 'json';
        $expected = [
            'pretty_print' => [
                'type' => 'boolean',
                'default' => true,
                'description' => 'Format output with indentation'
            ],
            'include_metadata' => [
                'type' => 'boolean',
                'default' => true,
                'description' => 'Include metadata in export'
            ]
        ];

        $this->exporter
            ->expects($this->once())
            ->method('get_export_options')
            ->with($format)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->get_export_options($format);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pretty_print', $result);
        $this->assertArrayHasKey('include_metadata', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function test_export_data_throws_exception_for_invalid_data($data): void {
        $this->exporter
            ->expects($this->once())
            ->method('export_data')
            ->with($data, 'json', [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_data($data, 'json', []);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_export_data_throws_exception_for_invalid_format($format): void {
        $data = ['test' => 'data'];
        
        $this->exporter
            ->expects($this->once())
            ->method('export_data')
            ->with($data, $format, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_data($data, $format, []);
    }

    /**
     * @dataProvider invalidOptionsProvider
     */
    public function test_export_data_throws_exception_for_invalid_options($options): void {
        $data = ['test' => 'data'];
        
        $this->exporter
            ->expects($this->once())
            ->method('export_data')
            ->with($data, 'json', $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export_data($data, 'json', $options);
    }

    public function invalidDataProvider(): array {
        return [
            'empty array' => [[]],
            'invalid structure' => [['invalid' => null]],
            'non-array input' => ['invalid'],
            'null input' => [null],
            'numeric input' => [42]
        ];
    }

    public function invalidFormatProvider(): array {
        return [
            'empty string' => [''],
            'unsupported format' => ['invalid'],
            'numeric format' => [42],
            'array format' => [[]],
            'null format' => [null]
        ];
    }

    public function invalidOptionsProvider(): array {
        return [
            'invalid option type' => [['pretty_print' => 'invalid']],
            'unknown option' => [['invalid_option' => true]],
            'non-array options' => ['invalid'],
            'null options' => [null],
            'numeric options' => [42]
        ];
    }
}
