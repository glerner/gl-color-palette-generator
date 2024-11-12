<?php
/**
 * Color Palette Generator Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteGeneratorInterface;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteFormatter;

/**
 * Generates color palettes using various algorithms.
 */
class ColorPaletteGenerator implements ColorPaletteGeneratorInterface {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private ColorPaletteFormatter $formatter;

    /**
     * Available generation algorithms.
     *
     * @var array
     */
    private array $algorithms = [
        'complementary',
        'analogous',
        'triadic',
        'monochromatic',
        'split_complementary',
        'tetradic',
        'random'
    ];

    /**
     * Default generation options.
     *
     * @var array
     */
    private array $default_options = [
        'count' => 5,
        'saturation_range' => [50, 100],
        'lightness_range' => [20, 80],
        'angle_variation' => 15,
        'include_base' => true,
        'name' => 'Generated Palette'
    ];

    /**
     * Constructor.
     *
     * @param ColorPaletteFormatter $formatter Color formatter instance.
     */
    public function __construct(ColorPaletteFormatter $formatter) {
        $this->formatter = $formatter;
    }

    /**
     * Generates a new color palette.
     *
     * @param array $options Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generatePalette(array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);

        if (!isset($options['algorithm'])) {
            $options['algorithm'] = $this->algorithms[array_rand($this->algorithms)];
        }

        if (!isset($options['base_color'])) {
            $options['base_color'] = $this->generateRandomColor();
        }

        return match ($options['algorithm']) {
            'complementary' => $this->generateComplementary($options['base_color'], $options),
            'analogous' => $this->generateAnalogous($options['base_color'], $options),
            'triadic' => $this->generateTriadic($options['base_color'], $options),
            'monochromatic' => $this->generateMonochromatic($options['base_color'], $options),
            'split_complementary' => $this->generateSplitComplementary($options['base_color'], $options),
            'tetradic' => $this->generateTetradic($options['base_color'], $options),
            'random' => $this->generateRandomPalette($options),
            default => throw new \InvalidArgumentException("Unknown algorithm: {$options['algorithm']}")
        };
    }

    /**
     * Generates a complementary color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateComplementary(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->hexToHsl($base_color);
        $colors = [];

        if ($options['include_base']) {
            $colors[] = $base_color;
        }

        // Add complementary color (180 degrees)
        $complement_hue = ($base_hsl[0] + 180) % 360;
        $colors[] = $this->hslToHex([
            $complement_hue,
            $base_hsl[1],
            $base_hsl[2]
        ]);

        // Add variations if needed
        while (count($colors) < $options['count']) {
            $colors[] = $this->generateVariation($base_color, $options);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => $colors,
            'metadata' => [
                'algorithm' => 'complementary',
                'base_color' => $base_color
            ]
        ]);
    }

    /**
     * Generates an analogous color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateAnalogous(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->hexToHsl($base_color);
        $colors = [];

        if ($options['include_base']) {
            $colors[] = $base_color;
        }

        // Generate colors on either side of the base color
        $angle = 30;
        $variations = floor(($options['count'] - 1) / 2);

        for ($i = 1; $i <= $variations; $i++) {
            // Add color clockwise
            $colors[] = $this->hslToHex([
                ($base_hsl[0] + ($angle * $i)) % 360,
                $base_hsl[1],
                $base_hsl[2]
            ]);

            // Add color counter-clockwise
            $colors[] = $this->hslToHex([
                ($base_hsl[0] - ($angle * $i) + 360) % 360,
                $base_hsl[1],
                $base_hsl[2]
            ]);
        }

        // Add one more if we need an odd number
        if (count($colors) < $options['count']) {
            $colors[] = $this->generateVariation($base_color, $options);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => $colors,
            'metadata' => [
                'algorithm' => 'analogous',
                'base_color' => $base_color
            ]
        ]);
    }

    /**
     * Generates a triadic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateTriadic(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->hexToHsl($base_color);
        $colors = [];

        if ($options['include_base']) {
            $colors[] = $base_color;
        }

        // Add colors at 120 and 240 degrees
        for ($i = 1; $i <= 2; $i++) {
            $colors[] = $this->hslToHex([
                ($base_hsl[0] + (120 * $i)) % 360,
                $base_hsl[1],
                $base_hsl[2]
            ]);
        }

        // Add variations if needed
        while (count($colors) < $options['count']) {
            $colors[] = $this->generateVariation($base_color, $options);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => $colors,
            'metadata' => [
                'algorithm' => 'triadic',
                'base_color' => $base_color
            ]
        ]);
    }

    /**
     * Generates a monochromatic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    public function generateMonochromatic(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->hexToHsl($base_color);
        $colors = [];

        if ($options['include_base']) {
            $colors[] = $base_color;
        }

        // Generate variations with different lightness values
        $lightness_step = (
            $options['lightness_range'][1] - $options['lightness_range'][0]
        ) / ($options['count'] - 1);

        for ($i = 0; $i < $options['count']; $i++) {
            if (count($colors) >= $options['count']) {
                break;
            }

            $lightness = $options['lightness_range'][0] + ($lightness_step * $i);
            $colors[] = $this->hslToHex([
                $base_hsl[0],
                $base_hsl[1],
                $lightness
            ]);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => array_unique($colors),
            'metadata' => [
                'algorithm' => 'monochromatic',
                'base_color' => $base_color
            ]
        ]);
    }

    /**
     * Generates a split complementary color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    private function generateSplitComplementary(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->hexToHsl($base_color);
        $colors = [];

        if ($options['include_base']) {
            $colors[] = $base_color;
        }

        // Add split complementary colors (150 and 210 degrees from base)
        $complement_hue = ($base_hsl[0] + 180) % 360;
        $split_angle = 30;

        $colors[] = $this->hslToHex([
            ($complement_hue - $split_angle + 360) % 360,
            $base_hsl[1],
            $base_hsl[2]
        ]);

        $colors[] = $this->hslToHex([
            ($complement_hue + $split_angle) % 360,
            $base_hsl[1],
            $base_hsl[2]
        ]);

        // Add variations if needed
        while (count($colors) < $options['count']) {
            $colors[] = $this->generateVariation($base_color, $options);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => $colors,
            'metadata' => [
                'algorithm' => 'split_complementary',
                'base_color' => $base_color
            ]
        ]);
    }

    /**
     * Generates a tetradic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return ColorPalette Generated palette.
     */
    private function generateTetradic(string $base_color, array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $base_hsl = $this->hexToHsl($base_color);
        $colors = [];

        if ($options['include_base']) {
            $colors[] = $base_color;
        }

        // Add colors at 90, 180, and 270 degrees
        for ($i = 1; $i <= 3; $i++) {
            $colors[] = $this->hslToHex([
                ($base_hsl[0] + (90 * $i)) % 360,
                $base_hsl[1],
                $base_hsl[2]
            ]);
        }

        // Add variations if needed
        while (count($colors) < $options['count']) {
            $colors[] = $this->generateVariation($base_color, $options);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => $colors,
            'metadata' => [
                'algorithm' => 'tetradic',
                'base_color' => $base_color
            ]
        ]);
    }

    /**
     * Generates a random color palette.
     *
     * @param array $options Generation options.
     * @return ColorPalette Generated palette.
     */
    private function generateRandomPalette(array $options = []): ColorPalette {
        $options = array_merge($this->default_options, $options);
        $colors = [];

        for ($i = 0; $i < $options['count']; $i++) {
            $colors[] = $this->generateRandomColor($options);
        }

        return new ColorPalette([
            'name' => $options['name'],
            'colors' => $colors,
            'metadata' => [
                'algorithm' => 'random'
            ]
        ]);
    }

    /**
     * Generates a random color.
     *
     * @param array $options Optional. Generation options.
     * @return string Generated color in hex format.
     */
    private function generateRandomColor(array $options = []): string {
        $options = array_merge($this->default_options, $options);

        return $this->hslToHex([
            rand(0, 359),
            rand(
                $options['saturation_range'][0],
                $options['saturation_range'][1]
            ),
            rand(
                $options['lightness_range'][0],
                $options['lightness_range'][1]
            )
        ]);
    }

    /**
     * Generates a variation of a color.
     *
     * @param string $base_color Base color.
     * @param array  $options    Generation options.
     * @return string Generated color in hex format.
     */
    private function generateVariation(string $base_color, array $options = []): string {
        $base_hsl = $this->hexToHsl($base_color);
        $variation = $options['angle_variation'] ?? 15;

        return $this->hslToHex([
            ($base_hsl[0] + rand(-$variation, $variation) + 360) % 360,
            $base_hsl[1],
            $base_hsl[2]
        ]);
    }

    /**
     * Gets available generation algorithms.
     *
     * @return array List of available algorithms.
     */
    public function getAvailableAlgorithms(): array {
        return $this->algorithms;
    }

    /**
     * Gets default generation options.
     *
     * @return array Default options.
     */
    public function getDefaultOptions(): array {
        return $this->default_options;
    }

    /**
     * Converts hex color to HSL array.
     *
     * @param string $hex Color in hex format.
     * @return array HSL values [h, s, l].
     */
    private function hexToHsl(string $hex): array {
        $hex = $this->formatter->normalizeColor($hex);
        return $this->formatter->formatColor($hex, 'hsl');
    }

    /**
     * Converts HSL array to hex color.
     *
     * @param array $hsl HSL values [h, s, l].
     * @return string Color in hex format.
     */
    private function hslToHex(array $hsl): string {
        $hsl_string = sprintf('hsl(%d, %d%%, %d%%)', ...$hsl);
        return $this->formatter->formatColor($hsl_string, 'hex');
    }
}
