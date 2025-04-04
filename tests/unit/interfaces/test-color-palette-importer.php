<?php
/**
 * Color Palette Importer Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Unit\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Importer;

/**
 * Test class for Color_Palette_Importer interface
 *
 * @covers GL_Color_Palette_Generator\Interfaces\Color_Palette_Importer
 */
class Test_Color_Palette_Importer extends Unit_Test_Case {
    private $importer;

    public function setUp(): void {
        $this->importer = $this->createMock(Color_Palette_Importer::class);
    }

    public function test_import_from_file_loads_palette(): void {
        // Arrange
        $file_path = '/imports/palette.json';
        $options = [
            'format' => 'json',
            'validation' => ['strict' => true]
        ];

        $expected = [
            'palette' => [
                'name' => 'Imported Palette',
                'colors' => ['#FF0000', '#00FF00']
            ],
            'validation' => [
                'valid' => true,
                'errors' => []
            ],
            'transformations' => [
                'applied' => ['format_colors', 'normalize_names']
            ],
            'metadata' => [
                'imported_at' => '2024-01-20T12:00:00Z',
                'source' => 'file'
            ]
        ];

        $this->importer
            ->expects($this->once())
            ->method('import_from_file')
            ->with($file_path, $options)
            ->willReturn($expected);

        // Act
        $result = $this->importer->import_from_file($file_path, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('transformations', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_import_from_url_fetches_palette(): void {
        // Arrange
        $url = 'https://api.example.com/palette';
        $options = [
            'headers' => ['Accept' => 'application/json'],
            'auth' => ['token' => 'abc123']
        ];

        $expected = [
            'palette' => [
                'name' => 'Remote Palette',
                'colors' => ['#FF0000', '#00FF00']
            ],
            'source' => [
                'url' => 'https://api.example.com/palette',
                'type' => 'api'
            ],
            'validation' => [
                'valid' => true,
                'errors' => []
            ],
            'metadata' => [
                'imported_at' => '2024-01-20T12:00:00Z',
                'source' => 'url'
            ]
        ];

        $this->importer
            ->expects($this->once())
            ->method('import_from_url')
            ->with($url, $options)
            ->willReturn($expected);

        // Act
        $result = $this->importer->import_from_url($url, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('source', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_import_from_tool_extracts_palette(): void {
        // Arrange
        $tool_file = '/imports/design.sketch';
        $tool = 'sketch';
        $options = [
            'version' => '70',
            'extraction' => ['scope' => 'global']
        ];

        $expected = [
            'palette' => [
                'name' => 'Sketch Palette',
                'colors' => ['#FF0000', '#00FF00']
            ],
            'assets' => [
                'swatches' => [
                    ['name' => 'Primary', 'color' => '#FF0000'],
                    ['name' => 'Secondary', 'color' => '#00FF00']
                ]
            ],
            'validation' => [
                'valid' => true,
                'errors' => []
            ],
            'metadata' => [
                'imported_at' => '2024-01-20T12:00:00Z',
                'tool' => 'sketch'
            ]
        ];

        $this->importer
            ->expects($this->once())
            ->method('import_from_tool')
            ->with($tool_file, $tool, $options)
            ->willReturn($expected);

        // Act
        $result = $this->importer->import_from_tool($tool_file, $tool, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('validation', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_validate_import_checks_data(): void {
        // Arrange
        $import_data = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $rules = [
            'schema' => ['required' => ['name', 'colors']],
            'constraints' => ['min_colors' => 1]
        ];

        $expected = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'metadata' => [
                'validated_at' => '2024-01-20T12:00:00Z',
                'rules_applied' => ['schema', 'constraints']
            ]
        ];

        $this->importer
            ->expects($this->once())
            ->method('validate_import')
            ->with($import_data, $rules)
            ->willReturn($expected);

        // Act
        $result = $this->importer->validate_import($import_data, $rules);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertTrue($result['valid']);
    }

    /**
     * @dataProvider invalidFilePathProvider
     */
    public function test_import_from_file_validates_path(string $file_path): void {
        $this->importer
            ->expects($this->once())
            ->method('import_from_file')
            ->with($file_path)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->importer->import_from_file($file_path);
    }

    public function invalidFilePathProvider(): array {
        return [
            'empty_path' => [''],
            'invalid_extension' => ['/path/file.xyz'],
            'directory_path' => ['/path/directory/'],
            'nonexistent' => ['/path/nonexistent.json']
        ];
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function test_import_from_url_validates_url(string $url): void {
        $this->importer
            ->expects($this->once())
            ->method('import_from_url')
            ->with($url)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->importer->import_from_url($url);
    }

    public function invalidUrlProvider(): array {
        return [
            'empty_url' => [''],
            'invalid_protocol' => ['ftp://example.com'],
            'malformed_url' => ['not_a_url'],
            'missing_host' => ['https://']
        ];
    }
}
