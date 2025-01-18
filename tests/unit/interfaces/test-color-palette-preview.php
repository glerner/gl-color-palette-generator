<?php
/**
 * Color Palette Preview Interface Tests
 *
 * Tests for color palette preview features including UI previews,
 * design mockups, code snippets, and visualization generation.
 * Validates various preview templates, options, and error handling.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 * @version 1.1.0
 * @author GL Color Palette Generator
 * @copyright 2024 GL Color Palette Generator
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Preview;

class Test_Color_Palette_Preview extends Unit_Test_Case {
    private $preview;

    public function setUp(): void {
        $this->preview = $this->createMock(Color_Palette_Preview::class);
    }

    /**
     * Test that generate_ui_preview creates interface
     */
    public function test_generate_ui_preview_creates_interface(): void {
        // Test cases for different UI preview scenarios
        $test_cases = [
            [
                'palette' => [
                    'name' => 'Test Palette',
                    'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                    'description' => 'Primary colors palette'
                ],
                'options' => [
                    'template' => 'grid',
                    'components' => ['swatches', 'labels', 'info'],
                    'interactions' => ['hover', 'click', 'copy'],
                    'layout' => 'horizontal',
                    'size' => 'large'
                ],
                'expected' => [
                    'preview' => '<div class="palette-preview">...</div>',
                    'components' => [
                        'swatches' => [
                            ['color' => '#FF0000', 'label' => 'Red', 'info' => ['rgb' => '255,0,0']],
                            ['color' => '#00FF00', 'label' => 'Green', 'info' => ['rgb' => '0,255,0']],
                            ['color' => '#0000FF', 'label' => 'Blue', 'info' => ['rgb' => '0,0,255']]
                        ],
                        'interactions' => [
                            'hover' => '.swatch:hover { transform: scale(1.1); }',
                            'click' => 'function handleClick(e) { ... }',
                            'copy' => 'function copyToClipboard(color) { ... }'
                        ]
                    ],
                    'assets' => [
                        'css' => ['preview.css', 'animations.css'],
                        'js' => ['interactions.js', 'clipboard.js']
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:22:36-07:00',
                        'template' => 'grid',
                        'version' => '1.1.0'
                    ]
                ]
            ],
            [
                'palette' => [
                    'name' => 'Brand Theme',
                    'colors' => ['#1A1A1A', '#FFFFFF'],
                    'description' => 'Monochrome theme'
                ],
                'options' => [
                    'template' => 'minimal',
                    'components' => ['swatches'],
                    'interactions' => ['hover'],
                    'layout' => 'vertical',
                    'size' => 'small'
                ],
                'expected' => [
                    'preview' => '<div class="palette-preview minimal">...</div>',
                    'components' => [
                        'swatches' => [
                            ['color' => '#1A1A1A', 'label' => 'Black'],
                            ['color' => '#FFFFFF', 'label' => 'White']
                        ],
                        'interactions' => [
                            'hover' => '.swatch:hover { opacity: 0.9; }'
                        ]
                    ],
                    'assets' => [
                        'css' => ['minimal.css'],
                        'js' => ['minimal.js']
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:22:36-07:00',
                        'template' => 'minimal',
                        'version' => '1.1.0'
                    ]
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->preview
                ->expects($this->once())
                ->method('generate_ui_preview')
                ->with($case['palette'], $case['options'])
                ->willReturn($case['expected']);

            $result = $this->preview->generate_ui_preview($case['palette'], $case['options']);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('preview', $result);
            $this->assertArrayHasKey('components', $result);
            $this->assertArrayHasKey('assets', $result);
            $this->assertArrayHasKey('metadata', $result);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test that generate_design_preview creates mockup
     */
    public function test_generate_design_preview_creates_mockup(): void {
        // Arrange
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
                'generated_at' => '2024-12-08T19:04:25-07:00',
                'template' => 'website'
            ]
        ];

        $this->preview
            ->expects($this->once())
            ->method('generate_design_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->preview->generate_design_preview($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * Test that generate_code_preview creates snippets
     */
    public function test_generate_code_preview_creates_snippets(): void {
        // Test cases for different code preview formats
        $test_cases = [
            [
                'palette' => [
                    'name' => 'Theme Colors',
                    'colors' => ['#FF0000', '#00FF00', '#0000FF']
                ],
                'options' => [
                    'language' => 'css',
                    'format' => 'variables',
                    'syntax' => ['highlighting' => true],
                    'prefix' => 'theme'
                ],
                'expected' => [
                    'preview' => ':root {\n  --theme-primary: #FF0000;\n  --theme-secondary: #00FF00;\n  --theme-tertiary: #0000FF;\n}',
                    'syntax' => [
                        'language' => 'css',
                        'tokens' => ['property', 'value', 'punctuation']
                    ],
                    'assets' => [
                        'css' => ['syntax-highlight.css'],
                        'js' => ['prism.js']
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:22:36-07:00',
                        'format' => 'variables'
                    ]
                ]
            ],
            [
                'palette' => [
                    'name' => 'Brand Colors',
                    'colors' => ['#1A1A1A', '#FFFFFF']
                ],
                'options' => [
                    'language' => 'scss',
                    'format' => 'map',
                    'syntax' => ['highlighting' => true],
                    'prefix' => 'brand'
                ],
                'expected' => [
                    'preview' => '$brand-colors: (\n  "primary": #1A1A1A,\n  "secondary": #FFFFFF\n);',
                    'syntax' => [
                        'language' => 'scss',
                        'tokens' => ['variable', 'string', 'color']
                    ],
                    'assets' => [
                        'css' => ['syntax-highlight.css'],
                        'js' => ['prism.js']
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:22:36-07:00',
                        'format' => 'map'
                    ]
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->preview
                ->expects($this->once())
                ->method('generate_code_preview')
                ->with($case['palette'], $case['options'])
                ->willReturn($case['expected']);

            $result = $this->preview->generate_code_preview($case['palette'], $case['options']);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('preview', $result);
            $this->assertArrayHasKey('syntax', $result);
            $this->assertArrayHasKey('assets', $result);
            $this->assertArrayHasKey('metadata', $result);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test that generate_mockup_preview creates visualization
     */
    public function test_generate_mockup_preview_creates_visualization(): void {
        // Arrange
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
                'generated_at' => '2024-12-08T19:04:25-07:00',
                'template' => 'mobile-app'
            ]
        ];

        $this->preview
            ->expects($this->once())
            ->method('generate_mockup_preview')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->preview->generate_mockup_preview($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('preview', $result);
        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_generate_ui_preview_throws_exception_for_invalid_palette($palette): void {
        $this->preview
            ->expects($this->once())
            ->method('generate_ui_preview')
            ->with($palette, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->preview->generate_ui_preview($palette, []);
    }

    /**
     * @dataProvider invalidOptionsProvider
     */
    public function test_generate_design_preview_throws_exception_for_invalid_options($options): void {
        $palette = ['name' => 'Test', 'colors' => ['#FF0000']];

        $this->preview
            ->expects($this->once())
            ->method('generate_design_preview')
            ->with($palette, $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->preview->generate_design_preview($palette, $options);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty array' => [[]],
            'missing colors' => [['name' => 'Test']],
            'invalid colors' => [['name' => 'Test', 'colors' => ['invalid']]],
            'non-array input' => ['invalid'],
            'null input' => [null],
            'empty colors array' => [['name' => 'Test', 'colors' => []]],
            'non-string colors' => [['name' => 'Test', 'colors' => [123, 456]]],
            'invalid hex format' => [['name' => 'Test', 'colors' => ['#GG0000']]],
            'missing hash prefix' => [['name' => 'Test', 'colors' => ['FF0000']]],
            'invalid hex length' => [['name' => 'Test', 'colors' => ['#FF00']]],
            'non-string name' => [['name' => 123, 'colors' => ['#FF0000']]],
            'missing name' => [['colors' => ['#FF0000']]],
            'invalid description type' => [['name' => 'Test', 'colors' => ['#FF0000'], 'description' => 123]]
        ];
    }

    public function invalidOptionsProvider(): array {
        return [
            'empty array' => [[]],
            'invalid template' => [['template' => 'invalid']],
            'missing required options' => [['elements' => []]],
            'non-array input' => ['invalid'],
            'null input' => [null],
            'invalid components type' => [['template' => 'grid', 'components' => 'invalid']],
            'empty components array' => [['template' => 'grid', 'components' => []]],
            'invalid interactions' => [['template' => 'grid', 'interactions' => ['invalid']]],
            'invalid layout value' => [['template' => 'grid', 'layout' => 'invalid']],
            'invalid size value' => [['template' => 'grid', 'size' => 'invalid']],
            'missing template' => [['components' => ['swatches']]],
            'invalid template type' => [['template' => 123]],
            'invalid assets format' => [['template' => 'grid', 'assets' => 'invalid']],
            'invalid metadata type' => [['template' => 'grid', 'metadata' => 'invalid']]
        ];
    }
}
