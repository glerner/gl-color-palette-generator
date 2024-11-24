<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorPalettePreview;

class ColorPalettePreviewTest extends TestCase {
    private $preview;

    protected function setUp(): void {
        $this->preview = $this->createMock(ColorPalettePreview::class);
    }

    public function test_generate_ui_preview_creates_interface(): void {
        / Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $options = [
            'template' => 'grid',
            'components' => ['swatches', 'labels'],
            'interactions' => ['hover', 'click']
        ];

        $expected = [
            'preview' => '<div class="palette-preview">...</div>',
            'components' => [
                'swatches' => [
                    ['color' => '#FF0000', 'label' => 'Red'],
                    ['color' => '#00FF00', 'label' => 'Green'],
                    ['color' => '#0000FF', 'label' => 'Blue']
                ],
                'interactions' => [
                    'hover' => '.swatch:hover { transform: scale(1.1); }',
                    'click' => 'function handleClick(e) { ... }'
                ]
            ],
            'assets' => [
                'css' => ['preview.css'],
                'js' => ['interactions.js']
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'template' => 'grid'
            ]
        ];

        $this->preview
            ->expects($this->once())
            ->method('generate_ui_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        / Act
        $result = $this->preview->generate_ui_preview($palette, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('components', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_design_preview_creates_mockup(): void {
        / Arrange
        $palette = [
            'name' => 'Brand Colors',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'template' => 'website',
            'elements' => ['header', 'buttons'],
            'styles' => ['modern', 'clean']
        ];

        $expected = [
            'preview' => '<div class="design-preview">...</div>',
            'elements' => [
                'header' => ['background' => '#FF0000'],
                'buttons' => ['background' => '#00FF00']
            ],
            'assets' => [
                'images' => ['preview.png'],
                'styles' => ['design.css']
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'template' => 'website'
            ]
        ];

        $this->preview
            ->expects($this->once())
            ->method('generate_design_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        / Act
        $result = $this->preview->generate_design_preview($palette, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_code_preview_creates_snippets(): void {
        / Arrange
        $palette = [
            'name' => 'Theme Colors',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'language' => 'css',
            'format' => 'variables',
            'syntax' => ['highlighting' => true]
        ];

        $expected = [
            'preview' => ':root {\n  --primary: #FF0000;\n  --secondary: #00FF00;\n}',
            'syntax' => [
                'language' => 'css',
                'tokens' => ['property', 'value']
            ],
            'assets' => [
                'css' => ['syntax-highlight.css'],
                'js' => ['prism.js']
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'format' => 'variables'
            ]
        ];

        $this->preview
            ->expects($this->once())
            ->method('generate_code_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        / Act
        $result = $this->preview->generate_code_preview($palette, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('syntax', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_mockup_preview_creates_visualization(): void {
        / Arrange
        $palette = [
            'name' => 'App Theme',
            'colors' => ['#FF0000', '#00FF00']
        ];

        $options = [
            'template' => 'mobile-app',
            'context' => ['screens' => ['home', 'profile']],
            'devices' => ['iphone', 'android']
        ];

        $expected = [
            'preview' => '<div class="mockup-preview">...</div>',
            'context' => [
                'screens' => [
                    'home' => ['background' => '#FF0000'],
                    'profile' => ['accent' => '#00FF00']
                ],
                'devices' => [
                    'iphone' => ['frame' => 'iphone-14.png'],
                    'android' => ['frame' => 'pixel-7.png']
                ]
            ],
            'assets' => [
                'images' => ['frames/', 'screens/'],
                'styles' => ['mockup.css']
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'template' => 'mobile-app'
            ]
        ];

        $this->preview
            ->expects($this->once())
            ->method('generate_mockup_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        / Act
        $result = $this->preview->generate_mockup_preview($palette, $options);

        / Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_generate_ui_preview_validates_palette(array $palette): void {
        $this->preview
            ->expects($this->once())
            ->method('generate_ui_preview')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->preview->generate_ui_preview($palette);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['colors' => 'not-array']],
            'empty_colors' => [['colors' => []]]
        ];
    }

    /**
     * @dataProvider invalidTemplateProvider
     */
    public function test_generate_design_preview_validates_template(string $template): void {
        $palette = ['colors' => ['#FF0000']];
        $options = ['template' => $template];

        $this->preview
            ->expects($this->once())
            ->method('generate_design_preview')
            ->with($palette, $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->preview->generate_design_preview($palette, $options);
    }

    public function invalidTemplateProvider(): array {
        return [
            'empty_template' => [''],
            'invalid_template' => ['nonexistent'],
            'numeric_template' => ['123'],
            'special_chars' => ['template@!']
        ];
    }
} 
