<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_Error;

/**
 * Color Exporter Interface
 *
 * Interface for exporting color palettes in different formats.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */
interface Color_Exporter_Interface {
	/**
	 * Export a palette
	 *
	 * @param Color_Palette $palette Palette to export
	 * @param string        $format  Export format
	 * @param array         $options Export options
	 * @return string|array|WP_Error Exported data or error
	 */
	public function export_palette( Color_Palette $palette, string $format, array $options = array() ): string|array|WP_Error;

	/**
	 * Get supported export formats
	 *
	 * @return array List of supported formats
	 */
	public function get_supported_formats(): array;
}
