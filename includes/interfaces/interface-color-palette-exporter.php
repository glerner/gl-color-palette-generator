<?php

/**
 * Color Palette Exporter Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface Color_Palette_Exporter_Interface
 * Defines the contract for exporting color palettes
 */
interface Color_Palette_Exporter_Interface extends Color_Palette_Formatter_Interface {
	/**
	 * Export color palette to specified format
	 *
	 * @param array  $palette Color palette data
	 * @param string $format  Export format (css, scss, json, etc.)
	 * @param array  $options Export options
	 * @return string|array Exported palette in specified format
	 * @throws \InvalidArgumentException If format is not supported
	 */
	public function export_palette( array $palette, string $format, array $options = array() ): string|array;

	/**
	 * Export palettes to JSON
	 *
	 * @param array $palettes Array of palettes to export.
	 * @return string JSON string.
	 */
	public function export_to_json( $palettes ): string;

	/**
	 * Export palettes to CSV
	 *
	 * @param array $palettes Array of palettes to export.
	 * @return string CSV string.
	 */
	public function export_to_csv( $palettes ): string;

	/**
	 * Import palettes from JSON
	 *
	 * @param string $json JSON string to import.
	 * @return array Imported palettes.
	 * @throws \Exception If import fails.
	 */
	public function import_from_json( $json ): array;
}
