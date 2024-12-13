/**
 * Color Palette Generator Interface Tests
 *
 * Tests for color palette generation features including generation from base colors,
 * themes, random generation, and variation creation. Validates various generation
 * options, constraints, and error handling.
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
use GLColorPalette\Interfaces\ColorPaletteGenerator;

class ColorPaletteGeneratorTest extends TestCase {
    private $generator;

    public function setUp(): void {
        $this->generator = $this->createMock(ColorPaletteGenerator::class);
    }

    public function test_generate_from_color_creates_palette(): void {
        // Test cases for different color generation scenarios
        $test_cases = [
            [
                'base_color' => '#FF0000',
                'options' => [
                    'scheme' => 'complementary',
                    'count' => 5,
                    'constraints' => [
                        'saturation' => ['min' => 50, 'max' => 100],
                        'lightness' => ['min' => 30, 'max' => 70]
                    ]
                ],
                'expected' => [
                    'palette' => [
                        'colors' => ['#FF0000', '#00FFFF', '#FF3333', '#00CCCC', '#FF6666']
                    ],
                    'relationships' => [
                        'complementary' => ['#FF0000', '#00FFFF'],
                        'analogous' => ['#FF3333', '#FF6666']
                    ],
                    'metrics' => [
                        'harmony' => 0.95,
                        'contrast' => 0.85,
                        'balance' => 0.90
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:30:44-07:00',
                        'base_color' => '#FF0000',
                        'scheme' => 'complementary'
                    ]
                ]
            ],
            [
                'base_color' => '#1A1A1A',
                'options' => [
                    'scheme' => 'monochromatic',
                    'count' => 3,
                    'constraints' => [
                        'saturation' => ['min' => 0, 'max' => 10],
                        'lightness' => ['min' => 10, 'max' => 90]
                    ]
                ],
                'expected' => [
                    'palette' => [
                        'colors' => ['#1A1A1A', '#4D4D4D', '#808080']
                    ],
                    'relationships' => [
                        'monochromatic' => ['#1A1A1A', '#4D4D4D', '#808080']
                    ],
                    'metrics' => [
                        'harmony' => 1.0,
                        'contrast' => 0.75,
                        'balance' => 0.95
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:30:44-07:00',
                        'base_color' => '#1A1A1A',
                        'scheme' => 'monochromatic'
                    ]
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('generate_from_color')
                ->with($case['base_color'], $case['options'])
                ->willReturn($case['expected']);

            $result = $this->generator->generate_from_color($case['base_color'], $case['options']);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('palette', $result);
            $this->assertArrayHasKey('relationships', $result);
            $this->assertArrayHasKey('metrics', $result);
            $this->assertArrayHasKey('metadata', $result);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_generate_from_theme_creates_palette(): void {
        // Arrange
        $theme = 'ocean';
        $options = [
            'mood' => 'calm',
            'style' => 'modern'
        ];

        $expected = [
            'palette' => [
                'colors' => ['#0077BE', '#00A3E0', '#004C8C', '#80D6FF']
            ],
            'theme' => [
                'name' => 'ocean',
                'mood' => 'calm',
                'style' => 'modern'
            ],
            'variations' => [
                'light' => ['#80D6FF', '#B3E5FF'],
                'dark' => ['#004C8C', '#003366']
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'theme' => 'ocean'
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_from_theme')
            ->with($theme, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_from_theme($theme, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('palette', $result);
        $this->assertArrayHasKey('theme', $result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    public function test_generate_random_creates_palette(): void {
        // Test cases for different random generation scenarios
        $test_cases = [
            [
                'options' => [
                    'count' => 4,
                    'constraints' => [
                        'saturation' => ['min' => 50, 'max' => 100],
                        'lightness' => ['min' => 30, 'max' => 70],
                        'harmony' => 'analogous'
                    ]
                ],
                'expected' => [
                    'palette' => [
                        'colors' => ['#FF3366', '#33CC99', '#9933FF', '#FFCC00']
                    ],
                    'metrics' => [
                        'harmony' => 0.75,
                        'contrast' => 0.80,
                        'balance' => 0.85
                    ],
                    'validation' => [
                        'constraints' => true,
                        'harmony' => true,
                        'spacing' => true
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:30:44-07:00',
                        'method' => 'random',
                        'constraints' => ['saturation', 'lightness', 'harmony']
                    ]
                ]
            ],
            [
                'options' => [
                    'count' => 3,
                    'constraints' => [
                        'hue' => ['min' => 0, 'max' => 60],
                        'saturation' => ['min' => 80, 'max' => 100],
                        'lightness' => ['min' => 40, 'max' => 60],
                        'harmony' => 'triadic'
                    ]
                ],
                'expected' => [
                    'palette' => [
                        'colors' => ['#FF3300', '#FF9900', '#FFCC00']
                    ],
                    'metrics' => [
                        'harmony' => 0.90,
                        'contrast' => 0.70,
                        'balance' => 0.95
                    ],
                    'validation' => [
                        'constraints' => true,
                        'harmony' => true,
                        'spacing' => true
                    ],
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:30:44-07:00',
                        'method' => 'random',
                        'constraints' => ['hue', 'saturation', 'lightness', 'harmony']
                    ]
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('generate_random')
                ->with($case['options'])
                ->willReturn($case['expected']);

            $result = $this->generator->generate_random($case['options']);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('palette', $result);
            $this->assertArrayHasKey('metrics', $result);
            $this->assertArrayHasKey('validation', $result);
            $this->assertArrayHasKey('metadata', $result);
            $this->assertEquals($case['expected'], $result);
        }
    }

    public function test_generate_variations_creates_alternatives(): void {
        // Arrange
        $palette = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF']
        ];

        $options = [
            'types' => ['lighter', 'darker'],
            'count' => 2
        ];

        $expected = [
            'variations' => [
                'lighter' => [
                    ['#FF3333', '#33FF33', '#3333FF'],
                    ['#FF6666', '#66FF66', '#6666FF']
                ],
                'darker' => [
                    ['#CC0000', '#00CC00', '#0000CC'],
                    ['#990000', '#009900', '#000099']
                ]
            ],
            'relationships' => [
                'type' => 'brightness',
                'steps' => [0.2, 0.4]
            ],
            'metrics' => [
                'variance' => 0.2,
                'consistency' => 0.9
            ],
            'metadata' => [
                'generated_at' => '2024-01-20T12:00:00Z',
                'variation_types' => ['lighter', 'darker']
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate_variations')
            ->with($palette, $options)
            ->willReturn($expected);

        // Act
        $result = $this->generator->generate_variations($palette, $options);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * Test color scheme validation
     *
     * @return void
     */
    public function test_validate_color_scheme(): void {
        $test_cases = [
            [
                'scheme' => 'analogous',
                'colors' => ['#FF0000', '#FF3300', '#FF6600'],
                'expected' => true
            ],
            [
                'scheme' => 'triadic',
                'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                'expected' => true
            ],
            [
                'scheme' => 'split-complementary',
                'colors' => ['#FF0000', '#00FF66', '#00FF99'],
                'expected' => true
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('validate_color_scheme')
                ->with($case['scheme'], $case['colors'])
                ->willReturn($case['expected']);

            $result = $this->generator->validate_color_scheme($case['scheme'], $case['colors']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test accessibility requirements
     *
     * @return void
     */
    public function test_check_accessibility(): void {
        $test_cases = [
            [
                'colors' => ['#FFFFFF', '#000000'],
                'requirements' => ['wcag_aa' => true],
                'expected' => true
            ],
            [
                'colors' => ['#FF0000', '#00FF00'],
                'requirements' => ['wcag_aaa' => true],
                'expected' => false
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('check_accessibility')
                ->with($case['colors'], $case['requirements'])
                ->willReturn($case['expected']);

            $result = $this->generator->check_accessibility($case['colors'], $case['requirements']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test palette metadata validation
     *
     * @return void
     */
    public function test_validate_palette_metadata(): void {
        $test_cases = [
            [
                'metadata' => [
                    'name' => 'Test Palette',
                    'description' => 'A test palette',
                    'tags' => ['test', 'colors'],
                    'generated_at' => '2024-12-08T19:37:12-07:00'
                ],
                'expected' => true
            ],
            [
                'metadata' => [
                    'name' => '',
                    'description' => 'Invalid palette',
                    'generated_at' => 'invalid-date'
                ],
                'expected' => false
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('validate_palette_metadata')
                ->with($case['metadata'])
                ->willReturn($case['expected']);

            $result = $this->generator->validate_palette_metadata($case['metadata']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test color space conversion validation
     *
     * @return void
     */
    public function test_validate_color_space_conversion(): void {
        $test_cases = [
            [
                'color' => '#FF0000',
                'space' => 'rgb',
                'expected' => [255, 0, 0]
            ],
            [
                'color' => '#00FF00',
                'space' => 'hsl',
                'expected' => [120, 100, 50]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('convert_color_space')
                ->with($case['color'], $case['space'])
                ->willReturn($case['expected']);

            $result = $this->generator->convert_color_space($case['color'], $case['space']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test concurrent generation requests
     *
     * @return void
     */
    public function test_handle_concurrent_requests(): void {
        $test_cases = [
            [
                'requests' => [
                    ['id' => 1, 'color' => '#FF0000'],
                    ['id' => 2, 'color' => '#00FF00']
                ],
                'expected' => [
                    1 => ['status' => 'success'],
                    2 => ['status' => 'success']
                ]
            ],
            [
                'requests' => [
                    ['id' => 3, 'color' => '#0000FF'],
                    ['id' => 4, 'color' => '#FFFF00'],
                    ['id' => 5, 'color' => '#FF00FF']
                ],
                'expected' => [
                    3 => ['status' => 'success'],
                    4 => ['status' => 'success'],
                    5 => ['status' => 'success']
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('handle_concurrent_requests')
                ->with($case['requests'])
                ->willReturn($case['expected']);

            $result = $this->generator->handle_concurrent_requests($case['requests']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test API failure scenarios
     *
     * @return void
     */
    public function test_handle_api_failures(): void {
        $test_cases = [
            [
                'scenario' => 'timeout',
                'expected' => [
                    'status' => 'error',
                    'message' => 'API request timed out',
                    'retry_after' => 5
                ]
            ],
            [
                'scenario' => 'rate_limit',
                'expected' => [
                    'status' => 'error',
                    'message' => 'Rate limit exceeded',
                    'retry_after' => 60
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('handle_api_failure')
                ->with($case['scenario'])
                ->willReturn($case['expected']);

            $result = $this->generator->handle_api_failure($case['scenario']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test color space boundary conditions
     *
     * @return void
     */
    public function test_color_space_boundaries(): void {
        $test_cases = [
            [
                'color' => [255, 255, 255],
                'space' => 'rgb',
                'expected' => true
            ],
            [
                'color' => [360, 100, 100],
                'space' => 'hsl',
                'expected' => true
            ],
            [
                'color' => [-1, 0, 0],
                'space' => 'rgb',
                'expected' => false
            ],
            [
                'color' => [0, 101, 50],
                'space' => 'hsl',
                'expected' => false
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('validate_color_space_boundaries')
                ->with($case['color'], $case['space'])
                ->willReturn($case['expected']);

            $result = $this->generator->validate_color_space_boundaries($case['color'], $case['space']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test palette naming conventions
     *
     * @return void
     */
    public function test_validate_palette_name(): void {
        $test_cases = [
            [
                'name' => 'My Cool Palette',
                'expected' => true
            ],
            [
                'name' => 'palette_123',
                'expected' => true
            ],
            [
                'name' => '',
                'expected' => false
            ],
            [
                'name' => str_repeat('a', 256),
                'expected' => false
            ],
            [
                'name' => '<script>alert("xss")</script>',
                'expected' => false
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('validate_palette_name')
                ->with($case['name'])
                ->willReturn($case['expected']);

            $result = $this->generator->validate_palette_name($case['name']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * @dataProvider invalidColorProvider
     */
    public function test_generate_from_color_validates_color(string $color): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_from_color')
            ->with($color)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_from_color($color);
    }

    public function invalidColorProvider(): array {
        return [
            'empty_color' => [''],
            'invalid_hex' => ['#XYZ'],
            'wrong_format' => ['rgb(0,0,0)'],
            'no_hash' => ['FF0000'],
            'invalid_length' => ['#FF00'],
            'invalid_chars' => ['#GG0000'],
            'with_spaces' => ['#FF 00 00'],
            'decimal_values' => ['#FF.5500'],
            'uppercase_required' => ['#ff0000'],
            'rgba_format' => ['rgba(255,0,0,0.5)'],
            'hsl_format' => ['hsl(0,100%,50%)'],
            'named_color' => ['red'],
            'null_value' => [null],
            'numeric_value' => [123],
            'special_chars' => ['#FF00@@']
        ];
    }

    /**
     * @dataProvider invalidThemeProvider
     */
    public function test_generate_from_theme_validates_theme(string $theme): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_from_theme')
            ->with($theme)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_from_theme($theme);
    }

    public function invalidThemeProvider(): array {
        return [
            'empty_theme' => [''],
            'invalid_theme' => ['nonexistent'],
            'numeric_theme' => ['123'],
            'special_chars' => ['theme@!'],
            'too_short' => ['a'],
            'too_long' => [str_repeat('a', 51)],
            'whitespace_only' => ['   '],
            'with_numbers' => ['theme123'],
            'with_spaces' => ['my theme'],
            'with_symbols' => ['theme-name'],
            'html_tags' => ['<theme>'],
            'sql_injection' => ["theme'; DROP TABLE"],
            'null_value' => [null],
            'boolean_value' => [true],
            'array_value' => [['theme']]
        ];
    }

    /**
     * @dataProvider invalidOptionsProvider
     */
    public function test_generate_from_color_validates_options(array $options): void {
        $this->generator
            ->expects($this->once())
            ->method('generate_from_color')
            ->with('#FF0000', $options)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_from_color('#FF0000', $options);
    }

    public function invalidOptionsProvider(): array {
        return [
            'empty_options' => [[]],
            'invalid_scheme' => [['scheme' => 'invalid']],
            'negative_count' => [['count' => -1]],
            'zero_count' => [['count' => 0]],
            'too_large_count' => [['count' => 101]],
            'invalid_count_type' => [['count' => '5']],
            'invalid_constraints' => [['constraints' => 'invalid']],
            'invalid_saturation_range' => [['constraints' => ['saturation' => ['min' => 150]]]],
            'invalid_lightness_range' => [['constraints' => ['lightness' => ['max' => -10]]]],
            'invalid_hue_range' => [['constraints' => ['hue' => ['min' => 400]]]],
            'missing_min_max' => [['constraints' => ['saturation' => ['mid' => 50]]]],
            'invalid_range_order' => [['constraints' => ['lightness' => ['min' => 80, 'max' => 20]]]],
            'non_numeric_range' => [['constraints' => ['hue' => ['min' => 'low', 'max' => 'high']]]],
            'null_constraints' => [['constraints' => null]],
            'invalid_harmony_type' => [['harmony' => 123]]
        ];
    }

    /**
     * Test API failure recovery strategies
     *
     * @return void
     */
    public function test_api_failure_recovery(): void {
        $test_cases = [
            [
                'scenario' => [
                    'type' => 'network_error',
                    'attempt' => 1,
                    'last_error' => 'Connection refused'
                ],
                'expected' => [
                    'action' => 'retry',
                    'backoff_seconds' => 2,
                    'max_retries' => 3
                ]
            ],
            [
                'scenario' => [
                    'type' => 'server_error',
                    'attempt' => 2,
                    'last_error' => '503 Service Unavailable'
                ],
                'expected' => [
                    'action' => 'retry',
                    'backoff_seconds' => 4,
                    'max_retries' => 3
                ]
            ],
            [
                'scenario' => [
                    'type' => 'auth_error',
                    'attempt' => 1,
                    'last_error' => '401 Unauthorized'
                ],
                'expected' => [
                    'action' => 'fail',
                    'message' => 'Authentication failed',
                    'retry_allowed' => false
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('handle_api_failure_recovery')
                ->with($case['scenario'])
                ->willReturn($case['expected']);

            $result = $this->generator->handle_api_failure_recovery($case['scenario']);
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test advanced color space validations
     *
     * @return void
     */
    public function test_advanced_color_space_validations(): void {
        $test_cases = [
            [
                'input' => [
                    'color' => [0, 0, 0, 1],
                    'space' => 'rgba',
                    'precision' => 2
                ],
                'expected' => [
                    'valid' => true,
                    'normalized' => [0, 0, 0, 1],
                    'precision_valid' => true
                ]
            ],
            [
                'input' => [
                    'color' => [180, 50, 50],
                    'space' => 'hsl',
                    'precision' => 1
                ],
                'expected' => [
                    'valid' => true,
                    'normalized' => [180, 50, 50],
                    'precision_valid' => true
                ]
            ],
            [
                'input' => [
                    'color' => [400, 150, 50],
                    'space' => 'hsl',
                    'precision' => 1
                ],
                'expected' => [
                    'valid' => false,
                    'error' => 'hue_out_of_range',
                    'precision_valid' => true
                ]
            ],
            [
                'input' => [
                    'color' => [255, 255, 255, 1.5],
                    'space' => 'rgba',
                    'precision' => 2
                ],
                'expected' => [
                    'valid' => false,
                    'error' => 'alpha_out_of_range',
                    'precision_valid' => true
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('validate_advanced_color_space')
                ->with(
                    $case['input']['color'],
                    $case['input']['space'],
                    $case['input']['precision']
                )
                ->willReturn($case['expected']);

            $result = $this->generator->validate_advanced_color_space(
                $case['input']['color'],
                $case['input']['space'],
                $case['input']['precision']
            );
            $this->assertEquals($case['expected'], $result);
        }
    }

    /**
     * Test advanced palette naming rules
     *
     * @return void
     */
    public function test_advanced_palette_naming_rules(): void {
        $test_cases = [
            [
                'input' => [
                    'name' => 'Winter Colors 2024',
                    'context' => [
                        'type' => 'seasonal',
                        'year' => 2024,
                        'language' => 'en'
                    ]
                ],
                'expected' => [
                    'valid' => true,
                    'normalized' => 'winter-colors-2024',
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:47:12-07:00',
                        'category' => 'seasonal',
                        'year' => 2024
                    ]
                ]
            ],
            [
                'input' => [
                    'name' => 'Brand_Colors_V2',
                    'context' => [
                        'type' => 'brand',
                        'version' => 2,
                        'language' => 'en'
                    ]
                ],
                'expected' => [
                    'valid' => true,
                    'normalized' => 'brand-colors-v2',
                    'metadata' => [
                        'generated_at' => '2024-12-08T19:47:12-07:00',
                        'category' => 'brand',
                        'version' => 2
                    ]
                ]
            ],
            [
                'input' => [
                    'name' => '../../etc/passwd',
                    'context' => [
                        'type' => 'system',
                        'language' => 'en'
                    ]
                ],
                'expected' => [
                    'valid' => false,
                    'error' => 'path_traversal_attempt',
                    'security_level' => 'high'
                ]
            ],
            [
                'input' => [
                    'name' => 'SELECT * FROM palettes',
                    'context' => [
                        'type' => 'custom',
                        'language' => 'en'
                    ]
                ],
                'expected' => [
                    'valid' => false,
                    'error' => 'sql_injection_attempt',
                    'security_level' => 'high'
                ]
            ]
        ];

        foreach ($test_cases as $case) {
            $this->generator
                ->expects($this->once())
                ->method('validate_advanced_palette_name')
                ->with($case['input']['name'], $case['input']['context'])
                ->willReturn($case['expected']);

            $result = $this->generator->validate_advanced_palette_name(
                $case['input']['name'],
                $case['input']['context']
            );
            $this->assertEquals($case['expected'], $result);
        }
    }
}
