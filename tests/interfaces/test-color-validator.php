<?php

namespace GLColorPalette\Tests\Interfaces;

use PHPUnit\Framework\TestCase;
use GLColorPalette\Interfaces\ColorValidator;

class ColorValidatorTest extends TestCase {
    private $validator;

    public function setUp(): void {
        $this->validator = $this->createMock(ColorValidator::class);
    }

    public function test_validate_color_returns_valid_structure(): void {
        // Test cases for different color formats and validations
        $test_cases = [
            [
                'color' => '#FF0000',
                'format' => 'hex',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'hex',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => '#F00',
                'format' => 'hex',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'hex',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => 'rgb(255,0,0)',
                'format' => 'rgb',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'rgb',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => 'rgba(255,0,0,0.5)',
                'format' => 'rgba',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'rgba',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'rgba' => [255, 0, 0, 0.5],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->validator
                ->expects($this->once())
                ->method('validate_color')
                ->with($case['color'], $case['format'])
                ->willReturn($case['expected']);

            $result = $this->validator->validate_color($case['color'], $case['format']);
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('is_valid', $result);
            $this->assertArrayHasKey('format', $result);
            $this->assertArrayHasKey('normalized', $result);
            $this->assertArrayHasKey('errors', $result);
            $this->assertIsBool($result['is_valid']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_validate_combination_returns_analysis(): void {
        // Test cases for different color combinations
        $test_cases = [
            [
                'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                'rules' => [
                    'min_contrast' => 4.5,
                    'harmony_type' => 'triadic'
                ],
                'expected' => [
                    'passes_rules' => true,
                    'harmony_scores' => [
                        'overall' => 0.95,
                        'balance' => 0.90,
                        'spacing' => 1.0
                    ],
                    'contrast_ratios' => [
                        ['colors' => ['#FF0000', '#00FF00'], 'ratio' => 5.2],
                        ['colors' => ['#FF0000', '#0000FF'], 'ratio' => 4.8],
                        ['colors' => ['#00FF00', '#0000FF'], 'ratio' => 5.0]
                    ],
                    'violations' => []
                ]
            ],
            [
                'colors' => ['#FF0000', '#FF5500', '#FFAA00'],
                'rules' => [
                    'min_contrast' => 3.0,
                    'harmony_type' => 'analogous'
                ],
                'expected' => [
                    'passes_rules' => true,
                    'harmony_scores' => [
                        'overall' => 0.88,
                        'balance' => 0.85,
                        'spacing' => 0.92
                    ],
                    'contrast_ratios' => [
                        ['colors' => ['#FF0000', '#FF5500'], 'ratio' => 3.2],
                        ['colors' => ['#FF5500', '#FFAA00'], 'ratio' => 3.1]
                    ],
                    'violations' => []
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->validator
                ->expects($this->once())
                ->method('validate_combination')
                ->with($case['colors'], $case['rules'])
                ->willReturn($case['expected']);

            $result = $this->validator->validate_combination($case['colors'], $case['rules']);
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('passes_rules', $result);
            $this->assertArrayHasKey('harmony_scores', $result);
            $this->assertArrayHasKey('contrast_ratios', $result);
            $this->assertIsBool($result['passes_rules']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_get_color_info_returns_detailed_information(): void {
        // Arrange
        $color = '#FF0000';
        $expected = [
            'formats' => [
                'hex' => '#FF0000',
                'rgb' => 'rgb(255, 0, 0)',
                'hsl' => 'hsl(0, 100%, 50%)'
            ],
            'properties' => [
                'brightness' => 0.5,
                'saturation' => 1.0,
                'temperature' => 'warm'
            ],
            'name' => 'Pure Red',
            'metadata' => [
                'category' => 'primary',
                'psychology' => 'energetic'
            ]
        ];

        $this->validator
            ->expects($this->once())
            ->method('get_color_info')
            ->with($color)
            ->willReturn($expected);

        // Act
        $result = $this->validator->get_color_info($color);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('formats', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_validate_against_guidelines_checks_compliance(): void {
        // Arrange
        $color = '#FF0000';
        $guidelines = [
            'allowed_colors' => ['#FF0000', '#00FF00'],
            'allowed_ranges' => [
                'hue' => [0, 360],
                'saturation' => [80, 100],
                'lightness' => [45, 55]
            ],
            'tolerance' => 0.05
        ];

        $expected = [
            'compliant' => true,
            'matches' => ['#FF0000'],
            'suggestions' => [],
            'violations' => []
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_against_guidelines')
            ->with($color, $guidelines)
            ->willReturn($expected);

        // Act
        $result = $this->validator->validate_against_guidelines($color, $guidelines);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('compliant', $result);
        $this->assertArrayHasKey('matches', $result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertArrayHasKey('violations', $result);
        $this->assertIsBool($result['compliant']);
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_validate_color_identifies_invalid_colors(string $color): void {
        $expected = [
            'is_valid' => false,
            'format' => null,
            'normalized' => [],
            'errors' => ['Invalid color format']
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_color')
            ->with($color)
            ->willReturn($expected);

        $result = $this->validator->validate_color($color);
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function invalidColorProvider(): array {
        return [
            'empty_string' => [''],
            'invalid_hex' => ['#GG0000'],
            'wrong_format' => ['rgb(256,0,0)'],
            'malformed' => ['#FF00'],
            'no_hash' => ['FF0000'],
            'invalid_rgb' => ['rgb(300,0,0)'],
            'invalid_rgba' => ['rgba(255,0,0,1.5)'],
            'invalid_hsl' => ['hsl(400,100%,50%)'],
            'invalid_hsla' => ['hsla(0,100%,50%,2)'],
            'missing_units' => ['hsl(0,100,50)'],
            'extra_spaces' => ['rgb( 255, 0, 0 )'],
            'invalid_chars' => ['#FF00XX'],
            'mixed_format' => ['rgb(#FF,0,0)'],
            'incomplete_rgba' => ['rgba(255,0,0)'],
            'negative_values' => ['rgb(-1,0,0)']
        ];
    }

    public function test_validate_combination_identifies_invalid_combinations(
        array $colors,
        array $rules
    ): void {
        $expected = [
            'passes_rules' => false,
            'harmony_scores' => [],
            'contrast_ratios' => [],
            'violations' => ['Insufficient contrast ratio']
        ];

        $this->validator
            ->expects($this->once())
            ->method('validate_combination')
            ->with($colors, $rules)
            ->willReturn($expected);

        $result = $this->validator->validate_combination($colors, $rules);
        $this->assertFalse($result['passes_rules']);
        $this->assertNotEmpty($result['violations']);
    }

    public function invalidCombinationProvider(): array {
        return [
            'low_contrast' => [
                ['#FFFFFF', '#FEFEFE'],
                ['min_contrast' => 4.5]
            ],
            'wrong_harmony' => [
                ['#FF0000', '#00FF00'],
                ['harmony_type' => 'analogous']
            ],
            'too_many_colors' => [
                ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF'],
                ['max_colors' => 5]
            ],
            'insufficient_colors' => [
                ['#FF0000'],
                ['harmony_type' => 'complementary']
            ],
            'invalid_spacing' => [
                ['#FF0000', '#FF0100', '#FF0200'],
                ['min_spacing' => 30]
            ],
            'unbalanced_colors' => [
                ['#FF0000', '#FF0001', '#000000'],
                ['balance_threshold' => 0.8]
            ]
        ];
    }

    public function test_validate_color_formats_returns_valid_structure(): void {
        // Test various color format validations
        $test_cases = [
            [
                'color' => 'rgb(255, 0, 0)',
                'format' => 'rgb',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'rgb',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => 'hsl(0, 100%, 50%)',
                'format' => 'hsl',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'hsl',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => 'rgba(255, 0, 0, 0.5)',
                'format' => 'rgba',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'rgba',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'rgba' => [255, 0, 0, 0.5],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => 'hsla(0, 100%, 50%, 0.5)',
                'format' => 'hsla',
                'expected' => [
                    'is_valid' => true,
                    'format' => 'hsla',
                    'normalized' => [
                        'hex' => '#FF0000',
                        'rgb' => [255, 0, 0],
                        'hsla' => [0, 100, 50, 0.5],
                        'hsl' => [0, 100, 50]
                    ],
                    'errors' => []
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->validator
                ->expects($this->once())
                ->method('validate_color')
                ->with($case['color'], $case['format'])
                ->willReturn($case['expected']);

            $result = $this->validator->validate_color($case['color'], $case['format']);
            $this->assertEquals($case['expected'], $result);
            $this->assertTrue($result['is_valid']);
            $this->assertArrayHasKey('normalized', $result);
        }
    }

    public function test_validate_color_ranges(): void {
        // Test color range validations
        $test_cases = [
            [
                'color' => '#FF0000',
                'ranges' => [
                    'rgb' => [
                        'red' => [0, 255],
                        'green' => [0, 255],
                        'blue' => [0, 255]
                    ]
                ],
                'expected' => [
                    'is_valid' => true,
                    'in_range' => true,
                    'violations' => []
                ]
            ],
            [
                'color' => 'hsl(180, 100%, 50%)',
                'ranges' => [
                    'hsl' => [
                        'hue' => [0, 360],
                        'saturation' => [0, 100],
                        'lightness' => [0, 100]
                    ]
                ],
                'expected' => [
                    'is_valid' => true,
                    'in_range' => true,
                    'violations' => []
                ]
            ],
            [
                'color' => 'rgb(300, 0, 0)',
                'ranges' => [
                    'rgb' => [
                        'red' => [0, 255],
                        'green' => [0, 255],
                        'blue' => [0, 255]
                    ]
                ],
                'expected' => [
                    'is_valid' => false,
                    'in_range' => false,
                    'violations' => ['Red value exceeds maximum of 255']
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->validator
                ->expects($this->once())
                ->method('validate_color_ranges')
                ->with($case['color'], $case['ranges'])
                ->willReturn($case['expected']);

            $result = $this->validator->validate_color_ranges($case['color'], $case['ranges']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_validate_specific_guidelines(): void {
        // Test specific guideline validations
        $test_cases = [
            [
                'color' => '#FF0000',
                'guidelines' => [
                    'brand_colors' => ['#FF0000', '#00FF00', '#0000FF'],
                    'allowed_variations' => 0.1,
                    'requirements' => [
                        'minimum_contrast' => 4.5,
                        'color_blindness_safe' => true
                    ]
                ],
                'expected' => [
                    'compliant' => true,
                    'matches' => ['#FF0000'],
                    'contrast_compliance' => true,
                    'accessibility' => [
                        'color_blindness_safe' => true,
                        'wcag_aa_compliant' => true
                    ],
                    'violations' => []
                ]
            ],
            [
                'color' => '#FF5500',
                'guidelines' => [
                    'material_design' => true,
                    'color_system' => 'material',
                    'requirements' => [
                        'primary_colors' => true,
                        'accent_colors' => false
                    ]
                ],
                'expected' => [
                    'compliant' => true,
                    'system' => 'material',
                    'category' => 'primary',
                    'variations' => [
                        'light' => ['50', '100', '200'],
                        'dark' => ['700', '800', '900']
                    ],
                    'violations' => []
                ]
            ],
            [
                'colors' => ['#FF0000', '#FE0000'],
                'guidelines' => [
                    'minimum_distinction' => 0.2,
                    'maximum_colors' => 5,
                    'requirements' => [
                        'harmony_type' => 'analogous',
                        'balance_score' => 0.8
                    ]
                ],
                'expected' => [
                    'compliant' => false,
                    'distinction_score' => 0.1,
                    'harmony_score' => 0.95,
                    'balance_score' => 0.85,
                    'violations' => ['Colors are too similar']
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->validator
                ->expects($this->once())
                ->method('validate_specific_guidelines')
                ->with(
                    isset($case['colors']) ? $case['colors'] : $case['color'],
                    $case['guidelines']
                )
                ->willReturn($case['expected']);

            $result = $this->validator->validate_specific_guidelines(
                isset($case['colors']) ? $case['colors'] : $case['color'],
                $case['guidelines']
            );
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_validate_color_format_compatibility(): void {
        // Test color format compatibility validations
        $test_cases = [
            [
                'color' => '#FF0000',
                'target_formats' => ['rgb', 'hsl', 'cmyk'],
                'expected' => [
                    'compatible' => true,
                    'conversions' => [
                        'rgb' => 'rgb(255, 0, 0)',
                        'hsl' => 'hsl(0, 100%, 50%)',
                        'cmyk' => 'cmyk(0%, 100%, 100%, 0%)'
                    ],
                    'errors' => []
                ]
            ],
            [
                'color' => 'rgb(255, 0, 0)',
                'target_formats' => ['hex', 'hsl', 'hsv'],
                'expected' => [
                    'compatible' => true,
                    'conversions' => [
                        'hex' => '#FF0000',
                        'hsl' => 'hsl(0, 100%, 50%)',
                        'hsv' => 'hsv(0, 100%, 100%)'
                    ],
                    'errors' => []
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->validator
                ->expects($this->once())
                ->method('validate_color_format_compatibility')
                ->with($case['color'], $case['target_formats'])
                ->willReturn($case['expected']);

            $result = $this->validator->validate_color_format_compatibility(
                $case['color'],
                $case['target_formats']
            );
            $this->assertEquals($case['expected'], $result);
        }
    }
}
