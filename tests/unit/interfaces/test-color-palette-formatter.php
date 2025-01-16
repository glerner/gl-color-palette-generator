<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Formatter;

class Test_Color_Palette_Formatter extends TestCase {
    private $formatter;

    public function setUp(): void {
        $this->formatter = $this->createMock(Color_Palette_Formatter::class);
    }

    public function test_format_colors_converts_values(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00'];
        $format = 'rgb';
        $options = [
            'notation' => 'object',
            'precision' => 0
        ];

        $expected = [
            'formatted' => [
                ['r' => 255, 'g' => 0, 'b' => 0],
                ['r' => 0, 'g' => 255, 'b' => 0]
            ],
            'original' => ['#FF0000', '#00FF00'],
            'conversions' => [
                ['from' => 'hex', 'to' => 'rgb'],
                ['success' => true]
            ],
            'metadata' => [
                'formatted_at' => '2024-01-20T12:00:00Z',
                'format' => 'rgb'
            ]
        ];

        $this->formatter
            ->expects($this->once())
            ->method('format_colors')
            ->with($colors, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->formatter->format_colors($colors, $format, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('formatted', $result);
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('conversions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_format_structure_organizes_data(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00'],
            'metadata' => ['created' => '2024-01-20']
        ];

        $options = [
            'structure' => 'nested',
            'fields' => ['name', 'colors']
        ];

        $expected = [
            'formatted' => [
                'palette' => [
                    'name' => 'Test Palette',
                    'colors' => [
                        ['hex' => '#FF0000'],
                        ['hex' => '#00FF00']
                    ]
                ]
            ],
            'structure' => [
                'type' => 'nested',
                'depth' => 2
            ],
            'mapping' => [
                'name' => 'string',
                'colors' => 'array'
            ],
            'metadata' => [
                'formatted_at' => '2024-01-20T12:00:00Z',
                'structure' => 'nested'
            ]
        ];

        $this->formatter
            ->expects($this->once())
            ->method('format_structure')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->formatter->format_structure($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('formatted', $result);
        $this->assertArrayHasKey('structure', $result);
        $this->assertArrayHasKey('mapping', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_format_metadata_processes_fields(): void {
        // Arrange
        $metadata = [
            'created' => '2024-01-20',
            'modified' => '2024-01-21',
            'version' => 1
        ];

        $options = [
            'fields' => ['created', 'modified'],
            'formatting' => ['dates' => 'Y-m-d']
        ];

        $expected = [
            'formatted' => [
                'created' => '2024-01-20',
                'modified' => '2024-01-21'
            ],
            'validation' => [
                'valid' => true,
                'errors' => []
            ],
            'defaults' => [
                'applied' => false,
                'fields' => []
            ],
            'metadata' => [
                'formatted_at' => '2024-01-20T12:00:00Z',
                'fields' => ['created', 'modified']
            ]
        ];

        $this->formatter
            ->expects($this->once())
            ->method('format_metadata')
            ->with($metadata, $options)
            ->willReturn($expected);

        // Act
        $result = $this->formatter->format_metadata($metadata, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('formatted', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('defaults', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_format_output_generates_formatted_data(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $format = 'json';
        $options = [
            'structure' => 'flat',
            'formatting' => ['pretty' => true]
        ];

        $expected = [
            'output' => '{"name":"Test Palette","colors":["#FF0000","#00FF00"]}',
            'format' => [
                'type' => 'json',
                'encoding' => 'utf-8'
            ],
            'applied' => [
                'structure' => 'flat',
                'formatting' => ['pretty' => true]
            ],
            'metadata' => [
                'formatted_at' => '2024-01-20T12:00:00Z',
                'format' => 'json'
            ]
        ];

        $this->formatter
            ->expects($this->once())
            ->method('format_output')
            ->with($palette, $format, $options)
            ->willReturn($expected);

        // Act
        $result = $this->formatter->format_output($palette, $format, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('format', $result);
        $this->assertArrayHasKey('applied', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidColorsProvider
     */
    public function test_format_colors_validates_input(array $colors): void {
        $format = 'rgb';

        $this->formatter
            ->expects($this->once())
            ->method('format_colors')
            ->with($colors, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->formatter->format_colors($colors, $format);
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
     * @dataProvider invalidFormatProvider
     */
    public function test_format_output_validates_format(string $format): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];

        $this->formatter
            ->expects($this->once())
            ->method('format_output')
            ->with($palette, $format)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->formatter->format_output($palette, $format);
    }

    public function invalidFormatProvider(): array {
        return [
            'empty_format' => [''],
            'invalid_format' => ['invalid'],
            'unknown_format' => ['binary'],
            'numeric_format' => ['123']
        ];
    }
} 
