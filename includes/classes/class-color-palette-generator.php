<?php

namespace GLColorPalette;

/**
 * Color Palette Generator Class
 *
 * Generates color palettes based on various algorithms and color theory.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteGenerator {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private $formatter;

    /**
     * Color analyzer instance.
     *
     * @var ColorPaletteAnalyzer
     */
    private $analyzer;

    /**
     * Default generation options.
     *
     * @var array
     */
    private $default_options = [
        'scheme' => 'analogous',     // Color scheme type
        'count' => 5,                // Number of colors
        'saturation_range' => [      // Saturation constraints
            'min' => 30,
            'max' => 80
        ],
        'lightness_range' => [       // Lightness constraints
            'min' => 20,
            'max' => 80
        ],
        'angle_range' => [           // Hue angle constraints
            'min' => 30,
            'max' => 90
        ],
        'quality_threshold' => 0.7,  // Minimum quality score
        'max_attempts' => 100,       // Maximum generation attempts
        'preserve_input' => true,    // Keep input color in palette
        'color_space' => 'hsl'       // Color space for generation
    ];

    /**
     * Constructor.
     *
     * @param ColorPaletteFormatter $formatter Color formatter instance.
     * @param ColorPaletteAnalyzer  $analyzer  Color analyzer instance.
     */
    public function __construct(
        ColorPaletteFormatter $formatter,
        ColorPaletteAnalyzer $analyzer
    ) {
        $this->formatter = $formatter;
        $this->analyzer = $analyzer;
    }

    /**
     * Generates a color palette from a base color.
     *
     * @param string $base_color Base color to generate from.
     * @param array $options Optional. Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generate_from_color(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->color_to_hsl($base_color);
        $colors = [$base_color];

        switch ($options['scheme']) {
            case 'monochromatic':
                $colors = $this->generate_monochromatic($base_hsl, $options);
                break;
            case 'analogous':
                $colors = $this->generate_analogous($base_hsl, $options);
                break;
            case 'complementary':
                $colors = $this->generate_complementary($base_hsl, $options);
                break;
            case 'triadic':
                $colors = $this->generate_triadic($base_hsl, $options);
                break;
            case 'tetradic':
                $colors = $this->generate_tetradic($base_hsl, $options);
                break;
            case 'split_complementary':
                $colors = $this->generate_split_complementary($base_hsl, $options);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported scheme: {$options['scheme']}");
        }

        // Ensure quality meets threshold
        $attempts = 0;
        $palette = new ColorPalette(['colors' => $colors]);
        $quality = $this->evaluate_palette_quality($palette);

        while ($quality < $options['quality_threshold'] && $attempts < $options['max_attempts']) {
            $colors = $this->regenerate_palette($base_hsl, $options);
            $palette = new ColorPalette(['colors' => $colors]);
            $quality = $this->evaluate_palette_quality($palette);
            $attempts++;
        }

        return new ColorPalette([
            'name' => "Generated {$options['scheme']} Palette",
            'colors' => $colors,
            'metadata' => [
                'base_color' => $base_color,
                'scheme' => $options['scheme'],
                'quality_score' => $quality,
                'generation_attempts' => $attempts + 1
            ]
        ]);
    }

    /**
     * Generates a random color palette.
     *
     * @param array $options Optional. Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generate_random(array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = [
            rand(0, 360),  // Hue
            rand($options['saturation_range']['min'], $options['saturation_range']['max']),
            rand($options['lightness_range']['min'], $options['lightness_range']['max'])
        ];

        return $this->generate_from_color(
            $this->hsl_to_hex($base_hsl),
            $options
        );
    }

    /**
     * Generates monochromatic color scheme.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Generated colors.
     */
    private function generate_monochromatic(array $base_hsl, array $options): array {
        $colors = [];
        $count = $options['count'];
        $s_step = ($options['saturation_range']['max'] - $options['saturation_range']['min']) / ($count - 1);
        $l_step = ($options['lightness_range']['max'] - $options['lightness_range']['min']) / ($count - 1);

        for ($i = 0; $i < $count; $i++) {
            $hsl = [
                $base_hsl[0],
                $options['saturation_range']['min'] + ($s_step * $i),
                $options['lightness_range']['min'] + ($l_step * $i)
            ];
            $colors[] = $this->hsl_to_hex($hsl);
        }

        return $colors;
    }

    /**
     * Generates analogous color scheme.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Generated colors.
     */
    private function generate_analogous(array $base_hsl, array $options): array {
        $colors = [];
        $count = $options['count'];
        $angle = $options['angle_range']['min'];
        $step = ($angle * 2) / ($count - 1);

        for ($i = 0; $i < $count; $i++) {
            $hue = ($base_hsl[0] - $angle + ($step * $i)) % 360;
            if ($hue < 0) $hue += 360;

            $hsl = [
                $hue,
                $base_hsl[1],
                $base_hsl[2]
            ];
            $colors[] = $this->hsl_to_hex($hsl);
        }

        return $colors;
    }

    /**
     * Generates complementary color scheme.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Generated colors.
     */
    private function generate_complementary(array $base_hsl, array $options): array {
        $colors = [$this->hsl_to_hex($base_hsl)];
        $complement_hue = ($base_hsl[0] + 180) % 360;

        // Generate variations around the complement
        $variations = $options['count'] - 1;
        $angle = 15;

        for ($i = 0; $i < $variations; $i++) {
            $hue = ($complement_hue + ($angle * ($i - floor($variations/2)))) % 360;
            if ($hue < 0) $hue += 360;

            $hsl = [
                $hue,
                $base_hsl[1],
                $base_hsl[2]
            ];
            $colors[] = $this->hsl_to_hex($hsl);
        }

        return $colors;
    }

    /**
     * Generates triadic color scheme.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Generated colors.
     */
    private function generate_triadic(array $base_hsl, array $options): array {
        $colors = [];
        $base_angles = [0, 120, 240];
        $variations_per_angle = ceil($options['count'] / 3);

        foreach ($base_angles as $angle) {
            $hue = ($base_hsl[0] + $angle) % 360;

            for ($i = 0; $i < $variations_per_angle; $i++) {
                if (count($colors) >= $options['count']) break;

                $variation = 15 * ($i - floor($variations_per_angle/2));
                $final_hue = ($hue + $variation) % 360;
                if ($final_hue < 0) $final_hue += 360;

                $hsl = [
                    $final_hue,
                    $base_hsl[1],
                    $base_hsl[2]
                ];
                $colors[] = $this->hsl_to_hex($hsl);
            }
        }

        return array_slice($colors, 0, $options['count']);
    }

    /**
     * Generates tetradic color scheme.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Generated colors.
     */
    private function generate_tetradic(array $base_hsl, array $options): array {
        $colors = [];
        $base_angles = [0, 90, 180, 270];
        $variations_per_angle = ceil($options['count'] / 4);

        foreach ($base_angles as $angle) {
            $hue = ($base_hsl[0] + $angle) % 360;

            for ($i = 0; $i < $variations_per_angle; $i++) {
                if (count($colors) >= $options['count']) break;

                $variation = 15 * ($i - floor($variations_per_angle/2));
                $final_hue = ($hue + $variation) % 360;
                if ($final_hue < 0) $final_hue += 360;

                $hsl = [
                    $final_hue,
                    $base_hsl[1],
                    $base_hsl[2]
                ];
                $colors[] = $this->hsl_to_hex($hsl);
            }
        }

        return array_slice($colors, 0, $options['count']);
    }

    /**
     * Generates split-complementary color scheme.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Generated colors.
     */
    private function generate_split_complementary(array $base_hsl, array $options): array {
        $colors = [$this->hsl_to_hex($base_hsl)];
        $complement_hue = ($base_hsl[0] + 180) % 360;
        $split_angle = 30;

        $split_hues = [
            ($complement_hue - $split_angle) % 360,
            ($complement_hue + $split_angle) % 360
        ];

        foreach ($split_hues as $hue) {
            if ($hue < 0) $hue += 360;

            $variations = floor(($options['count'] - 1) / 2);
            $angle = 15;

            for ($i = 0; $i < $variations; $i++) {
                $final_hue = ($hue + ($angle * ($i - floor($variations/2)))) % 360;
                if ($final_hue < 0) $final_hue += 360;

                $hsl = [
                    $final_hue,
                    $base_hsl[1],
                    $base_hsl[2]
                ];
                $colors[] = $this->hsl_to_hex($hsl);
            }
        }

        return array_slice($colors, 0, $options['count']);
    }

    /**
     * Evaluates palette quality.
     *
     * @param ColorPalette $palette Palette to evaluate.
     * @return float Quality score (0-1).
     */
    private function evaluate_palette_quality(ColorPalette $palette): float {
        $analysis = $this->analyzer->analyze_palette($palette);

        // Weight different aspects of the palette
        $contrast_score = $analysis['contrast']['statistics']['avg'] / 21.0; // Max contrast is 21
        $harmony_score = $analysis['harmony']['harmony_score'];
        $distribution_score = $analysis['distribution']['hue_distribution']['balance'];

        return ($contrast_score * 0.4 + $harmony_score * 0.4 + $distribution_score * 0.2);
    }

    /**
     * Regenerates palette with slight variations.
     *
     * @param array $base_hsl Base HSL values.
     * @param array $options Generation options.
     * @return array Regenerated colors.
     */
    private function regenerate_palette(array $base_hsl, array $options): array {
        // Add small random variations to the base color
        $varied_base = [
            ($base_hsl[0] + rand(-10, 10)) % 360,
            max(0, min(100, $base_hsl[1] + rand(-5, 5))),
            max(0, min(100, $base_hsl[2] + rand(-5, 5)))
        ];

        // Generate new palette with varied base
        $method = "generate_{$options['scheme']}";
        return $this->$method($varied_base, $options);
    }

    /**
     * Converts color to HSL array.
     *
     * @param string $color Color value.
     * @return array HSL values [h, s, l].
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
     * Converts HSL values to hex color.
     *
     * @param array $hsl HSL values [h, s, l].
     * @return string Hex color value.
     */
    private function hsl_to_hex(array $hsl): string {
        return $this->formatter->format_color(
            sprintf('hsl(%d, %d%%, %d%%)', $hsl[0], $hsl[1], $hsl[2]),
            'hex'
        );
    }
} 
