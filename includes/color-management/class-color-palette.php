<?php
namespace GLColorPalette;

/**
 * Class Color_Palette
 * Represents a collection of colors with associated metadata
 */
class Color_Palette {
    /**
     * Array of hex color codes
     * @var array
     */
    private $colors = [];

    /**
     * Metadata about the palette
     * @var array
     */
    private $metadata = [
        'name' => '',
        'description' => '',
        'theme' => '',
        'created' => '',
        'modified' => '',
        'provider' => '',
        'tags' => []
    ];

    /**
     * Constructor
     *
     * @param array $colors Array of hex color codes
     * @param array $metadata Optional metadata
     */
    public function __construct(array $colors = [], array $metadata = []) {
        $this->set_colors($colors);
        $this->set_metadata($metadata);
    }

    /**
     * Set the colors for this palette
     *
     * @param array $colors Array of hex color codes
     * @return void
     */
    public function set_colors(array $colors): void {
        $validator = new Color_Palette_Validator();
        foreach ($colors as $color) {
            if ($validator->is_valid_hex_color($color)) {
                $this->colors[] = strtoupper($color);
            }
        }
    }

    /**
     * Get the colors in this palette
     *
     * @return array Array of hex color codes
     */
    public function get_colors(): array {
        return $this->colors;
    }

    /**
     * Set metadata for this palette
     *
     * @param array $metadata Metadata to set
     * @return void
     */
    public function set_metadata(array $metadata): void {
        $this->metadata = array_merge($this->metadata, $metadata);

        / Ensure timestamps are set
        if (empty($this->metadata['created'])) {
            $this->metadata['created'] = current_time('mysql');
        }
        $this->metadata['modified'] = current_time('mysql');
    }

    /**
     * Get all metadata or a specific metadata field
     *
     * @param string|null $key Optional specific metadata key
     * @return mixed Array of all metadata or specific value
     */
    public function get_metadata(?string $key = null) {
        if ($key !== null) {
            return $this->metadata[$key] ?? null;
        }
        return $this->metadata;
    }

    /**
     * Add a color to the palette
     *
     * @param string $color Hex color code
     * @return bool Success
     */
    public function add_color(string $color): bool {
        $validator = new Color_Palette_Validator();
        if ($validator->is_valid_hex_color($color)) {
            $this->colors[] = strtoupper($color);
            $this->metadata['modified'] = current_time('mysql');
            return true;
        }
        return false;
    }

    /**
     * Remove a color from the palette
     *
     * @param string $color Hex color code to remove
     * @return bool Success
     */
    public function remove_color(string $color): bool {
        $color = strtoupper($color);
        $key = array_search($color, $this->colors);
        if ($key !== false) {
            unset($this->colors[$key]);
            $this->colors = array_values($this->colors);
            $this->metadata['modified'] = current_time('mysql');
            return true;
        }
        return false;
    }

    /**
     * Convert the palette to an array
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'colors' => $this->colors,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Create a palette from an array
     *
     * @param array $data Array containing colors and metadata
     * @return Color_Palette
     */
    public static function from_array(array $data): Color_Palette {
        $colors = $data['colors'] ?? [];
        $metadata = $data['metadata'] ?? [];
        return new self($colors, $metadata);
    }
} 
