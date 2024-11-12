<?php

namespace GLColorPalette;

/**
 * Color Palette Class
 *
 * Core class for managing a color palette and its properties.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPalette {
    /**
     * Palette identifier.
     *
     * @var string
     */
    private $id;

    /**
     * Palette name.
     *
     * @var string
     */
    private $name;

    /**
     * Array of colors in the palette.
     *
     * @var array
     */
    private $colors;

    /**
     * Palette metadata.
     *
     * @var array
     */
    private $metadata;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Initial palette data.
     *     @type string $id        Palette identifier.
     *     @type string $name      Palette name.
     *     @type array  $colors    Palette colors.
     *     @type array  $metadata  Palette metadata.
     * }
     */
    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? uniqid('pal_');
        $this->name = $data['name'] ?? '';
        $this->colors = $data['colors'] ?? [];
        $this->metadata = $data['metadata'] ?? [
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'version' => '1.0'
        ];
    }

    /**
     * Gets palette identifier.
     *
     * @return string Palette ID.
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Gets palette name.
     *
     * @return string Palette name.
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Sets palette name.
     *
     * @param string $name New palette name.
     * @return void
     */
    public function set_name(string $name): void {
        $this->name = $name;
        $this->update_metadata();
    }

    /**
     * Gets all colors.
     *
     * @return array Array of colors.
     */
    public function get_colors(): array {
        return $this->colors;
    }

    /**
     * Gets color by index.
     *
     * @param int $index Color index.
     * @return string|null Color value or null if not found.
     */
    public function get_color(int $index): ?string {
        return $this->colors[$index] ?? null;
    }

    /**
     * Adds color to palette.
     *
     * @param string $color Color to add.
     * @return void
     * @throws \InvalidArgumentException If color is invalid.
     */
    public function add_color(string $color): void {
        if (!$this->validate_color($color)) {
            throw new \InvalidArgumentException("Invalid color value: {$color}");
        }
        $this->colors[] = $color;
        $this->update_metadata();
    }

    /**
     * Updates color at index.
     *
     * @param int $index Color index.
     * @param string $color New color value.
     * @return void
     * @throws \InvalidArgumentException If color is invalid.
     * @throws \OutOfRangeException If index is invalid.
     */
    public function update_color(int $index, string $color): void {
        if (!isset($this->colors[$index])) {
            throw new \OutOfRangeException("Invalid color index: {$index}");
        }
        if (!$this->validate_color($color)) {
            throw new \InvalidArgumentException("Invalid color value: {$color}");
        }
        $this->colors[$index] = $color;
        $this->update_metadata();
    }

    /**
     * Removes color at index.
     *
     * @param int $index Color index.
     * @return void
     * @throws \OutOfRangeException If index is invalid.
     */
    public function remove_color(int $index): void {
        if (!isset($this->colors[$index])) {
            throw new \OutOfRangeException("Invalid color index: {$index}");
        }
        array_splice($this->colors, $index, 1);
        $this->update_metadata();
    }

    /**
     * Gets palette metadata.
     *
     * @return array Metadata array.
     */
    public function get_metadata(): array {
        return $this->metadata;
    }

    /**
     * Updates metadata field.
     *
     * @param string $key Metadata key.
     * @param mixed $value Metadata value.
     * @return void
     */
    public function update_metadata_field(string $key, $value): void {
        $this->metadata[$key] = $value;
        $this->metadata['updated_at'] = current_time('mysql');
    }

    /**
     * Converts palette to array.
     *
     * @return array Palette data array.
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'colors' => $this->colors,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Updates metadata timestamp.
     *
     * @return void
     */
    private function update_metadata(): void {
        $this->metadata['updated_at'] = current_time('mysql');
    }

    /**
     * Validates color value.
     *
     * @param string $color Color to validate.
     * @return bool True if valid, false otherwise.
     */
    private function validate_color(string $color): bool {
        // Basic hex color validation
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color);
    }
} 
