<?php
/**
 * Color Palette Importer Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Importer_Interface;
use GL_Color_Palette_Generator\Models\Color_Palette;
use GL_Color_Palette_Generator\Types\Color_Types;
use WP_Error;

/**
 * Handles color palette import operations.
 */
class Color_Palette_Importer implements Color_Palette_Importer_Interface {
	/**
	 * Color utility instance.
	 *
	 * @var Color_Utility
	 */
	private Color_Utility $color_utility;

	/**
	 * Supported import formats.
	 *
	 * @var array
	 */
	private array $supported_formats = array(
		'json',
		'css',
		'scss',
		'less',
		'ase',
		'act',
		'gpl',
		'image',
	);

	/**
	 * Format-specific options.
	 *
	 * @var array
	 */
	private array $format_options = array(
		'css'  => array(
			'variable_prefix' => '--',
			'class_prefix'    => 'color-',
		),
		'scss' => array(
			'variable_prefix' => '$',
		),
		'less' => array(
			'variable_prefix' => '@',
		),
	);

	/**
	 * Constructor.
	 *
	 * @param Color_Utility $color_utility Color utility instance.
	 */
	public function __construct( Color_Utility $color_utility ) {
		$this->color_utility = $color_utility;
	}

	/**
	 * Import a color palette from a string.
	 *
	 * @param string $data   Data to import.
	 * @param string $format Format of the data (json, css, etc.).
	 * @return Color_Palette|WP_Error Imported palette or error.
	 */
	public function import_palette_from_string( string $data, string $format ): Color_Palette|WP_Error {
		if ( ! $this->is_valid_format( $format ) ) {
			return new WP_Error( 'invalid_format', __( 'Unsupported import format', 'gl-color-palette-generator' ) );
		}
		try {
			$colors = array();
			switch ( $format ) {
				case 'json':
					$colors = $this->import_palette_from_json( $data );
					break;
				case 'css':
					$colors = $this->import_palette_from_css( $data );
					break;
				case 'scss':
					$colors = $this->import_palette_from_scss( $data );
					break;
				case 'less':
					$colors = $this->import_palette_from_less( $data );
					break;
				case 'image':
					$colors = $this->import_palette_from_image( $data );
					break;
			}

			if ( count( $colors ) === 0 ) {
				return new WP_Error( 'import_failed', __( 'No valid colors found in import data', 'gl-color-palette-generator' ) );
			}

			return new Color_Palette( $colors );
		} catch ( \Exception $e ) {
			return new WP_Error( 'import_failed', $e->getMessage() );
		}
	}

