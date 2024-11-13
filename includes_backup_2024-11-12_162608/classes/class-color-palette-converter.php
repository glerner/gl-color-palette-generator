<?php

namespace GLColorPalette;

/**
 * Color Palette Converter Class
 *
 * Handles conversion of color palettes between different formats and systems.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteConverter {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private $formatter;

    /**
     * Supported export formats.
     *
     * @var array
     */
    private $supported_formats = [
        'css',      // CSS Variables
        'scss',     // SCSS Variables
        'less',     // LESS Variables
        'json',     // JSON Format
        'svg',      // SVG Swatches
        'ase',      // Adobe Swatch Exchange
        'gpl',      // GIMP Palette
        'act',      // Adobe Color Table
        'sketchpalette' // Sketch Palette
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
     * Converts a palette to specified format.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param string $format Target format.
     * @param array $options Optional. Conversion options.
     * @return string Converted palette data.
     * @throws \InvalidArgumentException If format is unsupported.
     */
    public function convert_palette(
        ColorPalette $palette,
        string $format,
        array $options = []
    ): string {
        if (!in_array($format, $this->supported_formats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        $method = "to_{$format}";
        return $this->$method($palette, $options);
    }

    /**
     * Converts palette to CSS variables.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string CSS variables.
     */
    private function to_css(ColorPalette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $output = ":root {\n";

        foreach ($palette->get_colors() as $index => $color) {
            $name = $this->generate_color_name($color, $index, $options);
            $output .= "    --{$prefix}-{$name}: {$color};\n";
        }

        $output .= "}\n";
        return $output;
    }

    /**
     * Converts palette to SCSS variables.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string SCSS variables.
     */
    private function to_scss(ColorPalette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $output = "// {$palette->get_name()}\n";

        foreach ($palette->get_colors() as $index => $color) {
            $name = $this->generate_color_name($color, $index, $options);
            $output .= "\${$prefix}-{$name}: {$color};\n";
        }

        return $output;
    }

    /**
     * Converts palette to LESS variables.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string LESS variables.
     */
    private function to_less(ColorPalette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $output = "// {$palette->get_name()}\n";

        foreach ($palette->get_colors() as $index => $color) {
            $name = $this->generate_color_name($color, $index, $options);
            $output .= "@{$prefix}-{$name}: {$color};\n";
        }

        return $output;
    }

    /**
     * Converts palette to JSON format.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string JSON data.
     */
    private function to_json(ColorPalette $palette, array $options = []): string {
        $data = [
            'name' => $palette->get_name(),
            'colors' => array_map(function($color) use ($options) {
                return [
                    'hex' => $color,
                    'rgb' => $this->formatter->format_color($color, 'rgb'),
                    'hsl' => $this->formatter->format_color($color, 'hsl')
                ];
            }, $palette->get_colors()),
            'metadata' => $palette->get_metadata()
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Converts palette to SVG swatches.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string SVG markup.
     */
    private function to_svg(ColorPalette $palette, array $options = []): string {
        $width = $options['width'] ?? 100;
        $height = $options['height'] ?? 100;
        $colors = $palette->get_colors();
        $count = count($colors);

        $output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $output .= "<svg xmlns=\"http://www.w3.org/2000/svg\" ";
        $output .= "width=\"{$width}\" height=\"{$height}\">\n";

        foreach ($colors as $index => $color) {
            $x = 0;
            $y = ($height / $count) * $index;
            $h = $height / $count;

            $output .= "    <rect x=\"{$x}\" y=\"{$y}\" ";
            $output .= "width=\"{$width}\" height=\"{$h}\" ";
            $output .= "fill=\"{$color}\" />\n";
        }

        $output .= "</svg>";
        return $output;
    }

    /**
     * Converts palette to Adobe Swatch Exchange format.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string ASE file content.
     */
    private function to_ase(ColorPalette $palette, array $options = []): string {
        $output = "ASEF\x00\x01\x00\x00"; // ASE file signature and version
        $colors = $palette->get_colors();
        $count = count($colors);

        // Write block count
        $output .= pack('N', $count);

        foreach ($colors as $index => $color) {
            $name = $this->generate_color_name($color, $index, $options);
            $rgb = $this->hex_to_rgb($color);

            // Write color block
            $output .= "\x00\x01"; // Start color block
            $output .= pack('n', strlen($name) + 1); // Name length
            $output .= $name . "\x00"; // Name with null terminator
            $output .= "RGB "; // Color model
            $output .= pack('f*', $rgb[0]/255, $rgb[1]/255, $rgb[2]/255); // RGB values
            $output .= "\x00\x00"; // Color type (global)
        }

        return $output;
    }

    /**
     * Converts palette to GIMP Palette format.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string GPL file content.
     */
    private function to_gpl(ColorPalette $palette, array $options = []): string {
        $output = "GIMP Palette\n";
        $output .= "Name: {$palette->get_name()}\n";
        $output .= "#\n";

        foreach ($palette->get_colors() as $index => $color) {
            $rgb = $this->hex_to_rgb($color);
            $name = $this->generate_color_name($color, $index, $options);
            $output .= sprintf(
                "%3d %3d %3d\t%s\n",
                $rgb[0],
                $rgb[1],
                $rgb[2],
                $name
            );
        }

        return $output;
    }

    /**
     * Converts palette to Adobe Color Table format.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string ACT file content.
     */
    private function to_act(ColorPalette $palette, array $options = []): string {
        $output = '';
        $colors = $palette->get_colors();

        // Pad to 256 colors
        while (count($colors) < 256) {
            $colors[] = '#000000';
        }

        foreach (array_slice($colors, 0, 256) as $color) {
            $rgb = $this->hex_to_rgb($color);
            $output .= pack('C*', $rgb[0], $rgb[1], $rgb[2]);
        }

        return $output;
    }

    /**
     * Converts palette to Sketch Palette format.
     *
     * @param ColorPalette $palette Palette to convert.
     * @param array $options Conversion options.
     * @return string Sketch palette JSON.
     */
    private function to_sketchpalette(ColorPalette $palette, array $options = []): string {
        $colors = array_map(function($color) {
            $rgb = $this->hex_to_rgb($color);
            return [
                'red' => $rgb[0] / 255,
                'green' => $rgb[1] / 255,
                'blue' => $rgb[2] / 255,
                'alpha' => 1
            ];
        }, $palette->get_colors());

        $data = [
            'compatibleVersion' => '2.0',
            'pluginVersion' => '2.29',
            'colors' => $colors
        ];

        return json_encode($data);
    }

    /**
     * Generates a color name based on index and options.
     *
     * @param string $color Color value.
     * @param int $index Color index.
     * @param array $options Naming options.
     * @return string Generated name.
     */
    private function generate_color_name(
        string $color,
        int $index,
        array $options
    ): string {
        if (isset($options['names']) && isset($options['names'][$index])) {
            return $options['names'][$index];
        }

        if (isset($options['name_format'])) {
            return sprintf($options['name_format'], $index + 1);
        }

        return (string)($index + 1);
    }

    /**
     * Converts hex color to RGB array.
     *
     * @param string $hex Hex color value.
     * @return array RGB values [r, g, b].
     */
    private function hex_to_rgb(string $hex): array {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
} 
