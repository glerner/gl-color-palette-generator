<?php

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\ColorSchemeGenerator;

class ColorSchemeGeneratorTest extends TestCase {
    private $generator;

    public function setUp(): void {
        $this->generator = $this->createMock(ColorSchemeGenerator::class);
    }

    public function test_generate_scheme_returns_complete_scheme(): void {
        // Arrange
        $criteria = [
            'base_color' => '#FF0000',
            'style' => 'modern',
            'purpose' => 'web',
            'accessibility' => true
        ];

        $expected = [
            'primary' => [
                'base' => '#FF0000',
                'light' => '#FF3333',
                'dark' => '#CC0000'
            ],
            'secondary' => [
                'base' => '#00FF00',
                'light' => '#33FF33',
                'dark' => '#00CC00'
            ],
            'accent' => ['#0000FF', '#FFFF00'],
            'neutral' => [
                '100' => '#FFFFFF',
                '200' => '#F5F5F5',
                '900' => '#000000'
            ],
            'semantic' => [
                'success' => '#00CC00',
                'warning' => '#FFCC00',
                'error' => '#CC0000',
                'info' => '#0066CC'
            ],
            'metadata' => [
                'generation_date' => '2024-01-20',
                'algorithm' => 'v2.0'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_scheme')
            ->with($criteria)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_scheme($criteria);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary', $result);
        $this->assertArrayHasKey('secondary', $result);
        $this->assertArrayHasKey('accent', $result);
        $this->assertArrayHasKey('neutral', $result);
        $this->assertArrayHasKey('semantic', $result);
    }

    public function test_get_color_variations_returns_valid_variations(): void {
        // Arrange
        $base_color = '#FF0000';
        $role = 'primary';
        $options = [
            'steps' => 5,
            'value_range' => [20, 80]
        ];

        $expected = [
            'colors' => [
                '100' => '#FF3333',
                '300' => '#FF0000',
                '500' => '#CC0000',
                '700' => '#990000',
                '900' => '#660000'
            ],
            'metadata' => [
                'base_color' => '#FF0000',
                'variation_method' => 'value_steps'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('get_color_variations')
            ->with($base_color, $role, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->get_color_variations($base_color, $role, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertCount(5, $result['colors']);
    }

    public function test_generate_semantic_colors_returns_valid_assignments(): void {
        // Arrange
        $base_scheme = [
            'primary' => '#FF0000',
            'secondary' => '#00FF00'
        ];
        $options = [
            'use_defaults' => true,
            'ensure_contrast' => true
        ];

        $expected = [
            'colors' => [
                'success' => '#00CC00',
                'warning' => '#FFCC00',
                'error' => '#CC0000',
                'info' => '#0066CC'
            ],
            'rationale' => [
                'success' => 'Derived from secondary color',
                'error' => 'Derived from primary color'
            ],
            'contrast' => [
                'success' => ['ratio' => 4.5, 'passes' => true],
                'error' => ['ratio' => 4.8, 'passes' => true]
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_semantic_colors')
            ->with($base_scheme, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_semantic_colors($base_scheme, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('rationale', $result);
        $this->assertArrayHasKey('contrast', $result);
    }

    public function test_analyze_scheme_returns_comprehensive_analysis(): void {
        // Arrange
        $scheme = [
            'primary' => '#FF0000',
            'secondary' => '#00FF00',
            'accent' => '#0000FF'
        ];

        $expected = [
            'is_valid' => true,
            'accessibility' => [
                'wcag_aa_compliance' => true,
                'contrast_scores' => ['4.5', '5.2']
            ],
            'harmony' => [
                'score' => 0.85,
                'type' => 'triadic'
            ],
            'coverage' => [
                'color_space' => 0.75,
                'gamut' => 'sRGB'
            ],
            'improvements' => [
                'Consider adding neutral colors',
                'Increase contrast in secondary colors'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('analyze_scheme')
            ->with($scheme)
            ->willReturn($expected);

        // Act
        $result = $this->generator->analyze_scheme($scheme);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('accessibility', $result);
        $this->assertArrayHasKey('harmony', $result);
        $this->assertArrayHasKey('improvements', $result);
    }

    /**
     * @dataProvider invalidCriteriaProvider
     */
    public function test_generate_scheme_validates_criteria(array $criteria): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_scheme')
            ->with($criteria)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_scheme($criteria);
    }

    public function invalidCriteriaProvider(): array {
        return [
            'invalid_color' => [['base_color' => 'not-a-color']],
            'invalid_style' => [['style' => 123]],
            'empty_criteria' => [[]],
            'invalid_purpose' => [['purpose' => 'invalid']]
        ];
    }

    /**
     * @dataProvider invalidSchemeProvider
     */
    public function test_analyze_scheme_handles_invalid_schemes(array $scheme): void {
        $expected = [
            'is_valid' => false,
            'accessibility' => [],
            'harmony' => [],
            'coverage' => [],
            'improvements' => ['Invalid color scheme structure']
        ];

        $this->generator
            ->expects($this->once())
            ->method('analyze_scheme')
            ->with($scheme)
            ->willReturn($expected);

        $result = $this->generator->analyze_scheme($scheme);
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['improvements']);
    }

    public function invalidSchemeProvider(): array {
        return [
            'empty_scheme' => [[]],
            'missing_primary' => [['secondary' => '#00FF00']],
            'invalid_colors' => [['primary' => 'invalid']],
            'incomplete_scheme' => [['primary' => '#FF0000']]
        ];
    }
} 