	/**
	 * Import a color palette from a file.
	 *
	 * @param string $file_path Path to the file.
	 * @return Color_Palette|WP_Error Imported palette or error.
	 */
	public function import_palette_from_file( string $file_path ): Color_Palette|WP_Error {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'Import file not found', 'gl-color-palette-generator' ) );
		}

		$extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
		$format    = $this->get_format_from_extension( $extension );

		if ( $format === null ) {
			return new WP_Error( 'invalid_format', __( 'Unsupported file format', 'gl-color-palette-generator' ) );
		}

		$content = file_get_contents( $file_path );
		if ( $content === false ) {
			return new WP_Error( 'read_error', __( 'Failed to read import file', 'gl-color-palette-generator' ) );
		}

		return $this->import_palette_from_string( $content, $format );
	}

	/**
	 * Import a color palette from a URL.
	 *
	 * @param string $url URL to import from.
	 * @return Color_Palette|WP_Error Imported palette or error.
	 */
	public function import_palette_from_url( string $url ): Color_Palette|WP_Error {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', __( 'Invalid URL provided', 'gl-color-palette-generator' ) );
		}

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$content = wp_remote_retrieve_body( $response );
		if ( $content === '' ) {
			return new WP_Error( 'empty_response', __( 'No content retrieved from URL', 'gl-color-palette-generator' ) );
		}

		// Try to determine format from content-type or file extension
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		$format       = $this->get_format_from_content_type( $content_type );

		if ( $format === null ) {
			$format = $this->get_format_from_extension( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		}

		if ( $format === null ) {
			return new WP_Error( 'unknown_format', __( 'Could not determine import format', 'gl-color-palette-generator' ) );
		}

		return $this->import_palette_from_string( $content, $format );
	}

	/**
	 * Import colors from an image.
	 *
	 * @param string|\GdImage $image Image data or GD resource.
	 * @return array Array of colors.
	 */
	private function import_palette_from_image( $image ): array {
		if ( is_string( $image ) ) {
			$image_resource = @imagecreatefromstring( $image );
			if ( ! $image_resource ) {
				throw new \Exception( __( 'Invalid image data', 'gl-color-palette-generator' ) );
			}
		} else {
			$image_resource = $image;
		}

		$colors = $this->extract_colors_from_image( $image_resource );
		imagedestroy( $image_resource );

		return $colors;
	}

	/**
	 * Extract dominant colors from an image.
	 *
	 * @param \GdImage $image GD image resource.
	 * @return array Array of hex colors.
	 */
	private function extract_colors_from_image( \GdImage $image ): array {
		$width       = imagesx( $image );
		$height      = imagesy( $image );
		$colors      = array();
		$sample_size = 10; // Sample every 10th pixel

		for ( $x = 0; $x < $width; $x += $sample_size ) {
			for ( $y = 0; $y < $height; $y += $sample_size ) {
				$rgb = imagecolorat( $image, $x, $y );
				$r   = ( $rgb >> 16 ) & 0xFF;
				$g   = ( $rgb >> 8 ) & 0xFF;
				$b   = $rgb & 0xFF;
				$hex = $this->color_utility->rgb_to_hex( array( $r, $g, $b ) );
				if ( ! in_array( $hex, $colors, true ) ) {
					$colors[] = $hex;
				}
			}
		}

		// Limit to most representative colors
		return array_slice( $colors, 0, 5 );
	}

	/**
	 * Gets supported import formats.
	 *
	 * @return array List of supported formats.
	 */
	public function get_supported_formats(): array {
		return $this->supported_formats;
	}

	/**
	 * Gets format-specific import options.
	 *
	 * @param string $format Format to get options for.
	 * @return array Format options.
	 */
	public function get_format_options( string $format ): array {
		if ( ! isset( $this->format_options[ $format ] ) ) {
			throw new \InvalidArgumentException( "No options available for format: {$format}" );
		}
		return $this->format_options[ $format ];
	}

	/**
	 * Validates import data.
	 *
	 * @param string $data   Data to validate.
	 * @param string $format Format to validate against.
	 * @return bool True if valid.
	 */
	public function validate_import_data( string $data, string $format ): bool {
		if ( ! $this->is_valid_format( $format ) ) {
			return false;
		}

		return match ( $format ) {
			'json' => json_decode( $data ) !== null,
			'css', 'scss', 'less' => (bool) preg_match( '/[#]([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})/', $data ),
			'ase', 'act', 'gpl' => strlen( $data ) > 0,
			default => true
		};
	}

	/**
	 * Import from JSON format.
	 *
	 * @param string $data JSON data.
	 * @return array Array of colors.
	 */
	private function import_palette_from_json( string $data ): array {
		$parsed = json_decode( $data, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \InvalidArgumentException( 'Invalid JSON data' );
		}

		return $parsed['colors'] ?? array();
	}

	/**
	 * Import from CSS format.
	 *
	 * @param string $data CSS data.
	 * @return array Array of colors.
	 */
	private function import_palette_from_css( string $data ): array {
		$colors = array();
		$prefix = $this->format_options['css']['variable_prefix'];

		preg_match_all( "/{$prefix}([^:]+):\s*([^;]+);/", $data, $matches );

		foreach ( $matches[2] as $color ) {
			if ( Color_Types::is_valid_hex_color( $color ) ) {
				$colors[] = $color;
			}
		}

		return $colors;
	}

	/**
	 * Import from SCSS format.
	 *
	 * @param string $data SCSS data.
	 * @return array Array of colors.
	 */
	private function import_palette_from_scss( string $data ): array {
		$colors = array();
		$prefix = $this->format_options['scss']['variable_prefix'];

		preg_match_all( "/{$prefix}([^:]+):\s*([^;]+);/", $data, $matches );

		foreach ( $matches[2] as $color ) {
			if ( Color_Types::is_valid_hex_color( $color ) ) {
				$colors[] = $color;
			}
		}

		return $colors;
	}

	/**
	 * Import from LESS format.
	 *
	 * @param string $data LESS data.
	 * @return array Array of colors.
	 */
	private function import_palette_from_less( string $data ): array {
		$colors = array();
		$prefix = $this->format_options['less']['variable_prefix'];

		preg_match_all( "/{$prefix}([^:]+):\s*([^;]+);/", $data, $matches );

		foreach ( $matches[2] as $color ) {
			if ( Color_Types::is_valid_hex_color( $color ) ) {
				$colors[] = $color;
			}
		}

		return $colors;
	}

	/**
	 * Get format from extension.
	 *
	 * @param string $extension File extension.
	 * @return string|null Format or null if unknown.
	 */
	private function get_format_from_extension( string $extension ): ?string {
		switch ( $extension ) {
			case 'json':
				return 'json';
			case 'css':
				return 'css';
			case 'scss':
				return 'scss';
			case 'less':
				return 'less';
			case 'ase':
				return 'ase';
			case 'act':
				return 'act';
			case 'gpl':
				return 'gpl';
			case 'png':
			case 'jpg':
			case 'jpeg':
				return 'image';
			default:
				return null;
		}
	}

	/**
	 * Get format from content type.
	 *
	 * @param string $content_type Content type.
	 * @return string|null Format or null if unknown.
	 */
	private function get_format_from_content_type( string $content_type ): ?string {
		if ( strpos( $content_type, 'application/json' ) !== false ) {
			return 'json';
		} elseif ( strpos( $content_type, 'text/css' ) !== false ) {
			return 'css';
		} elseif ( strpos( $content_type, 'image/' ) !== false ) {
			return 'image';
		} else {
			return null;
		}
	}

	/**
	 * Check if format is valid.
	 *
	 * @param string $format Format to check.
	 * @return bool True if valid.
	 */
	private function is_valid_format( string $format ): bool {
		return in_array( $format, $this->supported_formats, true );
	}

	/**
	 * Imports a color palette from a string.
	 *
	 * @param string $data   Data to import.
	 * @param string $format Format of the data (json, css, etc.).
	 * @return Color_Palette Imported palette.
	 */
	public function import_from_string( string $data, string $format ): Color_Palette {
		if ( ! in_array( $format, $this->supported_formats, true ) ) {
			throw new \InvalidArgumentException( sprintf( 'Unsupported format: %s', $format ) );
		}

		switch ( $format ) {
			case 'json':
				return $this->import_from_json_string( $data );
			case 'css':
				return $this->import_from_css_string( $data );
			case 'scss':
				return $this->import_from_scss_string( $data );
			case 'less':
				return $this->import_from_less_string( $data );
			default:
				throw new \InvalidArgumentException( sprintf( 'Import method not implemented: %s', $format ) );
		}
	}

	/**
	 * Import from JSON string
	 *
	 * @param string $data JSON string
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_json_string( string $data ): Color_Palette {
		$colors = json_decode( $data, true );
		if ( ! is_array( $colors ) ) {
			throw new \InvalidArgumentException( 'Invalid JSON data' );
		}
		return new Color_Palette( $colors );
	}

	/**
	 * Import from CSS string
	 *
	 * @param string $data CSS string
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_css_string( string $data ): Color_Palette {
		$colors = array();
		preg_match_all( '/--(.*?):\s*(#[0-9a-f]{6})/i', $data, $matches );
		foreach ( $matches[1] as $i => $name ) {
			$colors[ trim( $name ) ] = $matches[2][ $i ];
		}
		return new Color_Palette( $colors );
	}

	/**
	 * Import from SCSS string
	 *
	 * @param string $data SCSS string
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_scss_string( string $data ): Color_Palette {
		$colors = array();
		preg_match_all( '/\$(.*?):\s*(#[0-9a-f]{6})/i', $data, $matches );
		foreach ( $matches[1] as $i => $name ) {
			$colors[ trim( $name ) ] = $matches[2][ $i ];
		}
		return new Color_Palette( $colors );
	}

	/**
	 * Import from LESS string
	 *
	 * @param string $data LESS string
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_less_string( string $data ): Color_Palette {
		$colors = array();
		preg_match_all( '/@(.*?):\s*(#[0-9a-f]{6})/i', $data, $matches );
		foreach ( $matches[1] as $i => $name ) {
			$colors[ trim( $name ) ] = $matches[2][ $i ];
		}
		return new Color_Palette( $colors );
	}

	/**
	 * Import from JSON file
	 *
	 * @param string $file_path Path to JSON file
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_json_file( string $file_path ): Color_Palette {
		$data = file_get_contents( $file_path );
		return $this->import_from_json_string( $data );
	}

	/**
	 * Import from CSS file
	 *
	 * @param string $file_path Path to CSS file
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_css_file( string $file_path ): Color_Palette {
		$data = file_get_contents( $file_path );
		return $this->import_from_css_string( $data );
	}

	/**
	 * Import from SCSS file
	 *
	 * @param string $file_path Path to SCSS file
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_scss_file( string $file_path ): Color_Palette {
		$data = file_get_contents( $file_path );
		return $this->import_from_scss_string( $data );
	}

	/**
	 * Import from LESS file
	 *
	 * @param string $file_path Path to LESS file
	 * @return Color_Palette Imported palette
	 */
	protected function import_from_less_file( string $file_path ): Color_Palette {
		$data = file_get_contents( $file_path );
		return $this->import_from_less_string( $data );
	}

	/**
	 * Import a color palette from a file.
	 *
	 * @param string $file_path Path to the file.
	 * @return Color_Palette Imported palette.
	 */
	public function import_from_file( string $file_path ): Color_Palette {
		if ( ! file_exists( $file_path ) ) {
			throw new \InvalidArgumentException( 'File does not exist: ' . $file_path );
		}

		$extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
		if ( ! in_array( $extension, $this->supported_formats, true ) ) {
			throw new \InvalidArgumentException( 'Unsupported file format: ' . $extension );
		}

		switch ( $extension ) {
			case 'json':
				return $this->import_from_json_file( $file_path );
			case 'css':
				return $this->import_from_css_file( $file_path );
			case 'scss':
				return $this->import_from_scss_file( $file_path );
			case 'less':
				return $this->import_from_less_file( $file_path );
			default:
				throw new \InvalidArgumentException( 'Import method not implemented for: ' . $extension );
		}
	}

	/**
	 * Import a color palette from a URL.
	 *
	 * @param string $url URL to import from.
	 * @return Color_Palette Imported palette.
	 */
	public function import_from_url( string $url ): Color_Palette {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new \InvalidArgumentException( 'Invalid URL: ' . $url );
		}

		$extension = strtolower( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		if ( ! in_array( $extension, $this->supported_formats, true ) ) {
			throw new \InvalidArgumentException( 'Unsupported URL format: ' . $extension );
		}

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'Failed to fetch URL: ' . $response->get_error_message() );
		}

		$data = wp_remote_retrieve_body( $response );
		return $this->import_from_string( $data, $extension );
	}
}
