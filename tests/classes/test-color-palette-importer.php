<?php
/**
 * Color Palette Importer Tests
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPaletteImporter;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteImporterTest extends TestCase {
    private ColorPaletteImporter $importer;
    private ColorPaletteFormatter $formatter;

    public function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->importer = new ColorPaletteImporter($this->formatter);
    }

    public function test_import_from_json(): void {
        $json = json_encode([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ]);

        $palette = $this->importer->importFromString($json, 'json');

        $this->assertEquals('Test Palette', $palette->getName());
        $this->assertCount(3, $palette->getColors());
    }

    public function test_import_from_css(): void {
        $css = ":root {\n" .
               "  --color-red: #FF0000;\n" .
               "  --color-green: #00FF00;\n" .
               "  --color-blue: #0000FF;\n" .
               "}";

        $palette = $this->importer->importFromString($css, 'css');

        $this->assertCount(3, $palette->getColors());
        $this->assertContains('#FF0000', $palette->getColors());
    }

    public function test_import_from_scss(): void {
        $scss = "\$color-red: #FF0000;\n" .
                "\$color-green: #00FF00;\n" .
                "\$color-blue: #0000FF;";

        $palette = $this->importer->importFromString($scss, 'scss');

        $this->assertCount(3, $palette->getColors());
        $this->assertContains('#FF0000', $palette->getColors());
    }

    public function test_import_from_less(): void {
        $less = "@color-red: #FF0000;\n" .
                "@color-green: #00FF00;\n" .
                "@color-blue: #0000FF;";

        $palette = $this->importer->importFromString($less, 'less');

        $this->assertCount(3, $palette->getColors());
        $this->assertContains('#FF0000', $palette->getColors());
    }

    public function test_import_from_file(): void {
        $temp_file = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($temp_file, json_encode([
            'name' => 'File Test',
            'colors' => ['#FF0000']
        ]));
        rename($temp_file, $temp_file . '.json');
        $temp_file .= '.json';

        $palette = $this->importer->importFromFile($temp_file);

        $this->assertEquals('File Test', $palette->getName());
        $this->assertContains('#FF0000', $palette->getColors());

        unlink($temp_file);
    }

    public function test_import_from_url(): void {
        $this->markTestSkipped('Requires mock HTTP client');
    }

    public function test_invalid_format(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->importFromString('invalid data', 'invalid');
    }

    public function test_invalid_json(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->importFromString('invalid json', 'json');
    }

    public function test_invalid_file(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->importFromFile('nonexistent.json');
    }

    public function test_get_supported_formats(): void {
        $formats = $this->importer->getSupportedFormats();

        $this->assertIsArray($formats);
        $this->assertContains('json', $formats);
        $this->assertContains('css', $formats);
        $this->assertContains('scss', $formats);
    }

    public function test_validate_import_data(): void {
        $this->assertTrue($this->importer->validateImportData('{"colors":[]}', 'json'));
        $this->assertFalse($this->importer->validateImportData('invalid', 'json'));
    }

    public function test_get_format_options(): void {
        $options = $this->importer->getFormatOptions('css');

        $this->assertIsArray($options);
        $this->assertArrayHasKey('variable_prefix', $options);
        $this->assertArrayHasKey('parse_comments', $options);
    }

    public function test_get_format_options_invalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->getFormatOptions('invalid');
    }

    public function test_import_with_metadata(): void {
        $json = json_encode([
            'name' => 'Test Palette',
            'colors' => ['#FF0000'],
            'metadata' => ['source' => 'test']
        ]);

        $palette = $this->importer->importFromString($json, 'json');
        $metadata = $palette->getMetadata();

        $this->assertArrayHasKey('source', $metadata);
        $this->assertEquals('test', $metadata['source']);
    }
} 
