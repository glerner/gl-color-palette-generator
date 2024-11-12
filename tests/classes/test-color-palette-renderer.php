<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteRenderer;

class ColorPaletteRendererTest extends TestCase {
    private $renderer;
    private $palette;

    protected function setUp(): void {
        $this->renderer = new ColorPaletteRenderer();
        $this->palette = new ColorPalette([
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ]);
    }

    public function test_render_html_creates_valid_markup(): void {
        // Act
        $output = $this->renderer->render($this->palette, ['format' => 'html']);

        // Assert
        $this->assertStringContainsString('<div class="gl-palette-container', $output);
        $this->assertStringContainsString('gl-palette-grid', $output);
        $this->assertStringContainsString('gl-palette-medium', $output);
        $this->assertStringContainsString('Test Palette', $output);
        $this->assertStringContainsString('background-color: #FF0000', $output);
        $this->assertStringContainsString('role="listitem"', $output);
    }

    public function test_render_html_respects_options(): void {
        // Arrange
        $options = [
            'format' => 'html',
            'template' => 'list',
            'show_labels' => false,
            'show_values' => false,
            'class_prefix' => 'custom',
            'container_class' => 'extra-class',
            'swatch_size' => 'large',
            'accessibility' => false
        ];

        // Act
        $output = $this->renderer->render($this->palette, $options);

        // Assert
        $this->assertStringContainsString('custom-container', $output);
        $this->assertStringContainsString('custom-list', $output);
        $this->assertStringContainsString('custom-large', $output);
        $this->assertStringContainsString('extra-class', $output);
        $this->assertStringNotContainsString('Test Palette', $output);
        $this->assertStringNotContainsString('role="listitem"', $output);
    }

    public function test_render_css_creates_valid_stylesheet(): void {
        // Act
        $output = $this->renderer->render($this->palette, ['format' => 'css']);

        // Assert
        $this->assertStringContainsString('/* Test Palette */', $output);
        $this->assertStringContainsString(':root {', $output);
        $this->assertStringContainsString('--test-palette-color-1: #FF0000;', $output);
        $this->assertStringContainsString('--test-palette-color-2: #00FF00;', $output);
        $this->assertStringContainsString('--test-palette-color-3: #0000FF;', $output);
    }

    public function test_render_json_creates_valid_json(): void {
        // Act
        $output = $this->renderer->render($this->palette, ['format' => 'json']);
        $data = json_decode($output, true);

        // Assert
        $this->assertIsArray($data);
        $this->assertEquals('Test Palette', $data['name']);
        $this->assertCount(3, $data['colors']);
        $this->assertArrayHasKey('metadata', $data);
    }

    public function test_get_supported_formats_returns_array(): void {
        // Act
        $formats = $this->renderer->get_supported_formats();

        // Assert
        $this->assertIsArray($formats);
        $this->assertContains('html', $formats);
        $this->assertContains('css', $formats);
        $this->assertContains('json', $formats);
    }

    public function test_get_supported_templates_returns_array(): void {
        // Act
        $templates = $this->renderer->get_supported_templates();

        // Assert
        $this->assertIsArray($templates);
        $this->assertContains('grid', $templates);
        $this->assertContains('list', $templates);
        $this->assertContains('circle', $templates);
        $this->assertContains('compact', $templates);
    }

    public function test_get_supported_sizes_returns_array(): void {
        // Act
        $sizes = $this->renderer->get_supported_sizes();

        // Assert
        $this->assertIsArray($sizes);
        $this->assertContains('small', $sizes);
        $this->assertContains('medium', $sizes);
        $this->assertContains('large', $sizes);
    }

    /**
     * @dataProvider invalidFormatProvider
     */
    public function test_render_validates_format(string $invalid_format): void {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->renderer->render($this->palette, ['format' => $invalid_format]);
    }

    public function invalidFormatProvider(): array {
        return [
            'empty_format' => [''],
            'invalid_format' => ['invalid'],
            'numeric_format' => ['123'],
            'special_chars' => ['format@!']
        ];
    }

    public function test_render_html_escapes_output(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test <script>alert("XSS")</script>',
            'colors' => ['#FF0000', '#00FF00']
        ]);

        // Act
        $output = $this->renderer->render($palette, ['format' => 'html']);

        // Assert
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function test_render_css_sanitizes_identifiers(): void {
        // Arrange
        $palette = new ColorPalette([
            'name' => 'Test @ Palette!',
            'colors' => ['#FF0000']
        ]);

        // Act
        $output = $this->renderer->render($palette, ['format' => 'css']);

        // Assert
        $this->assertStringContainsString('--test-palette', $output);
        $this->assertStringNotContainsString('@', $output);
        $this->assertStringNotContainsString('!', $output);
    }

    public function test_render_handles_empty_palette(): void {
        // Arrange
        $empty_palette = new ColorPalette([
            'name' => 'Empty Palette',
            'colors' => []
        ]);

        // Act
        $html = $this->renderer->render($empty_palette, ['format' => 'html']);
        $css = $this->renderer->render($empty_palette, ['format' => 'css']);
        $json = $this->renderer->render($empty_palette, ['format' => 'json']);

        // Assert
        $this->assertStringContainsString('gl-palette-swatches', $html);
        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('"colors":[]', $json);
    }

    public function test_render_html_includes_container_attributes(): void {
        // Arrange
        $options = [
            'format' => 'html',
            'container_class' => 'test-class another-class',
            'accessibility' => true
        ];

        // Act
        $output = $this->renderer->render($this->palette, $options);

        // Assert
        $this->assertStringContainsString('class="gl-palette-container test-class another-class"', $output);
        $this->assertStringContainsString('role="listitem"', $output);
        $this->assertStringContainsString('aria-label="Color', $output);
    }
} 
