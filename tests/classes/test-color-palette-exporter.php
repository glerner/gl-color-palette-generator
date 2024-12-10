<?php
/**
 * Color Palette Exporter Tests
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteExporter;
use GLColorPalette\ColorPaletteFormatter;

class ColorPaletteExporterTest extends TestCase {
    private ColorPaletteExporter $exporter;
    private ColorPaletteFormatter $formatter;
    private ColorPalette $test_palette;
    private string $temp_dir;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->exporter = new ColorPaletteExporter($this->formatter);
        $this->test_palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF'],
            'metadata' => [
                'type' => 'test',
                'created_at' => '2024-03-14T12:00:00Z',
                'version' => '1.0.0'
            ]
        ]);
        $this->temp_dir = sys_get_temp_dir();
    }

    public function test_export_json(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'json');
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals('Test Palette', $data['name']);
        $this->assertContains('#FF0000', $data['colors']);
        $this->assertArrayHasKey('metadata', $data);
    }

    public function test_export_css(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'css');

        $this->assertStringContainsString(':root {', $output);
        $this->assertStringContainsString('--color-0: #FF0000', $output);
        $this->assertStringContainsString('/* Test Palette */', $output);
    }

    public function test_export_scss(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'scss');

        $this->assertStringContainsString('$color-0: #FF0000', $output);
        $this->assertStringContainsString('// Test Palette', $output);
    }

    public function test_export_less(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'less');

        $this->assertStringContainsString('@color-0: #FF0000', $output);
        $this->assertStringContainsString('// Test Palette', $output);
    }

    public function test_export_ase(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'ase');

        $this->assertStringStartsWith('ASEF', $output);
        $this->assertGreaterThan(20, strlen($output));
    }

    public function test_export_act(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'act');

        $this->assertEquals(768, strlen($output)); // 256 colors * 3 bytes
    }

    public function test_export_gpl(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'gpl');

        $this->assertStringContainsString('GIMP Palette', $output);
        $this->assertStringContainsString('Name: Test Palette', $output);
        $this->assertStringContainsString('255   0   0', $output);
    }

    public function test_export_xml(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'xml');

        $this->assertStringContainsString('<?xml', $output);
        $this->assertStringContainsString('<palette>', $output);
        $this->assertStringContainsString('<color index="0" hex="#FF0000"', $output);
    }

    public function test_export_yaml(): void {
        $output = $this->exporter->exportPalette($this->test_palette, 'yaml');

        $this->assertStringContainsString('name: Test Palette', $output);
        $this->assertStringContainsString('colors:', $output);
        $this->assertStringContainsString('- "#FF0000"', $output);
    }

    public function test_export_to_file(): void {
        $filename = $this->temp_dir . '/test_palette.json';
        $result = $this->exporter->exportToFile($this->test_palette, $filename, 'json');

        $this->assertTrue($result);
        $this->assertFileExists($filename);
        $this->assertIsReadable($filename);

        unlink($filename);
    }

    public function test_get_supported_formats(): void {
        $formats = $this->exporter->getSupportedFormats();

        $this->assertIsArray($formats);
        $this->assertContains('json', $formats);
        $this->assertContains('css', $formats);
        $this->assertContains('scss', $formats);
    }

    public function test_get_format_options(): void {
        $options = $this->exporter->getFormatOptions('json');

        $this->assertIsArray($options);
        $this->assertArrayHasKey('pretty_print', $options);
        $this->assertArrayHasKey('include_metadata', $options);
    }

    public function test_get_file_extension(): void {
        $this->assertEquals('json', $this->exporter->getFileExtension('json'));
        $this->assertEquals('css', $this->exporter->getFileExtension('css'));
        $this->assertEquals('scss', $this->exporter->getFileExtension('scss'));
    }

    public function test_validate_options(): void {
        $valid_options = [
            'pretty_print' => true,
            'include_metadata' => false
        ];

        $invalid_options = [
            'invalid_option' => 'value'
        ];

        $this->assertTrue($this->exporter->validateOptions($valid_options, 'json'));
        $this->assertFalse($this->exporter->validateOptions($invalid_options, 'json'));
    }

    public function test_invalid_format(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->exporter->exportPalette($this->test_palette, 'invalid');
    }

    public function test_css_color_formats(): void {
        $options = ['format' => 'rgb'];
        $output = $this->exporter->exportPalette($this->test_palette, 'css', $options);
        $this->assertStringContainsString('rgb(255, 0, 0)', $output);

        $options['format'] = 'hsl';
        $output = $this->exporter->exportPalette($this->test_palette, 'css', $options);
        $this->assertStringContainsString('hsl(0, 100%, 50%)', $output);
    }

    public function test_export_empty_palette(): void {
        $empty_palette = new ColorPalette([
            'name' => 'Empty Palette',
            'colors' => []
        ]);

        $output = $this->exporter->exportPalette($empty_palette, 'json');
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals('Empty Palette', $data['name']);
        $this->assertEmpty($data['colors']);
    }

    public function test_export_with_custom_options(): void {
        $options = [
            'prefix' => '--theme',
            'include_comments' => false
        ];

        $output = $this->exporter->exportPalette($this->test_palette, 'css', $options);

        $this->assertStringContainsString('--theme-0:', $output);
        $this->assertStringNotContainsString('/* Test Palette */', $output);
    }

    protected function tearDown(): void {
        // Clean up any temporary files if they exist
        $temp_files = glob($this->temp_dir . '/test_palette.*');
        foreach ($temp_files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
} 
