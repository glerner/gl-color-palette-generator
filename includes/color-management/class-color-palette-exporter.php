<?php
/**
 * Color Palette Exporter Class
 *
 * Extends the Color_Palette_Formatter to provide additional export capabilities.
 * Supports all formats from Color_Palette_Formatter plus these additional formats:
 * - svg: SVG color swatches
 * - ase: Adobe Swatch Exchange format
 * - bootstrap: Bootstrap-specific SCSS variables
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Shade_Generator;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Exporter_Interface;
use GL_Color_Palette_Generator\Traits\Color_Shade_Generator_Trait;
use WP_Error;

/**
 * Class Color_Palette_Exporter
 *
 * Exports color palettes to various file formats. Inherits basic formatting capabilities
 * from Color_Palette_Formatter and adds support for additional export-specific formats.
 */
class Color_Palette_Exporter extends Color_Palette_Formatter implements Color_Palette_Exporter_Interface {
	use Color_Shade_Generator_Trait;

	/**
	 * Additional export-specific formats beyond those provided by Color_Palette_Formatter
	 *
	 * @see Color_Palette_Formatter::SUPPORTED_FORMATS for the base formatting options
	 */
	protected const EXPORT_FORMATS = array(
		'svg',      // SVG color swatches
		'ase',      // Adobe Swatch Exchange
		'bootstrap', // Bootstrap SCSS
	);

