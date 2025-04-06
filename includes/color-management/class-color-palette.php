<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Utils\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Color_Palette
 * Represents a collection of colors with associated metadata
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */
class Color_Palette {
	/**
	 * Array of hex color codes
	 *
	 * @var array<string>
	 */
	private array $colors = array();

	/**
	 * Metadata about the palette
	 *
	 * @var array{
	 *     name: string,
	 *     description: string,
	 *     theme: string,
	 *     created: string,
	 *     modified: string,
	 *     provider: string,
	 *     tags: array<string>
	 * }
	 */
	private array $metadata = array(
		'name'        => '',
		'description' => '',
		'theme'       => '',
		'created'     => '',
		'modified'    => '',
		'provider'    => '',
		'tags'        => array(),
	);

	/**
	 * Constructor
	 *
	 * @param array<string> $colors Array of hex color codes
	 * @param array{
	 *     name?: string,
	 *     description?: string,
	 *     theme?: string,
	 *     created?: string,
	 *     modified?: string,
	 *     provider?: string,
	 *     tags?: array<string>
	 * } $metadata Optional metadata
	 * @throws \InvalidArgumentException If colors or metadata are invalid
	 */
	public function __construct( array $colors = array(), array $metadata = array() ) {
		$this->set_colors( $colors );
		$this->set_metadata( $metadata );
	}

	/**
	 * Set the colors for this palette
	 *
	 * @param array<string> $colors Array of hex color codes
	 * @return void
	 * @throws \InvalidArgumentException If any color is invalid
	 */
	public function set_colors( array $colors ): void {
		$this->colors = array();

		foreach ( $colors as $color ) {
			if ( ! Color_Types::is_valid_hex_color( $color ) ) {
				throw new \InvalidArgumentException(
					sprintf( __( 'Invalid color code: %s', 'gl-color-palette-generator' ), $color )
				);
			}
			$this->colors[] = strtoupper( $color );
		}
	}

	/**
	 * Get the colors in this palette
	 *
	 * @return array<string> Array of hex color codes
	 */
	public function get_colors(): array {
		return $this->colors;
	}

	/**
	 * Set metadata for this palette
	 *
	 * @param array{
	 *     name?: string,
	 *     description?: string,
	 *     theme?: string,
	 *     created?: string,
	 *     modified?: string,
	 *     provider?: string,
	 *     tags?: array<string>
	 * } $metadata Metadata to set
	 * @return void
	 * @throws \InvalidArgumentException If metadata values are invalid
	 */
	public function set_metadata( array $metadata ): void {
		if ( ! empty( $metadata ) && ! Color_Types::is_valid_metadata( $metadata ) ) {
			throw new \InvalidArgumentException( __( 'Invalid metadata format', 'gl-color-palette-generator' ) );
		}

		$this->metadata = array_merge( $this->metadata, $metadata );

		// Ensure timestamps are set
		if ( empty( $this->metadata['created'] ) ) {
			$this->metadata['created'] = current_time( 'mysql' );
		}
		$this->metadata['modified'] = current_time( 'mysql' );
	}

	/**
	 * Get all metadata or a specific metadata field
	 *
	 * @param string|null $key Optional specific metadata key
	 * @return mixed Array of all metadata or specific value
	 * @throws \InvalidArgumentException If key doesn't exist
	 */
	public function get_metadata( ?string $key = null ) {
		if ( $key !== null ) {
			if ( ! array_key_exists( $key, $this->metadata ) ) {
				throw new \InvalidArgumentException(
					sprintf( __( 'Invalid metadata key: %s', 'gl-color-palette-generator' ), $key )
				);
			}
			return $this->metadata[ $key ];
		}
		return $this->metadata;
	}

	/**
	 * Add a color to the palette
	 *
	 * @param string $color Hex color code
	 * @return bool Success
	 * @throws \InvalidArgumentException If color is invalid
	 */
	public function add_color( string $color ): bool {
		if ( ! Color_Types::is_valid_hex_color( $color ) ) {
			throw new \InvalidArgumentException(
				sprintf( __( 'Invalid color code: %s', 'gl-color-palette-generator' ), $color )
			);
		}

		$this->colors[]             = strtoupper( $color );
		$this->metadata['modified'] = current_time( 'mysql' );
		return true;
	}

	/**
	 * Remove a color from the palette
	 *
	 * @param string $color Hex color code to remove
	 * @return bool Success
	 */
	public function remove_color( string $color ): bool {
		$color = strtoupper( $color );
		$key   = array_search( $color, $this->colors );
		if ( $key !== false ) {
			unset( $this->colors[ $key ] );
			$this->colors               = array_values( $this->colors );
			$this->metadata['modified'] = current_time( 'mysql' );
			return true;
		}
		return false;
	}

	/**
	 * Convert the palette to an array
	 *
	 * @return array{colors: array<string>, metadata: array}
	 */
	public function to_array(): array {
		return array(
			'colors'   => $this->colors,
			'metadata' => $this->metadata,
		);
	}

	/**
	 * Create a palette from an array
	 *
	 * @param array{colors?: array<string>, metadata?: array} $data Array containing colors and metadata
	 * @return self
	 * @throws \InvalidArgumentException If data is invalid
	 */
	public static function from_array( array $data ): self {
		$colors   = $data['colors'] ?? array();
		$metadata = $data['metadata'] ?? array();

		if ( ! is_array( $colors ) ) {
			throw new \InvalidArgumentException(
				__( 'Colors must be an array', 'gl-color-palette-generator' )
			);
		}

		return new self( $colors, $metadata );
	}
}
