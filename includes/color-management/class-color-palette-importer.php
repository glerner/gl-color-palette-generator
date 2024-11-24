<?php
/**
 * Color Palette Importer Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteImporterInterface;
use GLColorPalette\ColorPalette;
use GLColorPalette\ColorPaletteFormatter;

/**
 * Handles color palette import operations.
 */
class ColorPaletteImporter implements ColorPaletteImporterInterface {
    /**
     * Color formatter instance.
     *
     * @var ColorPaletteFormatter
     */
    private ColorPaletteFormatter $formatter;

    /**
     * Supported import formats.
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
        'image'
    ];

    /**
     * Format-specific options.
     *
     * @var array
     */
    private array $format_options = [
        'css' => [
            'variable_prefix' => '--',
            'parse_comments' => true
        ],
        'scss' => [
            'variable_prefix' => '$',
            'parse_mixins' => true
        ],
        'less' => [
            'variable_prefix' => '@',
            'parse_mixins' => true
        ],
        'image' => [
            'max_colors' => 10,
            'algorithm' => 'kmeans',
            'quality' => 'high'
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
     * Imports a color palette from a string.
     *
     * @param string $data   Data to import.
     * @param string $format Format of the data.
     * @return ColorPalette Imported palette.
     * @throws \InvalidArgumentException If format is not supported.
     */
    public function importFromString(string $data, string $format): ColorPalette {
        if (!$this->validateImportData($data, $format)) {
            throw new \InvalidArgumentException("Invalid {$format} data");
        }

        return match ($format) {
            'json' => $this->importFromJson($data),
            'css' => $this->importFromCss($data),
            'scss' => $this->importFromScss($data),
            'less' => $this->importFromLess($data),
            'ase' => $this->importFromAse($data),
            'act' => $this->importFromAct($data),
            'gpl' => $this->importFromGpl($data),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Imports a color palette from a file.
     *
     * @param string $file_path Path to the file.
     * @return ColorPalette Imported palette.
     * @throws \InvalidArgumentException If file is invalid.
     */
    public function importFromFile(string $file_path): ColorPalette {
        if (!file_exists($file_path)) {
            throw new \InvalidArgumentException("File not found: {$file_path}");
        }

        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {
            return $this->importFromImage($file_path);
        }

        $data = file_get_contents($file_path);
        return $this->importFromString($data, $extension);
    }

    /**
     * Imports a color palette from a URL.
     *
     * @param string $url URL to import from.
     * @return ColorPalette Imported palette.
     * @throws \InvalidArgumentException If URL is invalid.
     */
    public function importFromUrl(string $url): ColorPalette {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: {$url}");
        }

        $data = wp_remote_get($url);
        if (is_wp_error($data)) {
            throw new \InvalidArgumentException("Failed to fetch URL: {$url}");
        }

        $content = wp_remote_retrieve_body($data);
        $content_type = wp_remote_retrieve_header($data, 'content-type');

        return match (true) {
            str_contains($content_type, 'image/') => $this->importFromImageData($content),
            str_contains($content_type, 'application/json') => $this->importFromJson($content),
            str_contains($content_type, 'text/css') => $this->importFromCss($content),
            default => throw new \InvalidArgumentException("Unsupported content type: {$content_type}")
        };
    }

    /**
     * Imports from JSON format.
     *
     * @param string $data JSON data.
     * @return ColorPalette Imported palette.
     */
    private function importFromJson(string $data): ColorPalette {
        $parsed = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        return new ColorPalette([
            'name' => $parsed['name'] ?? 'Imported Palette',
            'colors' => $parsed['colors'] ?? [],
            'metadata' => $parsed['metadata'] ?? []
        ]);
    }

    /**
     * Imports from CSS format.
     *
     * @param string $data CSS data.
     * @return ColorPalette Imported palette.
     */
    private function importFromCss(string $data): ColorPalette {
        $colors = [];
        $prefix = $this->format_options['css']['variable_prefix'];

        preg_match_all("/{$prefix}([^:]+):\s*([^;]+);/", $data, $matches);

        foreach ($matches[2] as $color) {
            if ($this->formatter->isValidFormat($color, 'hex')) {
                $colors[] = $this->formatter->normalizeColor($color);
            }
        }

        return new ColorPalette([
            'name' => 'CSS Import',
            'colors' => $colors,
            'metadata' => ['source' => 'css']
        ]);
    }

    /**
     * Imports from SCSS format.
     *
     * @param string $data SCSS data.
     * @return ColorPalette Imported palette.
     */
    private function importFromScss(string $data): ColorPalette {
        $colors = [];
        $prefix = $this->format_options['scss']['variable_prefix'];

        preg_match_all("/{$prefix}([^:]+):\s*([^;]+);/", $data, $matches);

        foreach ($matches[2] as $color) {
            if ($this->formatter->isValidFormat($color, 'hex')) {
                $colors[] = $this->formatter->normalizeColor($color);
            }
        }

        return new ColorPalette([
            'name' => 'SCSS Import',
            'colors' => $colors,
            'metadata' => ['source' => 'scss']
        ]);
    }

    /**
     * Imports from LESS format.
     *
     * @param string $data LESS data.
     * @return ColorPalette Imported palette.
     */
    private function importFromLess(string $data): ColorPalette {
        $colors = [];
        $prefix = $this->format_options['less']['variable_prefix'];

        preg_match_all("/{$prefix}([^:]+):\s*([^;]+);/", $data, $matches);

        foreach ($matches[2] as $color) {
            if ($this->formatter->isValidFormat($color, 'hex')) {
                $colors[] = $this->formatter->normalizeColor($color);
            }
        }

        return new ColorPalette([
            'name' => 'LESS Import',
            'colors' => $colors,
            'metadata' => ['source' => 'less']
        ]);
    }

    /**
     * Imports from Adobe ASE format.
     *
     * @param string $data ASE data.
     * @return ColorPalette Imported palette.
     */
    private function importFromAse(string $data): ColorPalette {
        / ASE format implementation...
        throw new \RuntimeException('ASE import not yet implemented');
    }

    /**
     * Imports from Adobe ACT format.
     *
     * @param string $data ACT data.
     * @return ColorPalette Imported palette.
     */
    private function importFromAct(string $data): ColorPalette {
        / ACT format implementation...
        throw new \RuntimeException('ACT import not yet implemented');
    }

    /**
     * Imports from GIMP palette format.
     *
     * @param string $data GPL data.
     * @return ColorPalette Imported palette.
     */
    private function importFromGpl(string $data): ColorPalette {
        / GPL format implementation...
        throw new \RuntimeException('GPL import not yet implemented');
    }

    /**
     * Imports from image file.
     *
     * @param string $file_path Path to image file.
     * @return ColorPalette Imported palette.
     */
    private function importFromImage(string $file_path): ColorPalette {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension required for image import');
        }

        $image = imagecreatefromstring(file_get_contents($file_path));
        if (!$image) {
            throw new \InvalidArgumentException('Invalid image file');
        }

        $options = $this->format_options['image'];
        $colors = $this->extractColorsFromImage($image, $options);

        imagedestroy($image);

        return new ColorPalette([
            'name' => 'Image Import',
            'colors' => $colors,
            'metadata' => [
                'source' => 'image',
                'algorithm' => $options['algorithm']
            ]
        ]);
    }

    /**
     * Extracts colors from an image.
     *
     * @param resource $image   Image resource.
     * @param array    $options Extraction options.
     * @return array Extracted colors.
     */
    private function extractColorsFromImage($image, array $options): array {
        / Basic implementation - could be improved with different algorithms
        $width = imagesx($image);
        $height = imagesy($image);
        $colors = [];
        $samples = min($width * $height, 1000); / Sample up to 1000 pixels

        for ($i = 0; $i < $samples; $i++) {
            $x = rand(0, $width - 1);
            $y = rand(0, $height - 1);
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $colors[] = sprintf('#%02X%02X%02X', $r, $g, $b);
        }

        / Remove duplicates and limit to max_colors
        $colors = array_unique($colors);
        $colors = array_slice($colors, 0, $options['max_colors']);

        return $colors;
    }

    /**
     * Gets supported import formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array {
        return $this->supported_formats;
    }

    /**
     * Validates import data.
     *
     * @param string $data   Data to validate.
     * @param string $format Format to validate against.
     * @return bool True if valid.
     */
    public function validateImportData(string $data, string $format): bool {
        if (!in_array($format, $this->supported_formats)) {
            return false;
        }

        return match ($format) {
            'json' => json_decode($data) !== null,
            'css', 'scss', 'less' => (bool) preg_match('/[#]([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/', $data),
            'ase', 'act', 'gpl' => strlen($data) > 0,
            default => true
        };
    }

    /**
     * Gets format-specific import options.
     *
     * @param string $format Format to get options for.
     * @return array Format options.
     */
    public function getFormatOptions(string $format): array {
        if (!isset($this->format_options[$format])) {
            throw new \InvalidArgumentException("No options available for format: {$format}");
        }
        return $this->format_options[$format];
    }
} 
