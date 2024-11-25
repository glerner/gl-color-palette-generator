<?php

namespace GL_Color_Palette_Generator\Abstracts;

/**
 * Abstract class for processing colors.
 *
 * This class defines the structure for color processing and validation.
 */
abstract class BaseColorProcessor {
    /**
     * @var array List of supported color spaces.
     */
    protected array $color_space = ['rgb', 'hsl', 'hsv', 'cmyk', 'lab'];

    /**
     * @var array Current color palette.
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
        // Implementation
    }
}
