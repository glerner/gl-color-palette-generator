<?php
/**
 * Color Palette Importer Interface
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Interface for color palette import operations.
 */
interface ColorPaletteImporterInterface {
    /**
     * Imports a color palette from a string.
     *
     * @param string $data   Data to import.
     * @param string $format Format of the data (json, css, etc.).
     * @return ColorPalette Imported palette.
     */
    public function importFromString(string $data, string $format): ColorPalette;

    /**
     * Imports a color palette from a file.
     *
     * @param string $file_path Path to the file.
     * @return ColorPalette Imported palette.
     */
    public function importFromFile(string $file_path): ColorPalette;

    /**
     * Imports a color palette from a URL.
     *
     * @param string $url URL to import from.
     * @return ColorPalette Imported palette.
     */
    public function importFromUrl(string $url): ColorPalette;

    /**
     * Gets supported import formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array;

    /**
     * Validates import data.
     *
     * @param string $data   Data to validate.
     * @param string $format Format to validate against.
     * @return bool True if valid.
     */
    public function validateImportData(string $data, string $format): bool;

    /**
     * Gets format-specific import options.
     *
     * @param string $format Format to get options for.
     * @return array Format options.
     */
    public function getFormatOptions(string $format): array;
}
