<?php
/**
 * Color Converter Interface
 *
 * Defines the contract for color space conversion operations.
 *
 * @package GLColorPalette
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette\Interfaces;

/**
 * Interface ColorConverter
 *
 * Provides methods for converting colors between different color spaces.
 */
interface ColorConverter {
    /**
     * Converts RGB color values to hexadecimal format
     *
     * @param array $rgb {
     *     RGB color values
     *     @type int $r Red component (0-255)
     *     @type int $g Green component (0-255)
     *     @type int $b Blue component (0-255)
     * }
     * @return string Hexadecimal color value (e.g., '#FF0000')
     * @throws \InvalidArgumentException If RGB values are invalid
     */
    public function rgb_to_hex(array $rgb): string;

    /**
     * Converts hexadecimal color value to RGB format
     *
     * @param string $hex Hexadecimal color value (e.g., '#FF0000' or 'FF0000')
     * @return array {
     *     RGB color values
     *     @type int $r Red component (0-255)
     *     @type int $g Green component (0-255)
     *     @type int $b Blue component (0-255)
     * }
     * @throws \InvalidArgumentException If hex value is invalid
     */
    public function hex_to_rgb(string $hex): array;

    /**
     * Converts RGB color values to HSL format
     *
     * @param array $rgb {
     *     RGB color values
     *     @type int $r Red component (0-255)
     *     @type int $g Green component (0-255)
     *     @type int $b Blue component (0-255)
     * }
     * @return array {
     *     HSL color values
     *     @type float $h Hue component (0-360)
     *     @type float $s Saturation component (0-100)
     *     @type float $l Lightness component (0-100)
     * }
     * @throws \InvalidArgumentException If RGB values are invalid
     */
    public function rgb_to_hsl(array $rgb): array;

    /**
     * Converts HSL color values to RGB format
     *
     * @param array $hsl {
     *     HSL color values
     *     @type float $h Hue component (0-360)
     *     @type float $s Saturation component (0-100)
     *     @type float $l Lightness component (0-100)
     * }
     * @return array {
     *     RGB color values
     *     @type int $r Red component (0-255)
     *     @type int $g Green component (0-255)
     *     @type int $b Blue component (0-255)
     * }
     * @throws \InvalidArgumentException If HSL values are invalid
     */
    public function hsl_to_rgb(array $hsl): array;

    /**
     * Converts RGB color values to LAB format
     *
     * @param array $rgb {
     *     RGB color values
     *     @type int $r Red component (0-255)
     *     @type int $g Green component (0-255)
     *     @type int $b Blue component (0-255)
     * }
     * @return array {
     *     LAB color values
     *     @type float $l Lightness component (0-100)
     *     @type float $a A component (typically -128 to 127)
     *     @type float $b B component (typically -128 to 127)
     * }
     * @throws \InvalidArgumentException If RGB values are invalid
     */
    public function rgb_to_lab(array $rgb): array;

    /**
     * Converts LAB color values to RGB format
     *
     * @param array $lab {
     *     LAB color values
     *     @type float $l Lightness component (0-100)
     *     @type float $a A component (typically -128 to 127)
     *     @type float $b B component (typically -128 to 127)
     * }
     * @return array {
     *     RGB color values
     *     @type int $r Red component (0-255)
     *     @type int $g Green component (0-255)
     *     @type int $b Blue component (0-255)
     * }
     * @throws \InvalidArgumentException If LAB values are invalid
     */
    public function lab_to_rgb(array $lab): array;
}
