<?php
/**
 * Business Analyzer Interface Tests
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\BusinessAnalyzer;

class BusinessAnalyzerTest extends TestCase {
    private $analyzer;

    public function setUp(): void {
        $this->analyzer = $this->createMock(BusinessAnalyzer::class);
    }

    public function test_analyze_brand_compatibility_returns_valid_array(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $brand_info = [
            'industry' => 'technology',
            'target_audience' => 'professionals',
            'brand_values' => ['innovation', 'trust', 'reliability']
        ];
        $expected = [
            'compatibility_score' => 0.85,
            'strengths' => [
                'aligns with tech industry standards',
                'professional appearance',
                'conveys innovation'
            ],
            'weaknesses' => [
                'may be too bold for conservative clients'
            ],
            'recommendations' => [
                'consider adding a neutral color',
                'reduce saturation for better balance'
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_brand_compatibility')
            ->with($palette, $brand_info)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_brand_compatibility($palette, $brand_info);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('compatibility_score', $result);
        $this->assertArrayHasKey('strengths', $result);
        $this->assertArrayHasKey('weaknesses', $result);
        $this->assertArrayHasKey('recommendations', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_get_industry_guidelines_returns_valid_array(): void {
        // Arrange
        $industry = 'technology';
        $expected = [
            'recommended_colors' => [
                'primary' => ['#0066CC', '#00AA88'],
                'secondary' => ['#FF6600', '#FFCC00'],
                'accent' => ['#FF3366', '#9933CC']
            ],
            'color_ratios' => [
                'primary' => 0.6,
                'secondary' => 0.3,
                'accent' => 0.1
            ],
            'avoid_colors' => ['#FF0000', '#FF00FF'],
            'special_considerations' => [
                'ensure high contrast for readability',
                'consider color blindness accessibility'
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('get_industry_guidelines')
            ->with($industry)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->get_industry_guidelines($industry);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('recommended_colors', $result);
        $this->assertArrayHasKey('color_ratios', $result);
        $this->assertArrayHasKey('avoid_colors', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_generate_usage_guidelines_returns_valid_array(): void {
        // Arrange
        $palette = ['#FF0000', '#00FF00', '#0000FF'];
        $context = [
            'platform' => 'web',
            'use_cases' => ['branding', 'marketing']
        ];
        $expected = [
            'primary_color_usage' => [
                'main_brand_color' => '#FF0000',
                'recommended_uses' => ['logo', 'headers', 'buttons'],
                'usage_ratio' => '60%'
            ],
            'secondary_colors' => [
                [
                    'color' => '#00FF00',
                    'recommended_uses' => ['accents', 'icons'],
                    'usage_ratio' => '30%'
                ],
                [
                    'color' => '#0000FF',
                    'recommended_uses' => ['highlights', 'links'],
                    'usage_ratio' => '10%'
                ]
            ],
            'combinations' => [
                'recommended' => [
                    ['#FF0000', '#00FF00'],
                    ['#FF0000', '#0000FF']
                ],
                'avoid' => [
                    ['#00FF00', '#0000FF']
                ]
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('generate_usage_guidelines')
            ->with($palette, $context)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->generate_usage_guidelines($palette, $context);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('primary_color_usage', $result);
        $this->assertArrayHasKey('secondary_colors', $result);
        $this->assertArrayHasKey('combinations', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_analyze_brand_compatibility_throws_exception_for_invalid_palette(array $palette): void {
        $this->analyzer
            ->expects($this->once())
            ->method('analyze_brand_compatibility')
            ->with($palette, [])
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->analyze_brand_compatibility($palette, []);
    }

    /**
     * @dataProvider invalidIndustryProvider
     */
    public function test_get_industry_guidelines_throws_exception_for_invalid_industry(string $industry): void {
        $this->analyzer
            ->expects($this->once())
            ->method('get_industry_guidelines')
            ->with($industry)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->get_industry_guidelines($industry);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty array' => [[]],
            'invalid colors' => [['not-a-color', '#FF0000']],
            'too many colors' => [array_fill(0, 11, '#FF0000')],
            'duplicate colors' => [['#FF0000', '#FF0000']],
            'mixed formats' => [['#FF0000', 'rgb(0,255,0)']]
        ];
    }

    public function invalidIndustryProvider(): array {
        return [
            'empty string' => [''],
            'invalid industry' => ['not-a-real-industry'],
            'numeric value' => ['123'],
            'special characters' => ['tech@industry'],
            'too long' => [str_repeat('a', 256)]
        ];
    }
}
