<?php
/**
 * Color Palette Exporter Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteExporterInterface;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteFormatter;

/**
 * Handles color palette export operations.
 */
class ColorPaletteExporter implements ColorPaletteExporterInterface {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private ColorPaletteFormatter $formatter;

    /**
     * Supported export formats.
     *
     * @var array
     */
    private array $supported_formats = [
        'json',
        'css',
        'scss',
        'less',
        'ase',
        'act',
        'gpl',
        'xml',
        'yaml'
    ];

    /**
     * Format-specific options.
     *
     * @var array
     */
    private array $format_options = [
        'json' => [
            'pretty_print' => true,
            'include_metadata' => true
        ],
        'css' => [
            'prefix' => '--color',
            'include_comments' => true,
            'format' => 'hex'  // hex, rgb, hsl
        ],
        'scss' => [
            'prefix' => '$color',
            'include_comments' => true,
            'format' => 'hex'
        ],
        'less' => [
            'prefix' => '@color',
            'include_comments' => true,
            'format' => 'hex'
        ],
        'ase' => [
            'version' => '1.0',
            'color_model' => 'RGB'  // RGB, CMYK, LAB
        ],
        'act' => [
            'max_colors' => 256
        ],
        'gpl' => [
            'include_header' => true,
            'columns' => 'rgb'  // rgb, hex
        ],
        'xml' => [
            'pretty_print' => true,
            'include_metadata' => true,
            'root_element' => 'palette'
        ],
        'yaml' => [
            'include_metadata' => true,
            'inline_level' => 2
        ]
    ];

