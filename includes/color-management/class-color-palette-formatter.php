<?php
/**
 * Color Palette Formatter Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Formatter
 * Formats color palettes for different output formats and use cases
 */
class Color_Palette_Formatter implements \GL_Color_Palette_Generator\Interfaces\Color_Palette_Formatter {
    /**
     * Format palette for output
     *
     * @param array  $palette Array of colors.
     * @param string $format Output format (css, scss, less, tailwind, json).
     * @param array  $options Optional formatting options.
     * @return string Formatted palette.
     * @throws \Exception If format is invalid.
     */
    public function format_palette($palette, $format, $options = []) {
        $default_options = [
            'variable_prefix' => '--color',
            'class_prefix' => 'color',
            'include_comments' => true,
            'indent' => '  ',
            'line_ending' => "\n"
        ];

        $options = array_merge($default_options, $options);

        switch (strtolower($format)) {
            case 'css':
                return $this->format_css($palette, $options);
            case 'scss':
                return $this->format_scss($palette, $options);
            case 'less':
                return $this->format_less($palette, $options);
            case 'tailwind':
                return $this->format_tailwind($palette, $options);
            case 'json':
                return $this->format_json($palette, $options);
            default:
                throw new \Exception(
                    sprintf(
                        __('Invalid format: %s', 'gl-color-palette-generator'),
                        $format
                    )
                );
        }
    }

    /**
     * Format palette as CSS variables
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string CSS variables.
     */
    private function format_css($palette, $options) {
        $output = ":root {" . $options['line_ending'];

        foreach ($palette as $index => $color) {
            if ($options['include_comments']) {
                $output .= $options['indent'] . "/* Color " . ($index + 1) . " */" . $options['line_ending'];
            }

            $output .= $options['indent'] .
                      $options['variable_prefix'] . "-" . ($index + 1) . ": " .
                      $color . ";" . $options['line_ending'];
        }

        $output .= "}" . $options['line_ending'];

        // Add utility classes if requested
        if (!empty($options['generate_classes'])) {
            $output .= $this->generate_css_classes($palette, $options);
        }

        return $output;
    }

    /**
     * Format palette as SCSS variables
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string SCSS variables.
     */
    private function format_scss($palette, $options) {
        $output = "";

        foreach ($palette as $index => $color) {
            if ($options['include_comments']) {
                $output .= "// Color " . ($index + 1) . $options['line_ending'];
            }

            $output .= "$" . $options['variable_prefix'] . "-" . ($index + 1) . ": " .
                      $color . ";" . $options['line_ending'];
        }

        // Add color map
        $output .= $options['line_ending'] . "$colors: (" . $options['line_ending'];
        foreach ($palette as $index => $color) {
            $output .= $options['indent'] . "'color-" . ($index + 1) . "': " .
                      $color . "," . $options['line_ending'];
        }
        $output .= ");" . $options['line_ending'];

        return $output;
    }

    /**
     * Format palette as LESS variables
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string LESS variables.
     */
    private function format_less($palette, $options) {
        $output = "";

        foreach ($palette as $index => $color) {
            if ($options['include_comments']) {
                $output .= "// Color " . ($index + 1) . $options['line_ending'];
            }

            $output .= "@" . $options['variable_prefix'] . "-" . ($index + 1) . ": " .
                      $color . ";" . $options['line_ending'];
        }

        return $output;
    }

    /**
     * Format palette for Tailwind config
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string Tailwind config object.
     */
    private function format_tailwind($palette, $options) {
        $output = "module.exports = {" . $options['line_ending'];
        $output .= $options['indent'] . "theme: {" . $options['line_ending'];
        $output .= $options['indent'] . $options['indent'] . "colors: {" . $options['line_ending'];

        foreach ($palette as $index => $color) {
            if ($options['include_comments']) {
                $output .= $options['indent'] . $options['indent'] . $options['indent'] .
                          "// Color " . ($index + 1) . $options['line_ending'];
            }

            $output .= $options['indent'] . $options['indent'] . $options['indent'] .
                      "'" . $options['class_prefix'] . "-" . ($index + 1) . "': '" .
                      $color . "'," . $options['line_ending'];
        }

        $output .= $options['indent'] . $options['indent'] . "}," . $options['line_ending'];
        $output .= $options['indent'] . "}," . $options['line_ending'];
        $output .= "};" . $options['line_ending'];

        return $output;
    }

    /**
     * Format palette as JSON
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string JSON string.
     */
    private function format_json($palette, $options) {
        $colors = [];
        foreach ($palette as $index => $color) {
            $colors[$options['class_prefix'] . '-' . ($index + 1)] = $color;
        }

        return wp_json_encode(
            $colors,
            $options['include_comments'] ? JSON_PRETTY_PRINT : 0
        );
    }

    /**
     * Generate CSS utility classes
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string CSS classes.
     */
    private function generate_css_classes($palette, $options) {
        $output = "";

        foreach ($palette as $index => $color) {
            $class_name = "." . $options['class_prefix'] . "-" . ($index + 1);

            if ($options['include_comments']) {
                $output .= "/* Color " . ($index + 1) . " Utility Classes */" . $options['line_ending'];
            }

            // Background color class
            $output .= $class_name . "-bg {" . $options['line_ending'];
            $output .= $options['indent'] . "background-color: " . $color . ";" . $options['line_ending'];
            $output .= "}" . $options['line_ending'];

            // Text color class
            $output .= $class_name . "-text {" . $options['line_ending'];
            $output .= $options['indent'] . "color: " . $color . ";" . $options['line_ending'];
            $output .= "}" . $options['line_ending'];

            // Border color class
            $output .= $class_name . "-border {" . $options['line_ending'];
            $output .= $options['indent'] . "border-color: " . $color . ";" . $options['line_ending'];
            $output .= "}" . $options['line_ending'];
        }

        return $output;
    }
}
