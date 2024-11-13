<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Converter Interface
 *
 * Defines the contract for color space conversion operations.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */
interface ColorConverter {
    /**
     * Converts RGB color values to hexadecimal format.
     *
     * @param array $rgb RGB values [r, g, b] where each value is 0-255.
     * @return string Hexadecimal color value (e.g., '#FF0000').
     * @throws \InvalidArgumentException If RGB values are invalid.
     */
    public function rgb_to_hex(array $rgb): string;

    /**
     * Converts hexadecimal color value to RGB format.
     *
     * @param string $hex Hexadecimal color value (e.g., '#FF0000' or 'FF0000').
     * @return array RGB values [r, g, b] where each value is 0-255.
     * @throws \InvalidArgumentException If hex value is invalid.
     */
    public function hex_to_rgb(string $hex): array;

    /**
     * Converts RGB color values to HSL format.
     *
     * @param array $rgb RGB values [r, g, b] where each value is 0-255.
     * @return array HSL values [h, s, l] where h is 0-360, s and l are 0-100.
     * @throws \InvalidArgumentException If RGB values are invalid.
     */
    public function rgb_to_hsl(array $rgb): array;

    /**
     * Converts HSL color values to RGB format.
     *
     * @param array $hsl HSL values [h, s, l] where h is 0-360, s and l are 0-100.
     * @return array RGB values [r, g, b] where each value is 0-255.
     * @throws \InvalidArgumentException If HSL values are invalid.
     */
    public function hsl_to_rgb(array $hsl): array;
} 
