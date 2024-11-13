<?php

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteOptimizerInterface;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteAnalyzer;
use GLColorPalette\ColorPaletteFormatter;

/**
 * Color Palette Optimizer Class
 *
 * Optimizes color palettes for various criteria including contrast, harmony, and accessibility.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteOptimizer implements ColorPaletteOptimizerInterface {
    /**
     * Color analyzer instance.
     *
     * @var ColorPaletteAnalyzer
     */
    private ColorPaletteAnalyzer $analyzer;

    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private ColorPaletteFormatter $formatter;

    /**
     * Available optimization strategies.
     *
     * @var array
     */
    private array $strategies = [
        'accessibility',
        'harmony',
        'contrast',
        'balance',
        'saturation',
        'lightness'
    ];

    /**
     * Strategy-specific options.
     *
     * @var array
     */
    private array $strategy_options = [
        'accessibility' => [
            'level' => 'AA',
            'min_contrast' => 4.5,
            'preserve_hues' => true
        ],
        'harmony' => [
            'type' => 'complementary',
            'tolerance' => 15,
            'preserve_lightness' => true
        ],
        'contrast' => [
            'target_ratio' => 4.5,
            'tolerance' => 0.5,
            'preserve_hues' => true
        ],
        'balance' => [
            'hue_spacing' => 30,
            'saturation_range' => [50, 100],
            'lightness_range' => [20, 80]
        ],
        'saturation' => [
            'target' => 75,
            'tolerance' => 10,
            'preserve_hues' => true
        ],
        'lightness' => [
            'target' => 50,
            'tolerance' => 10,
            'preserve_hues' => true
        ]
    ];

    /**
     * Constructor.
     *
     * @param ColorPaletteAnalyzer $analyzer  Color analyzer instance.
     * @param ColorPaletteFormatter $formatter Color formatter instance.
     */
    public function __construct(
        ColorPaletteAnalyzer $analyzer,
        ColorPaletteFormatter $formatter
    ) {
        $this->analyzer = $analyzer;
        $this->formatter = $formatter;
    }

    /**
     * Optimizes a color palette.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param array        $options Optimization options.
     * @return ColorPalette Optimized palette.
     */
    public function optimizePalette(ColorPalette $palette, array $options = []): ColorPalette {
        $strategy = $options['strategy'] ?? 'accessibility';

        if (!in_array($strategy, $this->strategies)) {
            throw new \InvalidArgumentException("Invalid strategy: {$strategy}");
        }

        return match ($strategy) {
            'accessibility' => $this->optimizeForAccessibility($palette, $options['level'] ?? 'AA'),
            'harmony' => $this->optimizeForHarmony($palette, $options['type'] ?? 'complementary'),
            'contrast' => $this->optimizeForContrast($palette, $options['target'] ?? 4.5),
            'balance' => $this->optimizeForBalance($palette, $options),
            'saturation' => $this->optimizeForSaturation($palette, $options),
            'lightness' => $this->optimizeForLightness($palette, $options),
            default => throw new \InvalidArgumentException("Unsupported strategy: {$strategy}")
        };
    }

    /**
     * Optimizes for accessibility.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param string       $level   WCAG level.
     * @return ColorPalette Optimized palette.
     */
    public function optimizeForAccessibility(ColorPalette $palette, string $level = 'AA'): ColorPalette {
        $options = $this->strategy_options['accessibility'];
        $colors = $palette->getColors();
        $optimized_colors = [];

        foreach ($colors as $color) {
            $hsl = $this->formatter->hexToHsl($color);

            // Adjust lightness to meet contrast requirements
            if ($hsl[2] > 50) {
                $hsl[2] = min(95, $hsl[2] + 10);
            } else {
                $hsl[2] = max(5, $hsl[2] - 10);
            }

            $optimized_colors[] = $this->formatter->hslToHex($hsl);
        }

        return new ColorPalette([
            'name' => $palette->getName() . ' (Accessibility Optimized)',
            'colors' => $optimized_colors,
            'metadata' => array_merge($palette->getMetadata(), [
                'optimization' => [
                    'strategy' => 'accessibility',
                    'level' => $level
                ]
            ])
        ]);
    }

    /**
     * Optimizes for harmony.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param string       $type    Harmony type.
     * @return ColorPalette Optimized palette.
     */
    public function optimizeForHarmony(ColorPalette $palette, string $type = 'complementary'): ColorPalette {
        $options = $this->strategy_options['harmony'];
        $colors = $palette->getColors();
        $optimized_colors = [];
        $base_hsl = $this->formatter->hexToHsl($colors[0]);

        switch ($type) {
            case 'complementary':
                $optimized_colors = $this->generateComplementaryColors($base_hsl);
                break;
            case 'analogous':
                $optimized_colors = $this->generateAnalogousColors($base_hsl);
                break;
            case 'triadic':
                $optimized_colors = $this->generateTriadicColors($base_hsl);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported harmony type: {$type}");
        }

        return new ColorPalette([
            'name' => $palette->getName() . ' (Harmony Optimized)',
            'colors' => $optimized_colors,
            'metadata' => array_merge($palette->getMetadata(), [
                'optimization' => [
                    'strategy' => 'harmony',
                    'type' => $type
                ]
            ])
        ]);
    }

    /**
     * Optimizes for contrast.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param float        $target  Target contrast ratio.
     * @return ColorPalette Optimized palette.
     */
    public function optimizeForContrast(ColorPalette $palette, float $target = 4.5): ColorPalette {
        $options = $this->strategy_options['contrast'];
        $colors = $palette->getColors();
        $optimized_colors = [];

        foreach ($colors as $i => $color) {
            $hsl = $this->formatter->hexToHsl($color);

            // Adjust lightness to improve contrast with adjacent colors
            if ($i > 0) {
                $prev_hsl = $this->formatter->hexToHsl($optimized_colors[$i - 1]);
                $hsl[2] = $this->adjustLightnessForContrast($hsl[2], $prev_hsl[2], $target);
            }

            $optimized_colors[] = $this->formatter->hslToHex($hsl);
        }

        return new ColorPalette([
            'name' => $palette->getName() . ' (Contrast Optimized)',
            'colors' => $optimized_colors,
            'metadata' => array_merge($palette->getMetadata(), [
                'optimization' => [
                    'strategy' => 'contrast',
                    'target' => $target
                ]
            ])
        ]);
    }

    /**
     * Optimizes for balance.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param array        $options Balance options.
     * @return ColorPalette Optimized palette.
     */
    private function optimizeForBalance(ColorPalette $palette, array $options = []): ColorPalette {
        $balance_options = array_merge($this->strategy_options['balance'], $options);
        $colors = $palette->getColors();
        $optimized_colors = [];

        // Distribute hues evenly
        $hue_step = 360 / count($colors);
        foreach ($colors as $i => $color) {
            $hsl = $this->formatter->hexToHsl($color);
            $hsl[0] = ($hue_step * $i) % 360;

            // Normalize saturation and lightness
            $hsl[1] = $this->normalizeValue(
                $hsl[1],
                $balance_options['saturation_range'][0],
                $balance_options['saturation_range'][1]
            );

            $hsl[2] = $this->normalizeValue(
                $hsl[2],
                $balance_options['lightness_range'][0],
                $balance_options['lightness_range'][1]
            );

            $optimized_colors[] = $this->formatter->hslToHex($hsl);
        }

        return new ColorPalette([
            'name' => $palette->getName() . ' (Balance Optimized)',
            'colors' => $optimized_colors,
            'metadata' => array_merge($palette->getMetadata(), [
                'optimization' => [
                    'strategy' => 'balance',
                    'options' => $balance_options
                ]
            ])
        ]);
    }

    /**
     * Optimizes for saturation.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param array        $options Saturation options.
     * @return ColorPalette Optimized palette.
     */
    private function optimizeForSaturation(ColorPalette $palette, array $options = []): ColorPalette {
        $sat_options = array_merge($this->strategy_options['saturation'], $options);
        $colors = $palette->getColors();
        $optimized_colors = [];

        foreach ($colors as $color) {
            $hsl = $this->formatter->hexToHsl($color);
            $hsl[1] = $sat_options['target'];
            $optimized_colors[] = $this->formatter->hslToHex($hsl);
        }

        return new ColorPalette([
            'name' => $palette->getName() . ' (Saturation Optimized)',
            'colors' => $optimized_colors,
            'metadata' => array_merge($palette->getMetadata(), [
                'optimization' => [
                    'strategy' => 'saturation',
                    'options' => $sat_options
                ]
            ])
        ]);
    }

    /**
     * Optimizes for lightness.
     *
     * @param ColorPalette $palette Palette to optimize.
     * @param array        $options Lightness options.
     * @return ColorPalette Optimized palette.
     */
    private function optimizeForLightness(ColorPalette $palette, array $options = []): ColorPalette {
        $light_options = array_merge($this->strategy_options['lightness'], $options);
        $colors = $palette->getColors();
        $optimized_colors = [];

        foreach ($colors as $color) {
            $hsl = $this->formatter->hexToHsl($color);
            $hsl[2] = $light_options['target'];
            $optimized_colors[] = $this->formatter->hslToHex($hsl);
        }

        return new ColorPalette([
            'name' => $palette->getName() . ' (Lightness Optimized)',
            'colors' => $optimized_colors,
            'metadata' => array_merge($palette->getMetadata(), [
                'optimization' => [
                    'strategy' => 'lightness',
                    'options' => $light_options
                ]
            ])
        ]);
    }

    /**
     * Gets available optimization strategies.
     *
     * @return array List of available strategies.
     */
    public function getAvailableStrategies(): array {
        return $this->strategies;
    }

    /**
     * Gets optimization options.
     *
     * @param string $strategy Strategy to get options for.
     * @return array Strategy options.
     */
    public function getStrategyOptions(string $strategy): array {
        if (!isset($this->strategy_options[$strategy])) {
            throw new \InvalidArgumentException("Invalid strategy: {$strategy}");
        }
        return $this->strategy_options[$strategy];
    }

    /**
     * Generates complementary colors.
     *
     * @param array $base_hsl Base HSL values.
     * @return array Array of hex colors.
     */
    private function generateComplementaryColors(array $base_hsl): array {
        $colors = [$this->formatter->hslToHex($base_hsl)];

        // Add complement
        $complement_hsl = $base_hsl;
        $complement_hsl[0] = ($base_hsl[0] + 180) % 360;
        $colors[] = $this->formatter->hslToHex($complement_hsl);

        return $colors;
    }

    /**
     * Generates analogous colors.
     *
     * @param array $base_hsl Base HSL values.
     * @return array Array of hex colors.
     */
    private function generateAnalogousColors(array $base_hsl): array {
        $colors = [$this->formatter->hslToHex($base_hsl)];

        // Add analogous colors
        for ($angle = -30; $angle <= 30; $angle += 30) {
            if ($angle === 0) continue;

            $analogous_hsl = $base_hsl;
            $analogous_hsl[0] = ($base_hsl[0] + $angle + 360) % 360;
            $colors[] = $this->formatter->hslToHex($analogous_hsl);
        }

        return $colors;
    }

    /**
     * Generates triadic colors.
     *
     * @param array $base_hsl Base HSL values.
     * @return array Array of hex colors.
     */
    private function generateTriadicColors(array $base_hsl): array {
        $colors = [$this->formatter->hslToHex($base_hsl)];

        // Add triadic colors
        for ($angle = 120; $angle < 360; $angle += 120) {
            $triadic_hsl = $base_hsl;
            $triadic_hsl[0] = ($base_hsl[0] + $angle) % 360;
            $colors[] = $this->formatter->hslToHex($triadic_hsl);
        }

        return $colors;
    }

    /**
     * Adjusts lightness for contrast.
     *
     * @param float $l1     First lightness value.
     * @param float $l2     Second lightness value.
     * @param float $target Target contrast ratio.
     * @return float Adjusted lightness value.
     */
    private function adjustLightnessForContrast(float $l1, float $l2, float $target): float {
        if (abs($l1 - $l2) < $target) {
            return $l1 > $l2 ? min(100, $l1 + $target) : max(0, $l1 - $target);
        }
        return $l1;
    }

    /**
     * Normalizes a value within a range.
     *
     * @param float $value Value to normalize.
     * @param float $min   Minimum value.
     * @param float $max   Maximum value.
     * @return float Normalized value.
     */
    private function normalizeValue(float $value, float $min, float $max): float {
        return max($min, min($max, $value));
    }
}
