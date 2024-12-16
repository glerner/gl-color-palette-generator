<?php
namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Mock Color Shade Generator class for testing
 */
class Color_Shade_Generator {
    /**
     * Generate tints and shades for a color
     *
     * @param string $color Base color in hex format
     * @param array  $options Optional. Generation options.
     * @return array Array of tints and shades
     */
    public function generate_tints_and_shades($color, $options = []) {
        return [
            'variations' => [
                'lighter' => '#FFFFFF',
                'light' => '#CCCCCC',
                'base' => $color,
                'dark' => '#333333',
                'darker' => '#000000'
            ]
        ];
    }
}
