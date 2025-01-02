<?php
/**
 * Exporter Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\Exporter;

class ExporterTest extends TestCase {
    private $exporter;

    public function setUp(): void {
        $this->exporter = $this->createMock(Exporter::class);
    }

    public function test_export_returns_string(): void {
        // Arrange
        $data = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => [
                'name' => 'RGB Palette',
                'created' => '2024-12-08 18:38:25'
            ]
        ];
        $format = 'json';
        $expected = json_encode($data);

        $this->exporter
            ->expects($this->once())
            ->method('export')
            ->with($data, $format)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->export($data, $format);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_formats_returns_array(): void {
        // Arrange
        $expected = [
            'json' => 'application/json',
            'xml' => 'application/xml',
            'yaml' => 'application/x-yaml',
            'csv' => 'text/csv'
        ];

        $this->exporter
            ->expects($this->once())
            ->method('get_formats')
            ->willReturn($expected);

        // Act
        $result = $this->exporter->get_formats();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('json', $result);
        $this->assertArrayHasKey('xml', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_validate_format_returns_true_for_valid_format(): void {
        // Arrange
        $format = 'json';

        $this->exporter
            ->expects($this->once())
            ->method('validate_format')
            ->with($format)
            ->willReturn(true);

        // Act
        $result = $this->exporter->validate_format($format);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_mime_type_returns_string(): void {
        // Arrange
        $format = 'json';
        $expected = 'application/json';

        $this->exporter
            ->expects($this->once())
            ->method('get_mime_type')
            ->with($format)
            ->willReturn($expected);

        // Act
        $result = $this->exporter->get_mime_type($format);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function test_export_throws_exception_for_invalid_data($data): void {
        $this->exporter
            ->expects($this->once())
            ->method('export')
            ->with($data, 'json')
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export($data, 'json');
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_export_throws_exception_for_invalid_format($format): void {
        $data = ['test' => 'data'];
        
        $this->exporter
            ->expects($this->once())
            ->method('export')
            ->with($data, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->export($data, $format);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_validate_format_throws_exception_for_invalid_format($format): void {
        $this->exporter
            ->expects($this->once())
            ->method('validate_format')
            ->with($format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->validate_format($format);
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
}
