<?php
/**
 * Color Accessibility Interface Tests
 *
 * Tests for color accessibility features including WCAG compliance checking,
 * contrast ratio calculations, color blindness simulations, and accessible
 * color suggestions.
 *
 * @package GLColorPalette
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 * @version 1.1.0
 * @author GL Color Palette Generator
 * @copyright 2024 GL Color Palette Generator
 */

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorAccessibility;

class ColorAccessibilityTest extends TestCase {
    private $accessibility;

    public function setUp(): void {
        $this->accessibility = $this->createMock(ColorAccessibility::class);
    }

    public function test_check_wcag_compliance_returns_array(): void {
        // Arrange
        $test_cases = [
            [
                'foreground' => '#000000',
                'background' => '#FFFFFF',
                'level' => 'AAA',
                'expected_ratio' => 21.0
            ],
            [
                'foreground' => '#757575',
                'background' => '#FFFFFF',
                'level' => 'AA',
                'expected_ratio' => 4.6
            ],
            [
                'foreground' => '#FF0000',
                'background' => '#FFFFFF',
                'level' => 'AA',
                'expected_ratio' => 4.0
            ]
        ];

        foreach ($test_cases as $case) {
            $expected = [
                'compliant' => $case['expected_ratio'] >= ($case['level'] === 'AAA' ? 7.0 : 4.5),
                'contrast_ratio' => $case['expected_ratio'],
                'requirements' => [
                    'normal_text' => $case['expected_ratio'] >= ($case['level'] === 'AAA' ? 7.0 : 4.5),
                    'large_text' => $case['expected_ratio'] >= ($case['level'] === 'AAA' ? 4.5 : 3.0),
                    'ui_components' => $case['expected_ratio'] >= 3.0
                ],
                'minimum_requirements' => [
                    'normal_text' => $case['level'] === 'AAA' ? 7.0 : 4.5,
                    'large_text' => $case['level'] === 'AAA' ? 4.5 : 3.0,
                    'ui_components' => 3.0
                ]
            ];

            $this->accessibility
                ->expects($this->once())
                ->method('check_wcag_compliance')
                ->with($case['foreground'], $case['background'], $case['level'])
                ->willReturn($expected);

            // Act
            $result = $this->accessibility->check_wcag_compliance(
                $case['foreground'],
                $case['background'],
                $case['level']
            );

            // Assert
            $this->assertIsArray($result);
            $this->assertArrayHasKey('compliant', $result);
            $this->assertArrayHasKey('contrast_ratio', $result);
            $this->assertArrayHasKey('requirements', $result);
            $this->assertArrayHasKey('minimum_requirements', $result);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_get_minimum_contrast_ratio_returns_float(): void {
        // Arrange
        $level = 'AA';
        $context = 'normal_text';
        $expected = 4.5;

        $this->accessibility
            ->expects($this->once())
            ->method('get_minimum_contrast_ratio')
            ->with($level, $context)
            ->willReturn($expected);

        // Act
        $result = $this->accessibility->get_minimum_contrast_ratio($level, $context);

        // Assert
        $this->assertIsFloat($result);
        $this->assertEquals($expected, $result);
    }

    public function test_suggest_accessible_colors_returns_array(): void {
        // Test cases for different scenarios
        $test_cases = [
            [
                'base_color' => '#FF0000',
                'target_ratio' => 4.5,
                'preferences' => [
                    'preserve' => 'background',
                    'maintain_hue' => true
                ],
                'expected' => [
                    'suggestions' => [
                        ['color' => '#FFFFFF', 'contrast_ratio' => 4.8],
                        ['color' => '#F0F0F0', 'contrast_ratio' => 4.6],
                        ['color' => '#E0E0E0', 'contrast_ratio' => 4.5]
                    ],
                    'original_ratio' => 2.5,
                    'target_achieved' => true
                ]
            ],
            [
                'base_color' => '#808080',
                'target_ratio' => 7.0,
                'preferences' => [
                    'preserve' => 'foreground',
                    'maintain_hue' => false
                ],
                'expected' => [
                    'suggestions' => [
                        ['color' => '#000000', 'contrast_ratio' => 7.2],
                        ['color' => '#101010', 'contrast_ratio' => 7.1]
                    ],
                    'original_ratio' => 3.5,
                    'target_achieved' => true
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->accessibility
                ->expects($this->once())
                ->method('suggest_accessible_colors')
                ->with($case['base_color'], $case['target_ratio'], $case['preferences'])
                ->willReturn($case['expected']);

            // Act
            $result = $this->accessibility->suggest_accessible_colors(
                $case['base_color'],
                $case['target_ratio'],
                $case['preferences']
            );

            // Assert
            $this->assertIsArray($result);
            $this->assertArrayHasKey('suggestions', $result);
            $this->assertArrayHasKey('original_ratio', $result);
            $this->assertArrayHasKey('target_achieved', $result);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_analyze_color_blindness_returns_array(): void {
        // Test cases for different color blindness types
        $test_cases = [
            [
                'color' => '#FF0000',
                'types' => ['protanopia', 'deuteranopia', 'tritanopia'],
                'expected' => [
                    'original' => '#FF0000',
                    'simulations' => [
                        'protanopia' => '#A09300',
                        'deuteranopia' => '#9B9600',
                        'tritanopia' => '#FFB7B7'
                    ],
                    'distinguishable' => [
                        'protanopia' => false,
                        'deuteranopia' => false,
                        'tritanopia' => true
                    ],
                    'impact_scores' => [
                        'protanopia' => 0.85,
                        'deuteranopia' => 0.82,
                        'tritanopia' => 0.25
                    ]
                ]
            ],
            [
                'color' => '#00FF00',
                'types' => ['protanopia', 'deuteranopia'],
                'expected' => [
                    'original' => '#00FF00',
                    'simulations' => [
                        'protanopia' => '#C6C600',
                        'deuteranopia' => '#D1D100'
                    ],
                    'distinguishable' => [
                        'protanopia' => false,
                        'deuteranopia' => false
                    ],
                    'impact_scores' => [
                        'protanopia' => 0.90,
                        'deuteranopia' => 0.88
                    ]
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->accessibility
                ->expects($this->once())
                ->method('analyze_color_blindness')
                ->with($case['color'], $case['types'])
                ->willReturn($case['expected']);

            // Act
            $result = $this->accessibility->analyze_color_blindness(
                $case['color'],
                $case['types']
            );

            // Assert
            $this->assertIsArray($result);
            $this->assertArrayHasKey('simulations', $result);
            $this->assertArrayHasKey('distinguishable', $result);
            $this->assertArrayHasKey('impact_scores', $result);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_get_readable_text_colors_returns_array(): void {
        // Arrange
        $background = '#FF0000';
        $options = [
            'min_contrast' => 4.5,
            'prefer_dark' => true
        ];
        $expected = [
            'colors' => ['#000000', '#1A1A1A', '#333333'],
            'contrast_ratios' => [5.2, 4.8, 4.5],
            'best_match' => '#000000'
        ];

        $this->accessibility
            ->expects($this->once())
            ->method('get_readable_text_colors')
            ->with($background, $options)
            ->willReturn($expected);

        // Act
        $result = $this->accessibility->get_readable_text_colors($background, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('contrast_ratios', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidColorPairProvider
     */
    public function test_check_wcag_compliance_throws_exception_for_invalid_colors($foreground, $background): void {
        $this->accessibility
            ->expects($this->once())
            ->method('check_wcag_compliance')
            ->with($foreground, $background, 'AA')
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->accessibility->check_wcag_compliance($foreground, $background, 'AA');
    }

    /**
     * @dataProvider invalidWCAGLevelProvider
     */
    public function test_get_minimum_contrast_ratio_throws_exception_for_invalid_level($level): void {
        $this->accessibility
            ->expects($this->once())
            ->method('get_minimum_contrast_ratio')
            ->with($level, 'normal_text')
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->accessibility->get_minimum_contrast_ratio($level, 'normal_text');
    }

    /**
     * @dataProvider invalidColorBlindnessTypesProvider
     */
    public function test_analyze_color_blindness_throws_exception_for_invalid_types($types): void {
        $this->accessibility
            ->expects($this->once())
            ->method('analyze_color_blindness')
            ->with('#FF0000', $types)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->accessibility->analyze_color_blindness('#FF0000', $types);
    }

    public function invalidColorPairProvider(): array {
        return [
            'invalid foreground' => ['invalid', '#FFFFFF'],
            'invalid background' => ['#000000', 'invalid'],
            'both invalid' => ['invalid1', 'invalid2'],
            'null colors' => [null, null],
            'empty strings' => ['', ''],
            'invalid hex length' => ['#FFF', '#FFFFFF'],
            'invalid hex chars' => ['#GGGGGG', '#FFFFFF'],
            'missing hash' => ['000000', 'FFFFFF'],
            'spaces in color' => ['#FF FF FF', '#000000'],
            'rgb format' => ['rgb(255,0,0)', '#FFFFFF']
        ];
    }

    public function invalidWCAGLevelProvider(): array {
        return [
            'empty string' => [''],
            'invalid level' => ['AAAA'],
            'lowercase' => ['aa'],
            'numeric level' => [2],
            'null level' => [null],
            'mixed case' => ['AaA'],
            'with spaces' => [' AA '],
            'special chars' => ['AA+'],
            'non-string type' => [['AA']],
            'boolean type' => [true]
        ];
    }

    public function invalidColorBlindnessTypesProvider(): array {
        return [
            'empty array' => [[]],
            'invalid type' => [['invalid_type']],
            'mixed valid invalid' => [['protanopia', 'invalid']],
            'non-array input' => ['protanopia'],
            'null input' => [null],
            'numeric type' => [[1, 2, 3]],
            'empty string type' => [['']],
            'case sensitive' => [['PROTANOPIA']],
            'with spaces' => [['protanopia ', ' deuteranopia']],
            'duplicate types' => [['protanopia', 'protanopia']]
        ];
    }
}
