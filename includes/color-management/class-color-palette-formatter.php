<?php
/**
 * Color Palette Formatter Class
 *
 * Handles basic formatting of color palettes into various code formats.
 * Supported formats:
 * - css: CSS custom properties (variables)
 * - scss: SCSS variables and color map
 * - less: LESS variables
 * - tailwind: Tailwind CSS configuration
 * - json: JSON color object
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Palette_Formatter_Interface;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;

/**
 * Class Color_Palette_Formatter
 * Formats color palettes for different output formats and use cases
 */
class Color_Palette_Formatter implements Color_Palette_Formatter_Interface {
    /**
     * List of supported formatting formats
     *
     * These formats focus on code-level color definitions and can be
     * extended by child classes to support additional formats.
     */
    protected const SUPPORTED_FORMATS = [
        'css',    // CSS custom properties
        'scss',   // SCSS variables and maps
        'less',   // LESS variables
        'tailwind', // Tailwind config
        'json'    // JSON object
    ];

    /**
     * Default formatting options
     */
    protected const DEFAULT_OPTIONS = [
        'variable_prefix' => '--color',
        'class_prefix' => 'color',
        'include_comments' => true,
        'indent' => '  ',
        'line_ending' => "\n",
        'generate_classes' => false
    ];

    /**
     * Format palette for output
     *
     * @param array  $palette Array of colors.
     * @param string $format  Output format (css, scss, less, tailwind, json).
     * @param array  $options Optional formatting options.
     * @return string Formatted palette.
     * @throws \Exception If format is invalid.
     */
    public function format_palette(array $palette, string $format, array $options = []): string {
        if (!in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw new \Exception(
                sprintf(
                    __('Invalid format: %s', 'gl-color-palette-generator'),
                    $format
                )
            );
        }

        $options = array_merge($this->get_default_options(), $options);

        switch ($format) {
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
                        __('Format not implemented: %s', 'gl-color-palette-generator'),
                        $format
                    )
                );
        }
    }

    /**
     * Get supported formats
     *
     * @return array List of supported formats
     */
    public function get_supported_formats(): array {
        return self::SUPPORTED_FORMATS;
    }

    /**
     * Get default formatting options
     *
     * @return array Default options
     */
    public function get_default_options(): array {
        return self::DEFAULT_OPTIONS;
    }

    /**
     * Format palette as CSS variables
     *
     * @param array $palette Array of colors.
     * @param array $options Formatting options.
     * @return string CSS variables.
     */
    protected function format_css(array $palette, array $options): string {
        $output = ":root {" . $options['line_ending'];

        foreach ($palette as $name => $color) {
            if ($options['include_comments']) {
                $output .= $options['indent'] . "/* {$name} */" . $options['line_ending'];
            }

            $varName = sanitize_title($name);
            $output .= $options['indent'] .
                      $options['variable_prefix'] . "-" . $varName . ": " .
                      $color . ";" . $options['line_ending'];
        }

        $output .= "}" . $options['line_ending'];

        // Add utility classes if requested
        if (count($options['generate_classes']) > 0) {
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
    protected function format_scss(array $palette, array $options): string {
        $output = "";

        foreach ($palette as $name => $color) {
            if ($options['include_comments']) {
                $output .= "// {$name}" . $options['line_ending'];
            }

            $varName = sanitize_title($name);
            $output .= "$" . $options['variable_prefix'] . "-" . $varName . ": " .
                      $color . ";" . $options['line_ending'];
        }

        // Add color map
        $output .= $options['line_ending'] . '$'. 'color-palette: (' . $options['line_ending'];

        foreach ($palette as $name => $color) {
            $varName = sanitize_title($name);
            $output .= $options['indent'] . "'" . $varName . "': " .
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
    protected function format_less(array $palette, array $options): string {
        $output = "";

        foreach ($palette as $name => $color) {
            if ($options['include_comments']) {
                $output .= "// {$name}" . $options['line_ending'];
            }

            $varName = sanitize_title($name);
            $output .= "@" . $options['variable_prefix'] . "-" . $varName . ": " .
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
    protected function format_tailwind(array $palette, array $options): string {
        $output = "module.exports = {" . $options['line_ending'];
        $output .= $options['indent'] . "theme: {" . $options['line_ending'];
        $output .= $options['indent'] . $options['indent'] . "colors: {" . $options['line_ending'];

        foreach ($palette as $name => $color) {
            if ($options['include_comments']) {
                $output .= $options['indent'] . $options['indent'] . $options['indent'] .
                          "// {$name}" . $options['line_ending'];
            }

            $varName = sanitize_title($name);
            $output .= $options['indent'] . $options['indent'] . $options['indent'] .
                      "'" . $varName . "': '" . $color . "'," . $options['line_ending'];
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
    protected function format_json(array $palette, array $options): string {
        $formatted_colors = [];
        foreach ($palette as $name => $color) {
            $varName = sanitize_title($name);
            $formatted_colors[$varName] = $color;
        }

        return wp_json_encode(
            $formatted_colors,
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
    protected function generate_css_classes(array $palette, array $options): string {
        $output = "";

        foreach ($palette as $name => $color) {
            $varName = sanitize_title($name);
            $className = "." . $options['class_prefix'] . "-" . $varName;

            // Background color class
            $output .= "{$className}-bg {" . $options['line_ending'];
            $output .= $options['indent'] . "background-color: " . $color . ";" . $options['line_ending'];
            $output .= "}" . $options['line_ending'];

            // Text color class
            $output .= "{$className}-text {" . $options['line_ending'];
            $output .= $options['indent'] . "color: " . $color . ";" . $options['line_ending'];
            $output .= "}" . $options['line_ending'];

            // Border color class
            $output .= "{$className}-border {" . $options['line_ending'];
            $output .= $options['indent'] . "border-color: " . $color . ";" . $options['line_ending'];
            $output .= "}" . $options['line_ending'];
        }

        return $output;
    }
}
