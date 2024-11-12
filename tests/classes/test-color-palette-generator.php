<?php

namespace GLColorPalette\Tests;

use PHPUnit\Framework\TestCase;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteGenerator;
use GLColorPalette\ColorPaletteFormatter;
use GLColorPalette\ColorPaletteAnalyzer;

class ColorPaletteGeneratorTest extends TestCase {
    private $generator;
    private $formatter;
    private $analyzer;

    protected function setUp(): void {
        $this->formatter = new ColorPaletteFormatter();
        $this->analyzer = new ColorPaletteAnalyzer($this->formatter);
        $this->generator = new ColorPaletteGenerator($this->formatter, $this->analyzer);
    }

    public function test_generate_from_color_creates_valid_palette(): void {
        // Act
        $palette = $this->generator->generate_from_color('#FF0000');

        // Assert
        $this->assertInstanceOf(ColorPalette::class, $palette);
        $this->assertNotEmpty($palette->get_colors());
        $this->assertEquals(5, count($palette->get_colors())); // Default count

        foreach ($palette->get_colors() as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $color);
        }
    }

    /**
     * @dataProvider colorSchemeProvider
     */
    public function test_generate_different_color_schemes(
        string $scheme,
        int $count,
        array $expectations
    ): void {
        // Arrange
        $options = [
            'scheme' => $scheme,
            'count' => $count
        ];

        // Act
        $palette = $this->generator->generate_from_color('#FF0000', $options);
        $colors = $palette->get_colors();
        $analysis = $this->analyzer->analyze_palette($palette);

        // Assert
        $this->assertCount($count, $colors);
        foreach ($expectations as $key => $expected) {
            $this->assertGreaterThanOrEqual(
                $expected,
                $this->get_nested_value($analysis, $key)
            );
        }
    }

    public function colorSchemeProvider(): array {
        return [
            'monochromatic' => [
                'monochromatic',
                5,
                ['harmony.harmony_score' => 0.7]
            ],
            'analogous' => [
                'analogous',
                5,
                ['harmony.harmony_score' => 0.6]
            ],
            'complementary' => [
                'complementary',
                4,
                ['contrast.statistics.avg' => 3.0]
            ],
            'triadic' => [
                'triadic',
                6,
                ['distribution.hue_distribution.coverage' => 0.5]
            ],
            'tetradic' => [
                'tetradic',
                8,
                ['distribution.hue_distribution.coverage' => 0.6]
            ],
            'split_complementary' => [
                'split_complementary',
                5,
                ['contrast.statistics.avg' => 2.5]
            ]
        ];
    }

    public function test_generate_random_creates_valid_palette(): void {
        // Act
        $palette = $this->generator->generate_random();

        // Assert
        $this->assertInstanceOf(ColorPalette::class, $palette);
        $this->assertNotEmpty($palette->get_colors());

        $analysis = $this->analyzer->analyze_palette($palette);
        $this->assertGreaterThan(0.5, $analysis['harmony']['harmony_score']);
    }

    public function test_generate_respects_quality_threshold(): void {
        // Arrange
        $options = [
            'quality_threshold' => 0.8,
            'max_attempts' => 50
        ];

        // Act
        $palette = $this->generator->generate_from_color('#FF0000', $options);
        $metadata = $palette->get_metadata();

        // Assert
        $this->assertArrayHasKey('quality_score', $metadata);
        $this->assertGreaterThanOrEqual(0.8, $metadata['quality_score']);
    }

    public function test_generate_respects_color_constraints(): void {
        // Arrange
        $options = [
            'saturation_range' => ['min' => 50, 'max' => 70],
            'lightness_range' => ['min' => 40, 'max' => 60]
        ];

        // Act
        $palette = $this->generator->generate_from_color('#FF0000', $options);

        // Assert
        foreach ($palette->get_colors() as $color) {
            $hsl = $this->color_to_hsl($color);
            $this->assertGreaterThanOrEqual(50, $hsl[1]);
            $this->assertLessThanOrEqual(70, $hsl[1]);
            $this->assertGreaterThanOrEqual(40, $hsl[2]);
            $this->assertLessThanOrEqual(60, $hsl[2]);
        }
    }

    public function test_generate_handles_edge_cases(): void {
        // Test with extreme base colors
        $edge_cases = ['#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF'];

        foreach ($edge_cases as $color) {
            $palette = $this->generator->generate_from_color($color);
            $this->assertNotEmpty($palette->get_colors());
            $this->assertContains($color, $palette->get_colors());
        }
    }

    public function test_generate_preserves_input_color(): void {
        // Arrange
        $base_color = '#FF0000';
        $options = ['preserve_input' => true];

        // Act
        $palette = $this->generator->generate_from_color($base_color, $options);

        // Assert
        $this->assertContains($base_color, $palette->get_colors());
    }

    public function test_generate_with_invalid_scheme_throws_exception(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate_from_color('#FF0000', ['scheme' => 'invalid']);
    }

    public function test_generate_performance(): void {
        // Arrange
        $start_time = microtime(true);
        $max_execution_time = 2.0; // seconds

        // Act
        $this->generator->generate_from_color('#FF0000');
        $execution_time = microtime(true) - $start_time;

        // Assert
        $this->assertLessThan($max_execution_time, $execution_time);
    }

    public function test_generate_multiple_palettes_are_unique(): void {
        // Generate multiple palettes with same base color
        $palettes = [];
        for ($i = 0; $i < 5; $i++) {
            $palettes[] = $this->generator->generate_from_color('#FF0000');
        }

        // Compare each palette with others
        for ($i = 0; $i < count($palettes); $i++) {
            for ($j = $i + 1; $j < count($palettes); $j++) {
                $this->assertNotEquals(
                    $palettes[$i]->get_colors(),
                    $palettes[$j]->get_colors()
                );
            }
        }
    }

    public function test_generate_with_custom_metadata(): void {
        // Arrange
        $options = [
            'metadata' => [
                'purpose' => 'testing',
                'author' => 'PHPUnit'
            ]
        ];

        // Act
        $palette = $this->generator->generate_from_color('#FF0000', $options);
        $metadata = $palette->get_metadata();

        // Assert
        $this->assertArrayHasKey('purpose', $metadata);
        $this->assertEquals('testing', $metadata['purpose']);
        $this->assertEquals('PHPUnit', $metadata['author']);
    }

    /**
     * Helper method to convert color to HSL.
     */
    private function color_to_hsl(string $color): array {
        $hsl = $this->formatter->format_color($color, 'hsl');
        preg_match('/hsl\((\d+),\s*(\d+)%?,\s*(\d+)%?\)/', $hsl, $matches);
        return [
            (int)$matches[1],
            (int)$matches[2],
            (int)$matches[3]
        ];
    }

    /**
     * Helper method to get nested array value.
     */
    private function get_nested_value(array $array, string $path) {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
} 
