<?php
/**
 * Color Palette Importer Tests
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Classes;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Color_Palette_Importer;
use GL_Color_Palette_Generator\Color_Palette_Formatter;

class Test_Color_Palette_Importer extends Unit_Test_Case {
    private Color_Palette_Importer $importer;
    private Color_Palette_Formatter $formatter;

    public function setUp(): void {
        $this->formatter = new Color_Palette_Formatter();
        $this->importer = new Color_Palette_Importer($this->formatter);
    }

    /**
     * Test JSON import
     */
    public function test_import_from_json(): void {
        $json = json_encode([
            'colors' => ['#ff0000', '#00ff00', '#0000ff']
        ]);

        $palette = $this->importer->import_palette_from_string($json, 'json');

        $this->assertCount(3, $palette->get_colors());
    }

    /**
     * Test CSS import
     */
    public function test_import_from_css(): void {
        $css = '
            --color-primary: #ff0000;
            --color-secondary: #00ff00;
            --color-tertiary: #0000ff;
        ';

        $palette = $this->importer->import_palette_from_string($css, 'css');

        $this->assertCount(3, $palette->get_colors());
    }

    /**
     * Test SCSS import
     */
    public function test_import_from_scss(): void {
        $scss = '
            $color-primary: #ff0000;
            $color-secondary: #00ff00;
        ';
        $palette = $this->importer->import_palette_from_string($scss, 'scss');

        $this->assertCount(2, $palette->get_colors());
    }

    /**
     * Test LESS import
     */
    public function test_import_from_less(): void {
        $less = '
            @color-primary: #ff0000;
        ';
        $palette = $this->importer->import_palette_from_string($less, 'less');

        $this->assertCount(1, $palette->get_colors());
    }

    public function test_import_from_file(): void {
        $temp_file = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($temp_file, json_encode([
            'colors' => ['#ff0000']
        ]));
        rename($temp_file, $temp_file . '.json');
        $temp_file .= '.json';

        $palette = $this->importer->import_palette_from_file($temp_file);

        $this->assertContains('#ff0000', $palette->get_colors());

        unlink($temp_file);
    }

    public function test_import_from_url(): void {
        $this->markTestSkipped('Requires mock HTTP client');
    }

    public function test_invalid_format(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->import_palette_from_string('invalid data', 'invalid');
    }

    public function test_invalid_json(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->import_palette_from_string('invalid json', 'json');
    }

    public function test_invalid_file(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->import_palette_from_file('nonexistent.json');
    }

    public function test_get_supported_formats(): void {
        $formats = $this->importer->get_supported_formats();

        $this->assertIsArray($formats);
        $this->assertContains('json', $formats);
        $this->assertContains('css', $formats);
        $this->assertContains('scss', $formats);
    }

    public function test_validate_import_data(): void {
        $this->assertTrue($this->importer->validate_import_data('{"colors":[]}', 'json'));
        $this->assertFalse($this->importer->validate_import_data('invalid', 'json'));
    }

    public function test_get_format_options(): void {
        $options = $this->importer->get_format_options('css');

        $this->assertIsArray($options);
        $this->assertArrayHasKey('variable_prefix', $options);
        $this->assertArrayHasKey('parse_comments', $options);
    }

    public function test_get_format_options_invalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->importer->get_format_options('invalid');
    }

    public function test_import_with_metadata(): void {
        $json = json_encode([
            'colors' => ['#ff0000'],
            'metadata' => ['source' => 'test']
        ]);

        $palette = $this->importer->import_palette_from_string($json, 'json');
        $metadata = $palette->get_metadata();

        $this->assertArrayHasKey('source', $metadata);
        $this->assertEquals('test', $metadata['source']);
    }
} 