    /**
     * File extensions map.
     *
     * @var array
     */
    private array $file_extensions = [
        'json' => 'json',
        'css'  => 'css',
        'scss' => 'scss',
        'less' => 'less',
        'ase'  => 'ase',
        'act'  => 'act',
        'gpl'  => 'gpl',
        'xml'  => 'xml',
        'yaml' => 'yml'
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
     * Exports a color palette.
     *
     * @param ColorPalette $palette Palette to export.
     * @param string       $format  Export format.
     * @param array        $options Export options.
     * @return string Exported data.
     */
    public function exportPalette(ColorPalette $palette, string $format, array $options = []): string {
        if (!in_array($format, $this->supported_formats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        $options = array_merge($this->format_options[$format], $options);

        return match ($format) {
            'json' => $this->exportToJson($palette, $options),
            'css'  => $this->exportToCss($palette, $options),
            'scss' => $this->exportToScss($palette, $options),
            'less' => $this->exportToLess($palette, $options),
            'ase'  => $this->exportToAse($palette, $options),
            'act'  => $this->exportToAct($palette, $options),
            'gpl'  => $this->exportToGpl($palette, $options),
            'xml'  => $this->exportToXml($palette, $options),
            'yaml' => $this->exportToYaml($palette, $options),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Exports to file.
     *
     * @param ColorPalette $palette  Palette to export.
     * @param string       $filename Target filename.
     * @param string       $format   Export format.
     * @param array        $options  Export options.
     * @return bool True on success.
     */
    public function exportToFile(ColorPalette $palette, string $filename, string $format, array $options = []): bool {
        $data = $this->exportPalette($palette, $format, $options);
        return (bool) file_put_contents($filename, $data);
    }

    /**
     * Gets supported export formats.
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
     * Gets file extension for format.
     *
     * @param string $format Format to get extension for.
     * @return string File extension.
     */
    public function getFileExtension(string $format): string {
        if (!isset($this->file_extensions[$format])) {
            throw new \InvalidArgumentException("Invalid format: {$format}");
        }
        return $this->file_extensions[$format];
    }

    /**
     * Validates export options.
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
     * Exports to JSON format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string JSON data.
     */
    private function exportToJson(ColorPalette $palette, array $options): string {
        $data = [
            'name' => $palette->getName(),
            'colors' => $palette->getColors()
        ];

        if ($options['include_metadata']) {
            $data['metadata'] = $palette->getMetadata();
        }

        return json_encode(
            $data,
            $options['pretty_print'] ? JSON_PRETTY_PRINT : 0
        );
    }

    /**
     * Exports to CSS format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string CSS data.
     */
    private function exportToCss(ColorPalette $palette, array $options): string {
        $output = '';
        if ($options['include_comments']) {
            $output .= "/* {$palette->getName()} */\n";
        }

        $output .= ":root {\n";
        foreach ($palette->getColors() as $index => $color) {
            $value = $this->formatColorValue($color, $options['format']);
            $output .= "  {$options['prefix']}-{$index}: {$value};\n";
        }
        $output .= "}\n";

        return $output;
    }

    /**
     * Exports to SCSS format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string SCSS data.
     */
    private function exportToScss(ColorPalette $palette, array $options): string {
        $output = '';
        if ($options['include_comments']) {
            $output .= "// {$palette->getName()}\n";
        }

        foreach ($palette->getColors() as $index => $color) {
            $value = $this->formatColorValue($color, $options['format']);
            $output .= "{$options['prefix']}-{$index}: {$value};\n";
        }

        return $output;
    }

    /**
     * Exports to LESS format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string LESS data.
     */
    private function exportToLess(ColorPalette $palette, array $options): string {
        $output = '';
        if ($options['include_comments']) {
            $output .= "// {$palette->getName()}\n";
        }

        foreach ($palette->getColors() as $index => $color) {
            $value = $this->formatColorValue($color, $options['format']);
            $output .= "{$options['prefix']}-{$index}: {$value};\n";
        }

        return $output;
    }

    /**
     * Exports to ASE format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string ASE data.
     */
    private function exportToAse(ColorPalette $palette, array $options): string {
        // ASE format requires binary data manipulation
        $output = "ASEF";  // File signature
        $output .= pack("n", 1);  // Major version
        $output .= pack("n", 0);  // Minor version

        $blocks = [];
        foreach ($palette->getColors() as $index => $color) {
            $rgb = $this->formatter->hexToRgb($color);
            $blocks[] = $this->createAseColorBlock($rgb, "color-{$index}", $options['color_model']);
        }

        $output .= pack("N", count($blocks));  // Number of blocks
        $output .= implode('', $blocks);

        return $output;
    }

    /**
     * Exports to ACT format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string ACT data.
     */
    private function exportToAct(ColorPalette $palette, array $options): string {
        $colors = array_slice($palette->getColors(), 0, $options['max_colors']);
        $output = '';

        foreach ($colors as $color) {
            $rgb = $this->formatter->hexToRgb($color);
            $output .= pack("C*", $rgb[0], $rgb[1], $rgb[2]);
        }

        // Pad to 256 colors if necessary
        $padding_length = ($options['max_colors'] - count($colors)) * 3;
        if ($padding_length > 0) {
            $output .= str_repeat("\x00", $padding_length);
        }

        return $output;
    }

    /**
     * Exports to GPL format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string GPL data.
     */
    private function exportToGpl(ColorPalette $palette, array $options): string {
        $output = '';
        if ($options['include_header']) {
            $output .= "GIMP Palette\n";
            $output .= "Name: {$palette->getName()}\n";
            $output .= "#\n";
        }

        foreach ($palette->getColors() as $index => $color) {
            $rgb = $this->formatter->hexToRgb($color);
            if ($options['columns'] === 'rgb') {
                $output .= sprintf("%3d %3d %3d\tcolor-%d\n", $rgb[0], $rgb[1], $rgb[2], $index);
            } else {
                $output .= sprintf("%s\tcolor-%d\n", substr($color, 1), $index);
            }
        }

        return $output;
    }

    /**
     * Exports to XML format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string XML data.
     */
    private function exportToXml(ColorPalette $palette, array $options): string {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $options['pretty_print'];

        $root = $dom->createElement($options['root_element']);
        $dom->appendChild($root);

        $name = $dom->createElement('name', $palette->getName());
        $root->appendChild($name);

        $colors = $dom->createElement('colors');
        $root->appendChild($colors);

        foreach ($palette->getColors() as $index => $color) {
            $colorElement = $dom->createElement('color');
            $colorElement->setAttribute('index', (string)$index);
            $colorElement->setAttribute('hex', $color);

            $rgb = $this->formatter->hexToRgb($color);
            $colorElement->setAttribute('rgb', implode(',', $rgb));

            $colors->appendChild($colorElement);
        }

        if ($options['include_metadata']) {
            $metadata = $dom->createElement('metadata');
            foreach ($palette->getMetadata() as $key => $value) {
                $meta = $dom->createElement($key);
                $meta->appendChild($dom->createTextNode((string)$value));
                $metadata->appendChild($meta);
            }
            $root->appendChild($metadata);
        }

        return $dom->saveXML();
    }

    /**
     * Exports to YAML format.
     *
     * @param ColorPalette $palette Palette to export.
     * @param array        $options Export options.
     * @return string YAML data.
     */
    private function exportToYaml(ColorPalette $palette, array $options): string {
        $data = [
            'name' => $palette->getName(),
            'colors' => $palette->getColors()
        ];

        if ($options['include_metadata']) {
            $data['metadata'] = $palette->getMetadata();
        }

        return yaml_emit($data, $options['inline_level']);
    }

    /**
     * Creates an ASE color block.
     *
     * @param array  $rgb         RGB color values.
     * @param string $name        Color name.
     * @param string $color_model Color model.
     * @return string Binary block data.
     */
    private function createAseColorBlock(array $rgb, string $name, string $color_model): string {
        $block = "\x00\x01";  // Color block type
        $block .= pack("n", 4 + strlen($name) * 2 + 4);  // Block length
        $block .= pack("n", strlen($name));  // Name length
        $block .= mb_convert_encoding($name, 'UTF-16BE');  // Name
        $block .= $color_model;  // Color model

        // Color values as floats
        foreach ($rgb as $value) {
            $block .= pack("f", $value / 255);
        }

        return $block;
    }

    /**
     * Formats a color value.
     *
     * @param string $color  Color to format.
     * @param string $format Output format.
     * @return string Formatted color.
     */
    private function formatColorValue(string $color, string $format): string {
        return match ($format) {
            'hex' => $color,
            'rgb' => $this->formatRgb($this->formatter->hexToRgb($color)),
            'hsl' => $this->formatHsl($this->formatter->hexToHsl($color)),
            default => $color
        };
    }

    /**
     * Formats RGB values.
     *
     * @param array $rgb RGB values.
     * @return string Formatted RGB.
     */
    private function formatRgb(array $rgb): string {
        return sprintf('rgb(%d, %d, %d)', $rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * Formats HSL values.
     *
     * @param array $hsl HSL values.
     * @return string Formatted HSL.
     */
    private function formatHsl(array $hsl): string {
        return sprintf('hsl(%d, %d%%, %d%%)',
            round($hsl[0]),
            round($hsl[1]),
            round($hsl[2])
        );
    }
}