	private $shade_generator;
	private $color_utility;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->shade_generator = new Color_Shade_Generator();
		$this->color_utility   = new Color_Utility();
	}

	/**
	 * Get all supported formats
	 *
	 * @return array List of supported formats
	 */
	public function get_supported_formats(): array {
		return array_merge( parent::get_supported_formats(), self::EXPORT_FORMATS );
	}

	/**
	 * Export color palette to specified format
	 *
	 * @param array  $palette Color palette data
	 * @param string $format  Export format (css, scss, json, etc.)
	 * @param array  $options Export options
	 * @return string|array Exported palette in specified format
	 * @throws \InvalidArgumentException If format is not supported
	 */
	public function export_palette( array $palette, string $format, array $options = array() ): string|array {
		// Use shade generator for extended color variations
		$extended_palette = $this->shade_generator->generate_shades( $palette );

		// First try parent formatter
		try {
			return $this->format_palette( $extended_palette, $format, $options );
		} catch ( \Exception $e ) {
			return match ( $format ) {
				'svg' => $this->to_svg( $extended_palette, $options ),
				'ase' => $this->to_ase( $extended_palette, $options ),
				'bootstrap' => $this->to_bootstrap( $extended_palette, $options ),
				default => throw new \InvalidArgumentException(
					sprintf(
						__( 'Unsupported export format: %s', 'gl-color-palette-generator' ),
						$format
					)
				),
			};
		}
	}

	/**
	 * Export to SVG
	 *
	 * @param array $palette Color palette.
	 * @param array $options Export options.
	 * @return string SVG markup.
	 */
	protected function to_svg( array $palette, array $options ): string {
		$size   = $options['size'] ?? 100;
		$gap    = $options['gap'] ?? 10;
		$output = sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">',
			$size * count( $palette ),
			$size
		);

		$x = 0;
		foreach ( $palette as $name => $color ) {
			if ( $color === null || $color === '' ) {
				continue;
			}

			$output .= sprintf(
				'<rect x="%d" y="0" width="%d" height="%d" fill="%s"><title>%s</title></rect>',
				$x,
				$size - $gap,
				$size - $gap,
				esc_attr( $color ),
				esc_attr( $name )
			);
			$x      += $size;
		}

		$output .= '</svg>';
		return $output;
	}

	/**
	 * Export to Adobe Swatch Exchange (ASE)
	 *
	 * @param array $palette Color palette.
	 * @param array $options Export options.
	 * @return string Binary ASE file content.
	 */
	protected function to_ase( array $palette, array $options ): string {
		// ASE file header
		$output = pack( 'n*', 0xAEFF, 0x0001, 0x0000, count( $palette ) );

		foreach ( $palette as $name => $color ) {
			if ( $color === null || $color === '' ) {
				continue;
			}

			$rgb  = $this->color_utility->hex_to_rgb( $color );
			$name = substr( $name, 0, 32 ); // ASE name length limit

			// Color entry
			$output .= pack( 'n', 0x0001 ); // Entry start
			$output .= pack( 'n', strlen( $name ) + 1 ); // Name length + null
			$output .= $name . "\0"; // Name + null terminator
			$output .= pack(
				'CCCCCCCCCCCC',
				0x52,
				0x47,
				0x42,
				0x20, // "RGB "
				$rgb['r'],
				0, // R
				$rgb['g'],
				0, // G
				$rgb['b'],
				0, // B
				0,
				0  // Mode
			);
		}

		return $output;
	}

	/**
	 * Export to Bootstrap SCSS variables
	 *
	 * @param array $palette Color palette.
	 * @param array $options Export options.
	 * @return string Bootstrap SCSS variables.
	 */
	protected function to_bootstrap( array $palette, array $options ): string {
		$output = "// Custom color variables\n";

		foreach ( $palette as $name => $color ) {
			if ( $color === null || $color === '' ) {
				continue;
			}

			$varName = sanitize_title( $name );
			$output .= "\$theme-colors: (\n";
			$output .= "  \"{$varName}\": {$color},\n";
			$output .= ");\n";
		}

		return $output;
	}

	/**
	 * Export palettes to JSON
	 *
	 * @param array $palettes Array of palettes to export.
	 * @return string JSON string.
	 */
	public function export_to_json( $palettes ): string {
		$export_data = array(
			'version'     => GL_CPG_VERSION,
			'exported_at' => current_time( 'mysql' ),
			'palettes'    => $palettes,
		);

		return wp_json_encode( $export_data, JSON_PRETTY_PRINT );
	}

	/**
	 * Export palettes to CSV
	 *
	 * @param array $palettes Array of palettes to export.
	 * @return string CSV string.
	 */
	public function export_to_csv( $palettes ): string {
		$csv = "Name,Colors,Created At\n";

		foreach ( $palettes as $palette ) {
			$csv .= sprintf(
				'%s,%s,%s' . "\n",
				$this->esc_csv( $palette['name'] ),
				$this->esc_csv( implode( ', ', $palette['colors'] ) ),
				$this->esc_csv( $palette['created_at'] )
			);
		}

		return $csv;
	}

	/**
	 * Import palettes from JSON
	 *
	 * @param string $json JSON string to import.
	 * @return array Imported palettes.
	 * @throws \Exception If import fails.
	 */
	public function import_from_json( $json ): array {
		$data = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( __( 'Invalid JSON format', 'gl-color-palette-generator' ) );
		}

		if ( ! isset( $data['palettes'] ) || ! is_array( $data['palettes'] ) ) {
			throw new \Exception( __( 'Invalid palette data format', 'gl-color-palette-generator' ) );
		}

		return $data['palettes'];
	}

	/**
	 * Import palette from CSV file
	 *
	 * @param string $file_path Path to CSV file.
	 * @return array|WP_Error Imported palette or error.
	 */
	public function import_from_csv( string $file_path ): array|WP_Error {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'CSV file not found', 'gl-color-palette-generator' ) );
		}

		$palette = array();
		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			while ( ( $data = fgetcsv( $handle ) ) !== false ) {
				if ( count( $data ) >= 2 ) {
					$color = $data[1];
					if ( $this->color_utility->is_valid_hex_color( $color ) ) {
						$palette[ $data[0] ] = $color;
					}
				}
			}
			fclose( $handle );
		}

		if ( $palette === array() || count( $palette ) === 0 ) {
			return new WP_Error( 'invalid_csv', __( 'No valid colors found in CSV', 'gl-color-palette-generator' ) );
		}

		return $palette;
	}

	/**
	 * Escape CSV value
	 *
	 * @param string $value Value to escape
	 * @return string Escaped value
	 */
	private function esc_csv( string $value ): string {
		if ( strpos( $value, ',' ) !== false || strpos( $value, '"' ) !== false || strpos( $value, "\n" ) !== false ) {
			return '"' . str_replace( '"', '""', $value ) . '"';
		}
		return $value;
	}
}
