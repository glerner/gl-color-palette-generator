<?php
/**
 * Abstract Color Processor Class
 *
 * Provides base functionality for processing and validating colors across
 * different color spaces. Handles color space conversions, validation,
 * and palette management.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Abstracts
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Abstracts;

/**
 * Abstract class Color_Processor
 *
 * Defines the core structure for color processing and validation operations.
 * Supports multiple color spaces and provides palette management functionality.
 *
 * @since 1.0.0
 */
abstract class Color_Processor {
    /**
     * List of supported color spaces
     *
     * @var array Supported color spaces including rgb, hsl, hsv, cmyk, and lab
     */
    protected array $color_space = ['rgb', 'hsl', 'hsv', 'cmyk', 'lab'];

    /**
     * Current color palette
     *
     * @var array Array of colors in the current working palette
     */
    protected array $current_palette = [];

    /**
     * Process a given color and return its representation as an array.
     *
     * @param string $color The color to process.
     * @return array Processed color data.
     */
    abstract public function process_color(string $color): array;

    /**
     * Validate a given color string.
     *
     * @param string $color The color to validate.
     * @return bool True if the color is valid, false otherwise.
     */
    abstract public function validate_color(string $color): bool;

    /**
     * Normalize a color value based on its type.
     *
     * @param mixed $value The value to normalize.
     * @param string $type The type of color (e.g., 'rgb', 'hsl').
     * @return float Normalized color value.
     */
    protected function normalize_color_value($value, string $type): float {
        // Convert string values to float
        $value = (float) $value;
        
        // Normalize based on color type
        switch ($type) {
            case 'rgb':
                return max(0, min(255, $value)) / 255;
            case 'hsl':
            case 'hsv':
                return max(0, min(360, $value)) / 360;
            case 'cmyk':
                return max(0, min(100, $value)) / 100;
            default:
                return max(0, min(1, $value));
        }
    }
}
