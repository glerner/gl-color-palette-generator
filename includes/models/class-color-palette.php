<?php

namespace GL_Color_Palette_Generator\Models;

/**
 * Color Palette Model
 *
 * Represents a color palette with its properties and metadata.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Models
 * @since 1.0.0
 */
class Color_Palette {
	/**
	 * Palette ID
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * Colors in the palette
	 *
	 * @var array
	 */
	private array $colors;

	/**
	 * Palette metadata
	 *
	 * @var array
	 */
	private array $metadata;

	/**
	 * Creation timestamp
	 *
	 * @var int
	 */
	private int $created_at;

	/**
	 * Last update timestamp
	 *
	 * @var int|null
	 */
	private ?int $updated_at;

	/**
	 * Constructor
	 *
	 * @param string   $id         Palette ID
	 * @param array    $colors     Colors in the palette
	 * @param array    $metadata   Palette metadata
	 * @param int      $created_at Creation timestamp
	 * @param int|null $updated_at Last update timestamp
	 */
	public function __construct(
		string $id,
		array $colors,
		array $metadata = array(),
		int $created_at = 0,
		?int $updated_at = null
	) {
		$this->id         = $id;
		$this->colors     = $colors;
		$this->metadata   = $metadata;
		$this->created_at = $created_at !== 0 ? $created_at : time();
		$this->updated_at = $updated_at;
	}

	/**
	 * Get palette ID
	 *
	 * @return string Palette ID
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get palette colors
	 *
	 * @return array Colors
	 */
	public function get_colors(): array {
		return $this->colors;
	}

	/**
	 * Get palette metadata
	 *
	 * @return array Metadata
	 */
	public function get_metadata(): array {
		return $this->metadata;
	}

	/**
	 * Get creation timestamp
	 *
	 * @return int Creation timestamp
	 */
	public function get_created_at(): int {
		return $this->created_at;
	}

	/**
	 * Get last update timestamp
	 *
	 * @return int|null Last update timestamp
	 */
	public function get_updated_at(): ?int {
		return $this->updated_at;
	}

	/**
	 * Set palette colors
	 *
	 * @param array $colors New colors
	 * @return void
	 */
	public function set_colors( array $colors ): void {
		$this->colors     = $colors;
		$this->updated_at = time();
	}

	/**
	 * Set palette metadata
	 *
	 * @param array $metadata New metadata
	 * @return void
	 */
	public function set_metadata( array $metadata ): void {
		$this->metadata   = $metadata;
		$this->updated_at = time();
	}
}
