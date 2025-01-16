<?php
/**
 * Tests for Color Palette Analyzer Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Analyzer;

/**
 * Test Color Palette Analyzer Interface implementation
 */
class Test_Color_Palette_Analyzer extends Unit_Test_Case {
    /** @var Color_Palette_Analyzer */
    private $analyzer;

    public function setUp(): void {
        parent::setUp();
        $this->analyzer = $this->getMockBuilder(Color_Palette_Analyzer::class)
                              ->getMockForAbstractClass();
    }

    public function test_analyze_harmony_evaluates_relationships(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $options = [
            'schemes' => ['complementary', 'triadic'],
            'thresholds' => ['harmony' => 0.8]
        ];

        $expected = [
            'relationships' => [
                'complementary' => [
                    'score' => 0.9,
                    'pairs' => [['#FF0000', '#00FF00']]
                ],
                'triadic' => [
                    'score' => 0.95,
                    'groups' => [['#FF0000', '#00FF00', '#0000FF']]
                ]
            ],
            'scores' => [
                'overall' => 0.92,
                'schemes' => ['complementary' => 0.9, 'triadic' => 0.95]
            ],
            'suggestions' => [
                ['type' => 'adjustment', 'message' => 'Slight adjustment to green could improve harmony']
            ],
            'metadata' => [
                'analyzed_at' => '2024-01-20T12:00:00Z',
                'schemes_analyzed' => ['complementary', 'triadic']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_harmony($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_analyze_contrast_evaluates_ratios(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FFFFFF', '#000000']
        ];

        $options = [
            'ratios' => ['min' => 4.5],
            'standards' => ['wcag' => 'AA']
        ];

        $expected = [
            'ratios' => [
                ['colors' => ['#FFFFFF', '#000000'], 'ratio' => 21],
                'summary' => ['min' => 21, 'max' => 21, 'avg' => 21]
            ],
            'compliance' => [
                'wcag_aa' => true,
                'wcag_aaa' => true
            ],
            'suggestions' => [],
            'metadata' => [
                'analyzed_at' => '2024-01-20T12:00:00Z',
                'standards' => ['WCAG 2.1 AA']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_contrast')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_contrast($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('ratios', $result);
        $this->assertArrayHasKey('compliance', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_analyze_distribution_evaluates_balance(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $options = [
            'metrics' => ['hue', 'saturation', 'lightness'],
            'ranges' => ['saturation' => [0, 100]]
        ];

        $expected = [
            'metrics' => [
                'hue' => ['spread' => 120, 'variance' => 60],
                'saturation' => ['avg' => 100, 'range' => 0],
                'lightness' => ['avg' => 50, 'range' => 0]
            ],
            'balance' => [
                'hue' => 'well_distributed',
                'saturation' => 'uniform',
                'lightness' => 'uniform'
            ],
            'suggestions' => [
                ['type' => 'variety', 'message' => 'Consider adding lighter tones']
            ],
            'metadata' => [
                'analyzed_at' => '2024-01-20T12:00:00Z',
                'metrics' => ['hue', 'saturation', 'lightness']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_distribution')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_distribution($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('balance', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_analyze_psychology_evaluates_meanings(): void {
        // Arrange
        $palette = [
            'name' => 'Test Palette',
            'colors' => ['#FF0000', '#0000FF']
        ];

        $options = [
            'associations' => ['general', 'cultural'],
            'context' => ['web', 'business']
        ];

        $expected = [
            'meanings' => [
                '#FF0000' => ['energy', 'passion', 'urgency'],
                '#0000FF' => ['trust', 'stability', 'professionalism']
            ],
            'emotions' => [
                'primary' => ['excitement', 'confidence'],
                'secondary' => ['trust', 'reliability']
            ],
            'suggestions' => [
                ['type' => 'context', 'message' => 'Well-suited for business applications']
            ],
            'metadata' => [
                'analyzed_at' => '2024-01-20T12:00:00Z',
                'context' => ['web', 'business']
            ]
        ];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_psychology')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->analyzer->analyze_psychology($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('meanings', $result);
        $this->assertArrayHasKey('emotions', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * @dataProvider invalidPaletteProvider
     */
    public function test_analyze_harmony_validates_palette(array $palette): void {
        $this->analyzer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($palette)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->analyze_harmony($palette);
    }

    public function invalidPaletteProvider(): array {
        return [
            'empty_palette' => [[]],
            'missing_colors' => [['name' => 'Test']],
            'invalid_colors' => [['colors' => ['not-a-color']]],
            'single_color' => [['colors' => ['#FF0000']]]
        ];
    }

    /**
     * @dataProvider invalidContrastOptionsProvider
     */
    public function test_analyze_contrast_validates_options(array $options): void {
        $palette = ['colors' => ['#FF0000', '#FFFFFF']];

        $this->analyzer
            ->expects($this->once())
            ->method('analyze_contrast')
            ->with($palette, $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->analyzer->analyze_contrast($palette, $options);
    }

    public function invalidContrastOptionsProvider(): array {
        return [
            'invalid_ratio' => [['ratios' => ['min' => -1]]],
            'invalid_standard' => [['standards' => ['invalid']]],
            'invalid_type' => [['ratios' => 'not-array']],
            'empty_options' => [[]]
        ];
    }
}
