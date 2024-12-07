<?php
namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Utils\Validator;

if (!defined('ABSPATH')) {
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
     * @var string[]
     */
    private $colors = [];

    /**
     * Metadata about the palette
     * @var array{
     *     name: string,
     *     description: string,
     *     theme: string,
     *     created: string,
     *     modified: string,
     *     provider: string,
     *     tags: string[]
     * }
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
     * @param string[] $colors Array of hex color codes
     * @param array    $metadata Optional metadata
     * @throws \InvalidArgumentException If colors are invalid
     */
    public function __construct(array $colors = [], array $metadata = []) {
        $this->set_colors($colors);
        $this->set_metadata($metadata);
    }

    /**
     * Set the colors for this palette
     *
     * @param string[] $colors Array of hex color codes
     * @return void
     * @throws \InvalidArgumentException If any color is invalid
     */
    public function set_colors(array $colors): void {
        $validator = new Validator();
        $this->colors = [];
        
        foreach ($colors as $color) {
            if (!$validator->is_valid_hex_color($color)) {
                throw new \InvalidArgumentException(
                    sprintf(__('Invalid color code: %s', 'gl-color-palette-generator'), $color)
                );
            }
            $this->colors[] = strtoupper($color);
        }
    }

    /**
     * Get the colors in this palette
     *
     * @return string[] Array of hex color codes
     */
    public function get_colors(): array {
        return $this->colors;
    }

    /**
     * Set metadata for this palette
     *
     * @param array $metadata Metadata to set
     * @return void
     * @throws \InvalidArgumentException If metadata values are invalid
     */
    public function set_metadata(array $metadata): void {
        // Validate string fields
        $string_fields = ['name', 'description', 'theme', 'provider'];
        foreach ($string_fields as $field) {
            if (isset($metadata[$field]) && !is_string($metadata[$field])) {
                throw new \InvalidArgumentException(
                    sprintf(__('%s must be a string', 'gl-color-palette-generator'), $field)
                );
            }
        }

        // Validate tags array
        if (isset($metadata['tags'])) {
            if (!is_array($metadata['tags'])) {
                throw new \InvalidArgumentException(
                    __('Tags must be an array', 'gl-color-palette-generator')
                );
            }
            foreach ($metadata['tags'] as $tag) {
                if (!is_string($tag)) {
                    throw new \InvalidArgumentException(
                        __('All tags must be strings', 'gl-color-palette-generator')
                    );
                }
            }
        }

        $this->metadata = array_merge($this->metadata, $metadata);

        // Ensure timestamps are set
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
     * @throws \InvalidArgumentException If key doesn't exist
     */
    public function get_metadata(?string $key = null) {
        if ($key !== null) {
            if (!array_key_exists($key, $this->metadata)) {
                throw new \InvalidArgumentException(
                    sprintf(__('Invalid metadata key: %s', 'gl-color-palette-generator'), $key)
                );
            }
            return $this->metadata[$key];
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
    public function add_color(string $color): bool {
        $validator = new Validator();
        if (!$validator->is_valid_hex_color($color)) {
            throw new \InvalidArgumentException(
                sprintf(__('Invalid color code: %s', 'gl-color-palette-generator'), $color)
            );
        }
        
        $this->colors[] = strtoupper($color);
        $this->metadata['modified'] = current_time('mysql');
        return true;
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
     * @return array{colors: string[], metadata: array}
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
     * @param array{colors?: string[], metadata?: array} $data Array containing colors and metadata
     * @return self
     * @throws \InvalidArgumentException If data is invalid
     */
    public static function from_array(array $data): self {
        $colors = $data['colors'] ?? [];
        $metadata = $data['metadata'] ?? [];
        
        if (!is_array($colors)) {
            throw new \InvalidArgumentException(
                __('Colors must be an array', 'gl-color-palette-generator')
            );
        }
        
        return new self($colors, $metadata);
    }
}
