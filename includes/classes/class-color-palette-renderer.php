<?php
/**
 * Color Palette Renderer Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteRendererInterface;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteFormatter;

/**
 * Renders color palettes in various formats.
 */
class ColorPaletteRenderer implements ColorPaletteRendererInterface {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private ColorPaletteFormatter $formatter;

    /**
     * Supported rendering formats.
     *
     * @var array
     */
    private array $supported_formats = [
        'html',
        'svg',
        'canvas',
        'text',
        'json'
    ];

    /**
     * Format-specific options.
     *
     * @var array
     */
    private array $format_options = [
        'html' => [
            'swatch_size' => '50px',
            'border_radius' => '4px',
            'spacing' => '10px',
            'show_labels' => true,
            'show_info' => true,
            'container_class' => 'color-palette',
            'swatch_class' => 'color-swatch',
            'label_class' => 'color-label',
            'info_class' => 'color-info'
        ],
        'svg' => [
            'width' => 500,
            'height' => 100,
            'swatch_size' => 80,
            'spacing' => 10,
            'show_labels' => true,
            'font_family' => 'Arial',
            'font_size' => 12
        ],
        'canvas' => [
            'width' => 500,
            'height' => 100,
            'swatch_size' => 80,
            'spacing' => 10,
            'background' => '#FFFFFF'
        ],
        'text' => [
            'format' => 'hex',
            'separator' => "\n",
            'show_name' => true,
            'show_info' => true
        ],
        'json' => [
            'pretty_print' => true,
            'include_metadata' => true,
            'include_info' => true
        ]
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
     * Renders a color palette.
     *
     * @param ColorPalette $palette Palette to render.
     * @param string       $format  Output format.
     * @param array        $options Rendering options.
     * @return string Rendered output.
     */
    public function renderPalette(ColorPalette $palette, string $format = 'html', array $options = []): string {
        if (!in_array($format, $this->supported_formats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        $options = array_merge($this->format_options[$format], $options);

        return match ($format) {
            'html' => $this->renderHtml($palette, $options),
            'svg' => $this->renderSvg($palette, $options),
            'canvas' => $this->renderCanvas($palette, $options),
            'text' => $this->renderText($palette, $options),
            'json' => $this->renderJson($palette, $options),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Renders a color swatch.
     *
     * @param string $color   Color to render.
     * @param string $format  Output format.
     * @param array  $options Rendering options.
     * @return string Rendered swatch.
     */
    public function renderSwatch(string $color, string $format = 'html', array $options = []): string {
        $options = array_merge($this->format_options[$format], $options);

        return match ($format) {
            'html' => $this->renderHtmlSwatch($color, $options),
            'svg' => $this->renderSvgSwatch($color, $options),
            'canvas' => $this->renderCanvasSwatch($color, $options),
            default => $color
        };
    }

    /**
     * Renders color information.
     *
     * @param string $color   Color to render info for.
     * @param array  $options Rendering options.
     * @return string Rendered color information.
     */
    public function renderColorInfo(string $color, array $options = []): string {
        $rgb = $this->formatter->hexToRgb($color);
        $hsl = $this->formatter->hexToHsl($color);
        $info = [];

        $info[] = "HEX: {$color}";
        $info[] = sprintf("RGB: %d, %d, %d", $rgb[0], $rgb[1], $rgb[2]);
        $info[] = sprintf("HSL: %d°, %d%%, %d%%", round($hsl[0]), round($hsl[1]), round($hsl[2]));

        return implode($options['separator'] ?? '<br>', $info);
    }

    /**
     * Gets supported rendering formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array {
        return $this->supported_formats;
    }

    /**
     * Gets format-specific options.
     *
     * @param string $format Format to get options for.
     * @return array Format options.
     */
    public function getFormatOptions(string $format): array {
        if (!isset($this->format_options[$format])) {
            throw new \InvalidArgumentException("Invalid format: {$format}");
        }
        return $this->format_options[$format];
    }

    /**
     * Validates rendering options.
     *
     * @param array  $options Options to validate.
     * @param string $format  Format to validate against.
     * @return bool True if valid.
     */
    public function validateOptions(array $options, string $format): bool {
        if (!isset($this->format_options[$format])) {
            return false;
        }

        foreach ($options as $key => $value) {
            if (!array_key_exists($key, $this->format_options[$format])) {
                return false;
            }

            $default_value = $this->format_options[$format][$key];
            if (gettype($value) !== gettype($default_value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Renders palette as HTML.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array        $options Rendering options.
     * @return string HTML output.
     */
    private function renderHtml(ColorPalette $palette, array $options): string {
        $output = "<div class=\"{$options['container_class']}\" style=\"display: flex; gap: {$options['spacing']};\">\n";

        foreach ($palette->getColors() as $color) {
            $output .= $this->renderHtmlSwatch($color, $options);
        }

        $output .= "</div>\n";
        return $output;
    }

    /**
     * Renders palette as SVG.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array        $options Rendering options.
     * @return string SVG output.
     */
    private function renderSvg(ColorPalette $palette, array $options): string {
        $colors = $palette->getColors();
        $total_width = $options['width'];
        $swatch_width = $options['swatch_size'];
        $spacing = $options['spacing'];

        $output = "<svg width=\"{$total_width}\" height=\"{$options['height']}\" xmlns=\"http://www.w3.org/2000/svg\">\n";

        foreach ($colors as $i => $color) {
            $x = $i * ($swatch_width + $spacing);
            $output .= $this->renderSvgSwatch($color, array_merge($options, ['x' => $x]));
        }

        $output .= "</svg>\n";
        return $output;
    }

    /**
     * Renders HTML swatch.
     *
     * @param string $color   Color to render.
     * @param array  $options Rendering options.
     * @return string HTML swatch.
     */
    private function renderHtmlSwatch(string $color, array $options): string {
        $style = "
            width: {$options['swatch_size']};
            height: {$options['swatch_size']};
            background-color: {$color};
            border-radius: {$options['border_radius']};
        ";

        $output = "<div class=\"{$options['swatch_class']}\" style=\"{$style}\">";

        if ($options['show_labels']) {
            $output .= "<span class=\"{$options['label_class']}\">{$color}</span>";
        }

        if ($options['show_info']) {
            $output .= "<div class=\"{$options['info_class']}\">";
            $output .= $this->renderColorInfo($color, ['separator' => '<br>']);
            $output .= "</div>";
        }

        $output .= "</div>\n";
        return $output;
    }

    /**
     * Renders SVG swatch.
     *
     * @param string $color   Color to render.
     * @param array  $options Rendering options.
     * @return string SVG swatch.
     */
    private function renderSvgSwatch(string $color, array $options): string {
        $x = $options['x'] ?? 0;
        $size = $options['swatch_size'];

        $output = "<rect x=\"{$x}\" y=\"0\" width=\"{$size}\" height=\"{$size}\" fill=\"{$color}\"/>\n";

        if ($options['show_labels']) {
            $text_x = $x + ($size / 2);
            $text_y = $size + $options['font_size'] + 5;
            $output .= "<text x=\"{$text_x}\" y=\"{$text_y}\"
                             font-family=\"{$options['font_family']}\"
                             font-size=\"{$options['font_size']}px\"
                             text-anchor=\"middle\">{$color}</text>\n";
        }

        return $output;
    }

    /**
     * Renders canvas swatch.
     *
     * @param string $color   Color to render.
     * @param array  $options Rendering options.
     * @return string Canvas instructions.
     */
    private function renderCanvasSwatch(string $color, array $options): string {
        $rgb = $this->formatter->hexToRgb($color);
        return "ctx.fillStyle = 'rgb({$rgb[0]}, {$rgb[1]}, {$rgb[2]})';";
    }

    /**
     * Renders palette as text.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array        $options Rendering options.
     * @return string Text output.
     */
    private function renderText(ColorPalette $palette, array $options): string {
        $output = [];

        if ($options['show_name']) {
            $output[] = $palette->getName();
        }

        foreach ($palette->getColors() as $color) {
            $line = $color;
            if ($options['show_info']) {
                $line .= $options['separator'] . $this->renderColorInfo($color, $options);
            }
            $output[] = $line;
        }

        return implode($options['separator'], $output);
    }

    /**
     * Renders palette as JSON.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array        $options Rendering options.
     * @return string JSON output.
     */
    private function renderJson(ColorPalette $palette, array $options): string {
        $data = [
            'name' => $palette->getName(),
            'colors' => $palette->getColors()
        ];

        if ($options['include_metadata']) {
            $data['metadata'] = $palette->getMetadata();
        }

        if ($options['include_info']) {
            $data['color_info'] = array_map(function ($color) {
                return [
                    'hex' => $color,
                    'rgb' => $this->formatter->hexToRgb($color),
                    'hsl' => $this->formatter->hexToHsl($color)
                ];
            }, $palette->getColors());
        }

        return json_encode(
            $data,
            $options['pretty_print'] ? JSON_PRETTY_PRINT : 0
        );
    }
} 
