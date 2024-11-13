<?php
/**
 * Core Color Palette Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteInterface;

/**
 * Represents a color palette with its associated metadata and operations.
 */
class ColorPalette implements ColorPaletteInterface {
    /**
     * Palette name
     *
     * @var string
     */
    private string $name;

    /**
     * Array of colors in hex format
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
     * Constructor
     *
     * @param array $data {
     *     Optional. Array of palette data.
     *     @type string $name     Palette name. Default 'Untitled Palette'.
     *     @type array  $colors   Array of hex colors. Default empty array.
     *     @type array  $metadata Additional metadata. Default empty array.
     * }
     */
    public function __construct(array $data = []) {
        $this->name = $data['name'] ?? 'Untitled Palette';
        $this->colors = $data['colors'] ?? [];
        $this->metadata = $data['metadata'] ?? [];

        // Ensure colors are properly formatted
        $this->colors = array_map([$this, 'normalize_color'], $this->colors);
    }

    /**
     * Gets the palette name.
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Gets the palette colors.
     *
     * @return array
     */
    public function getColors(): array {
        return $this->colors;
    }

    /**
     * Gets the palette metadata.
     *
     * @return array
     */
    public function getMetadata(): array {
        return $this->metadata;
    }

    /**
     * Sets the palette name.
     *
     * @param string $name New palette name.
     * @return self
     */
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the palette colors.
     *
     * @param array $colors Array of hex colors.
     * @return self
     * @throws \InvalidArgumentException If any color is invalid.
     */
    public function setColors(array $colors): self {
        $this->colors = array_map([$this, 'normalize_color'], $colors);
        return $this;
    }

    /**
     * Sets the palette metadata.
     *
     * @param array $metadata Palette metadata.
     * @return self
     */
    public function setMetadata(array $metadata): self {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Adds a color to the palette.
     *
     * @param string $color Hex color to add.
     * @return self
     * @throws \InvalidArgumentException If color is invalid.
     */
    public function addColor(string $color): self {
        $this->colors[] = $this->normalize_color($color);
        return $this;
    }

    /**
     * Removes a color from the palette.
     *
     * @param string $color Hex color to remove.
     * @return self
     */
    public function removeColor(string $color): self {
        $normalized = $this->normalize_color($color);
        $this->colors = array_values(array_filter(
            $this->colors,
            fn($c) => $c !== $normalized
        ));
        return $this;
    }

    /**
     * Converts the palette to an array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'name' => $this->name,
            'colors' => $this->colors,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Normalizes a color to 6-digit hex format.
     *
     * @param string $color Color to normalize.
     * @return string Normalized color.
     * @throws \InvalidArgumentException If color is invalid.
     */
    private function normalize_color(string $color): string {
        $color = strtoupper(trim($color, '# '));

        // Convert 3-digit hex to 6-digit
        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        // Validate hex format
        if (!preg_match('/^[0-9A-F]{6}$/', $color)) {
            throw new \InvalidArgumentException("Invalid color format: {$color}");
        }

        return "#{$color}";
    }
}
