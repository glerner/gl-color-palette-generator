<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Exporter Interface
 *
 * Defines the contract for exporting color palettes to various formats.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteExporterInterface {
    /**
     * Exports a color palette.
     *
     * @param ColorPalette $palette Palette to export.
     * @param string       $format  Export format.
     * @param array        $options Export options.
     * @return string Exported data.
     */
    public function exportPalette(ColorPalette $palette, string $format, array $options = []): string;

    /**
     * Exports to file.
     *
     * @param ColorPalette $palette  Palette to export.
     * @param string       $filename Target filename.
     * @param string       $format   Export format.
     * @param array        $options  Export options.
     * @return bool True on success.
     */
    public function exportToFile(ColorPalette $palette, string $filename, string $format, array $options = []): bool;

    /**
     * Gets supported export formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array;

    /**
     * Gets format-specific options.
     *
     * @param string $format Format to get options for.
     * @return array Format options.
     */
    public function getFormatOptions(string $format): array;

    /**
     * Gets file extension for format.
     *
     * @param string $format Format to get extension for.
     * @return string File extension.
     */
    public function getFileExtension(string $format): string;

    /**
     * Validates export options.
     *
     * @param array  $options Options to validate.
     * @param string $format  Format to validate against.
     * @return bool True if valid.
     */
    public function validateOptions(array $options, string $format): bool;
}
