<?php
/**
 * Color Palette Importer Interface
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;

/**
 * Interface for color palette import operations.
 */
interface Color_Palette_Importer_Interface {
	/**
	 * Imports a color palette from a string.
	 *
	 * @param string $data   Data to import.
	 * @param string $format Format of the data (json, css, etc.).
	 * @return Color_Palette Imported palette.
	 */
	public function import_from_string( string $data, string $format ): Color_Palette;

	/**
	 * Imports a color palette from a file.
	 *
	 * @param string $file_path Path to the file.
	 * @return Color_Palette Imported palette.
	 */
	public function import_from_file( string $file_path ): Color_Palette;

	/**
	 * Imports a color palette from a URL.
	 *
	 * @param string $url URL to import from.
	 * @return Color_Palette Imported palette.
	 */
	public function import_from_url( string $url ): Color_Palette;

	/**
	 * Gets supported import formats.
	 *
	 * @return array List of supported formats.
	 */
	public function get_supported_formats(): array;

	/**
	 * Validates import data.
	 *
	 * @param string $data   Data to validate.
	 * @param string $format Format to validate against.
	 * @return bool True if valid.
	 */
	public function validate_import_data( string $data, string $format ): bool;

	/**
	 * Gets format-specific import options.
	 *
	 * @param string $format Format to get options for.
	 * @return array Format options.
	 */
	public function get_format_options( string $format ): array;
}
