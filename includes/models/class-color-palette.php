<?php
/**
 * Color Palette Model Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Models
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Models;

/**
 * Class Color_Palette
 *
 * Represents a collection of colors in a palette.
 */
class Color_Palette {
    /**
     * Array of colors in the palette
     *
     * @var array
     */
    private array $colors;

    /**
     * Constructor
     *
     * @param array $colors Array of colors with role keys.
     */
    public function __construct(array $colors) {
        $this->colors = $colors;
    }

    /**
     * Get all colors in the palette
     *
     * @return array Array of colors with role keys.
     */
    public function get_colors(): array {
        return $this->colors;
    }

    /**
     * Get a specific color by role
     *
     * @param string $role Role of the color to get.
     * @return string|null Color in hex format or null if not found.
     */
    public function get_color(string $role): ?string {
        return $this->colors[$role] ?? null;
    }

    /**
     * Add a color to the palette
     *
     * @param string $role  Role of the color.
     * @param string $color Color in hex format.
     * @return void
     */
    public function add_color(string $role, string $color): void {
        $this->colors[$role] = $color;
    }

    /**
     * Check if a color role exists in the palette
     *
     * @param string $role Role to check.
     * @return bool True if the role exists.
     */
    public function has_color(string $role): bool {
        return isset($this->colors[$role]);
    }

    /**
     * Get the number of colors in the palette
     *
     * @return int Number of colors.
     */
    public function count(): int {
        return count($this->colors);
    }
}
