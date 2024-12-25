<?php
/**
 * Color Generator Interface
 *
 * Defines the contract for color generation implementations.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface Color_Generator_Interface
 *
 * Defines methods that must be implemented by color generator classes.
 */
interface Color_Generator_Interface {
    /**
     * Generate a single color based on given parameters.
     *
     * @param array $params Optional parameters to influence color generation.
     * @return string Generated color in hexadecimal format.
     */
    public function generate_color(array $params = []): string;

    /**
     * Generate multiple colors based on given parameters.
     *
     * @param int   $count  Number of colors to generate.
     * @param array $params Optional parameters to influence color generation.
     * @return array Array of generated colors in hexadecimal format.
     */
    public function generate_colors(int $count, array $params = []): array;

    /**
     * Set the base color for generation.
     *
     * @param string $color Base color in hexadecimal format.
     * @return void
     */
    public function set_base_color(string $color): void;

    /**
     * Get the current base color.
     *
     * @return string|null Current base color in hexadecimal format or null if not set.
     */
    public function get_base_color(): ?string;

    /**
     * Set generation constraints.
     *
     * @param array $constraints Array of constraints for color generation.
     * @return void
     */
    public function set_constraints(array $constraints): void;

    /**
     * Get current generation constraints.
     *
     * @return array Current constraints.
     */
    public function get_constraints(): array;
}
