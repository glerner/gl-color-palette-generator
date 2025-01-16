/**
 * Color Harmonizer Interface Tests
 *
 * Tests for color harmony generation, including complementary, analogous,
 * triadic, and other color relationships. Validates harmony scores,
 * angle calculations, and relationship strengths.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 * @version 1.1.0
 * @author GL Color Palette Generator
 * @copyright 2024 GL Color Palette Generator
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Interfaces\ColorHarmonizer;

class ColorHarmonizerTest extends TestCase {
    private $harmonizer;

    public function setUp(): void {
        $this->harmonizer = $this->createMock(ColorHarmonizer::class);
    }

    /**
     * Test generation of color harmonies with various schemes
     *
     * @return void
     */
    public function test_generate_harmony_returns_valid_structure(): void {
        // Arrange
        $base_color = '#FF0000';
        $harmony_type = 'complementary';
        $options = [
            'count' => 4,
            'include_base' => true,
            'generated_at' => '2024-12-08T19:52:31-07:00'
        ];

        $expected = [
            'colors' => ['#FF0000', '#00FFFF', '#FF3333', '#00CCCC'],
            'relationships' => [
                'base' => '#FF0000',
                'complement' => '#00FFFF',
                'variants' => ['#FF3333', '#00CCCC']
            ],
            'angles' => [0, 180, 15, 195],
            'harmony_score' => 0.95,
            'metadata' => [
                'generated_at' => '2024-12-08T19:52:31-07:00',
                'scheme' => 'complementary',
                'version' => '1.1.0'
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('generate_harmony')
            ->with($base_color, $harmony_type, $options)
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->generate_harmony($base_color, $harmony_type, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('angles', $result);
        $this->assertArrayHasKey('harmony_score', $result);
        $this->assertIsFloat($result['harmony_score']);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('2024-12-08T19:52:31-07:00', $result['metadata']['generated_at']);
    }

    /**
     * Test advanced harmony calculations with custom angles
     *
     * @return void
     */
    public function test_calculate_advanced_harmony(): void {
        $test_cases = [
            [
                'input' => [
                    'base_color' => '#FF0000',
                    'angles' => [30, 60, 90],
                    'weights' => [1.0, 0.8, 0.6]
                ],
                'expected' => [
                    'colors' => ['#FF0000', '#FF8000', '#FFB300', '#FFE600'],
                    'harmony_score' => 0.92,
                    'balance_score' => 0.88,
                    'contrast_ratio' => 3.5
                ]
            ],
            [
                'input' => [
                    'base_color' => '#0000FF',
                    'angles' => [120, 240],
                    'weights' => [1.0, 1.0]
                ],
                'expected' => [
                    'colors' => ['#0000FF', '#FF0000', '#00FF00'],
                    'harmony_score' => 0.95,
                    'balance_score' => 0.90,
                    'contrast_ratio' => 4.2
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->harmonizer
                ->expects($this->once())
                ->method('calculate_advanced_harmony')
                ->with(
                    $case['input']['base_color'],
                    $case['input']['angles'],
                    $case['input']['weights']
                )
                ->willReturn($case['expected']);

            $result = $this->harmonizer->calculate_advanced_harmony(
                $case['input']['base_color'],
                $case['input']['angles'],
                $case['input']['weights']
            );

            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test harmony validation with accessibility requirements
     *
     * @return void
     */
    public function test_validate_harmony_accessibility(): void {
        $test_cases = [
            [
                'input' => [
                    'colors' => ['#FFFFFF', '#000000'],
                    'requirements' => [
                        'min_contrast' => 4.5,
                        'wcag_level' => 'AA'
                    ]
                ],
                'expected' => [
                    'valid' => true,
                    'contrast_ratios' => [
                        ['#FFFFFF', '#000000', 21.0]
                    ],
                    'wcag_compliance' => [
                        'level' => 'AAA',
                        'passed' => true
                    ]
                ]
            ],
            [
                'input' => [
                    'colors' => ['#FF0000', '#FF8800'],
                    'requirements' => [
                        'min_contrast' => 4.5,
                        'wcag_level' => 'AA'
                    ]
                ],
                'expected' => [
                    'valid' => false,
                    'contrast_ratios' => [
                        ['#FF0000', '#FF8800', 1.8]
                    ],
                    'wcag_compliance' => [
                        'level' => 'AA',
                        'passed' => false,
                        'reason' => 'insufficient_contrast'
                    ]
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->harmonizer
                ->expects($this->once())
                ->method('validate_harmony_accessibility')
                ->with($case['input']['colors'], $case['input']['requirements'])
                ->willReturn($case['expected']);

            $result = $this->harmonizer->validate_harmony_accessibility(
                $case['input']['colors'],
                $case['input']['requirements']
            );

            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_analyze_harmony_returns_detailed_analysis(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00', '#0000FF'];
        $expected = [
            'harmony_type' => 'triadic',
            'harmony_score' => 0.92,
            'relationships' => [
                ['type' => 'triadic', 'colors' => ['#FF0000', '#00FF00', '#0000FF']]
            ],
            'improvements' => [
                'Consider adjusting saturation for better balance'
            ],
            'metrics' => [
                'hue_spacing' => 0.95,
                'value_balance' => 0.88,
                'saturation_coherence' => 0.93
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($colors)
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->analyze_harmony($colors);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('harmony_type', $result);
        $this->assertArrayHasKey('harmony_score', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertIsFloat($result['harmony_score']);
    }

    public function test_suggest_improvements_returns_valid_suggestions(): void {
        // Arrange
        $colors = ['#FF0000', '#00FF00'];
        $target_harmony = 'complementary';
        $expected = [
            'adjustments' => [
                ['color' => '#FF0000', 'suggestion' => 'No change needed'],
                ['color' => '#00FF00', 'suggestion' => 'Adjust to #00FFFF']
            ],
            'alternatives' => [
                ['#FF0000', '#00FFFF'],
                ['#FF3333', '#00CCCC']
            ],
            'explanation' => 'Adjusting second color to true complement',
            'impact_score' => 0.85
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('suggest_improvements')
            ->with($colors, $target_harmony)
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->suggest_improvements($colors, $target_harmony);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('adjustments', $result);
        $this->assertArrayHasKey('alternatives', $result);
        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('impact_score', $result);
        $this->assertIsFloat($result['impact_score']);
    }

    public function test_get_harmony_types_returns_complete_list(): void {
        // Arrange
        $expected = [
            'complementary' => [
                'name' => 'Complementary',
                'description' => 'Colors opposite each other on the color wheel',
                'angles' => [0, 180],
                'use_cases' => ['High contrast designs', 'Call to action']
            ],
            'analogous' => [
                'name' => 'Analogous',
                'description' => 'Colors adjacent to each other',
                'angles' => [0, 30, 60],
                'use_cases' => ['Natural, harmonious designs']
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('get_harmony_types')
            ->willReturn($expected);

        // Act
        $result = $this->harmonizer->get_harmony_types();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('complementary', $result);
        $this->assertArrayHasKey('analogous', $result);
        $this->assertArrayHasKey('use_cases', $result['complementary']);
    }

    /**
     * @dataProvider invalidHarmonyTypeProvider
     */
    public function test_generate_harmony_throws_exception_for_invalid_type(
        string $base_color,
        string $harmony_type
    ): void {
        $this->harmonizer
            ->expects($this->once())
            ->method('generate_harmony')
            ->with($base_color, $harmony_type)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->harmonizer->generate_harmony($base_color, $harmony_type);
    }

    public function invalidHarmonyTypeProvider(): array {
        return [
            'empty_type' => ['#FF0000', ''],
            'invalid_type' => ['#FF0000', 'invalid'],
            'invalid_color' => ['invalid', 'complementary']
        ];
    }

    /**
     * @dataProvider invalidColorSetProvider
     */
    public function test_analyze_harmony_handles_invalid_color_sets(array $colors): void {
        $expected = [
            'harmony_type' => 'unknown',
            'harmony_score' => 0.0,
            'relationships' => [],
            'improvements' => ['Invalid color set'],
            'metrics' => [
                'hue_spacing' => 0.0,
                'value_balance' => 0.0,
                'saturation_coherence' => 0.0
            ]
        ];

        $this->harmonizer
            ->expects($this->once())
            ->method('analyze_harmony')
            ->with($colors)
            ->willReturn($expected);

        $result = $this->harmonizer->analyze_harmony($colors);
        $this->assertEquals(0.0, $result['harmony_score']);
        $this->assertNotEmpty($result['improvements']);
    }

    public function invalidColorSetProvider(): array {
        return [
            'empty_set' => [[]],
            'single_color' => [['#FF0000']],
            'invalid_colors' => [['invalid', '#FF0000']],
            'too_many_colors' => [array_fill(0, 10, '#FF0000')]
        ];
    }
} 
