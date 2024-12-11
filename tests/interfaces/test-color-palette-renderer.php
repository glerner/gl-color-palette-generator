<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPaletteRenderer;

class ColorPaletteRendererTest extends TestCase {
    private $renderer;

    protected function setUp(): void {
        $this->renderer = $this->createMock(ColorPaletteRenderer::class);
    }

    public function test_render_as_html_generates_markup(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'template' => 'grid',
            'classes' => ['palette-container'],
            'interactions' => ['hover' => true]
        ];

        $expected = [
            'html' => '<div class="palette-container">...</div>',
            'assets' => [
                'css' => ['palette.css'],
                'js' => ['interactions.js']
            ],
            'interactions' => [
                'hover' => ['selector' => '.color-swatch'],
                'click' => ['selector' => '.color-info']
            ],
            'metadata' => [
                'rendered_at' => '2024-01-20T12:00:00Z',
                'template' => 'grid'
            ]
        ];

        $this->renderer
            ->expects($this->once())
            ->method('render_as_html')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_as_html($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('interactions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_render_as_image_creates_image(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'format' => 'png',
            'dimensions' => ['width' => 800, 'height' => 400],
            'layout' => 'horizontal'
        ];

        $expected = [
            'image' => '/path/to/palette.png',
            'dimensions' => [
                'width' => 800,
                'height' => 400,
                'ratio' => 2
            ],
            'format' => [
                'type' => 'png',
                'quality' => 100
            ],
            'metadata' => [
                'rendered_at' => '2024-01-20T12:00:00Z',
                'layout' => 'horizontal'
            ]
        ];

        $this->renderer
            ->expects($this->once())
            ->method('render_as_image')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_as_image($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('image', $result);
        $this->assertArrayHasKey('dimensions', $result);
        $this->assertArrayHasKey('format', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_render_preview_generates_preview(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'type' => 'website',
            'elements' => ['header', 'buttons']
        ];

        $expected = [
            'preview' => '<div class="preview-container">...</div>',
            'context' => [
                'type' => 'website',
                'elements' => ['header', 'buttons']
            ],
            'interactions' => [
                'theme_switch' => true,
                'element_hover' => true
            ],
            'metadata' => [
                'rendered_at' => '2024-01-20T12:00:00Z',
                'preview_type' => 'website'
            ]
        ];

        $this->renderer
            ->expects($this->once())
            ->method('render_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_preview($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('interactions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_render_documentation_generates_docs(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'format' => 'markdown',
            'sections' => ['usage', 'examples']
        ];

        $expected = [
            'documentation' => '# Test Palette\n## Usage...',
            'sections' => [
                'usage' => '## Usage\n...',
                'examples' => '## Examples\n...'
            ],
            'examples' => [
                'css' => '.primary { color: #FF0000; }',
                'scss' => '$primary: #FF0000;'
            ],
            'metadata' => [
                'rendered_at' => '2024-01-20T12:00:00Z',
                'format' => 'markdown'
            ]
        ];

        $this->renderer
            ->expects($this->once())
            ->method('render_documentation')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->renderer->render_documentation($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('documentation', $result);
        $this->assertArrayHasKey('sections', $result);
        $this->assertArrayHasKey('examples', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteDataProvider
     */
    public function test_render_as_html_validates_palette(array $palette): void {
        $this->renderer
            ->expects($this->once())
            ->method('render_as_html')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->render_as_html($palette);
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
     * @dataProvider invalidImageOptionsProvider
     */
    public function test_render_as_image_validates_options(array $options): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];

        $this->renderer
            ->expects($this->once())
            ->method('render_as_image')
            ->with($palette, $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->render_as_image($palette, $options);
    }

    public function invalidImageOptionsProvider(): array {
        return [
            'invalid_format' => [['format' => 'invalid']],
            'negative_dimensions' => [['dimensions' => ['width' => -100]]],
            'invalid_layout' => [['layout' => 123]],
            'invalid_effects' => [['effects' => 'not-array']]
        ];
    }
} 
