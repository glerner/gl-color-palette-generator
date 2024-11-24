<?php
namespace GLColorPalette;
class ColorExporter {
    private $formatter;
    private $validator;
    private $settings;

    / Export formats
    private const EXPORT_FORMATS = [
        'css' => ['variables', 'tailwind', 'sass', 'less'],
        'design' => ['sketch', 'figma', 'adobe_xd', 'invision'],
        'code' => ['json', 'js', 'ts', 'swift', 'kotlin'],
        'system' => ['android', 'ios', 'flutter', 'react-native'],
        'web' => ['html', 'svg', 'postcss', 'styled-components']
    ];

    public function __construct() {
        $this->formatter = new ColorFormatter();
        $this->validator = new PaletteValidator();
        $this->settings = new SettingsManager();
    }

    /**
     * Export palette in specified format
     */
    public function export_palette($palette, $format, $options = []) {
        try {
            / Validate palette before export
            if (!$this->validator->validate_palette($palette)) {
                throw new ExportException('Invalid palette for export');
            }

            switch ($format) {
                case 'css':
                    return $this->export_css($palette, $options);
                case 'design':
                    return $this->export_design_tool($palette, $options);
                case 'code':
                    return $this->export_code($palette, $options);
                case 'system':
                    return $this->export_system($palette, $options);
                case 'web':
                    return $this->export_web($palette, $options);
                default:
                    throw new ExportException("Unsupported export format: {$format}");
            }
        } catch (Exception $e) {
            throw new ExportException(
                "Export failed: " . $e->getMessage(),
                ErrorCodes::EXPORT_FAILED
            );
        }
    }

    /**
     * Export CSS formats
     */
    private function export_css($palette, $options) {
        $format = $options['css_format'] ?? 'variables';

        switch ($format) {
            case 'variables':
                return $this->export_css_variables($palette, $options);
            case 'tailwind':
                return $this->export_tailwind_config($palette, $options);
            case 'sass':
                return $this->export_sass_variables($palette, $options);
            case 'less':
                return $this->export_less_variables($palette, $options);
        }
    }

    /**
     * Export CSS Variables
     */
    private function export_css_variables($palette, $options) {
        $prefix = $options['prefix'] ?? 'color';
        $output = ":root {\n";

        foreach ($palette as $name => $color) {
            if (is_string($color)) {
                $output .= "  --{$prefix}-{$name}: {$color};\n";
            } elseif (is_array($color)) {
                foreach ($color as $shade => $value) {
                    $output .= "  --{$prefix}-{$name}-{$shade}: {$value};\n";
                }
            }
        }

        $output .= "}\n";
        return $output;
    }

    /**
     * Export Tailwind config
     */
    private function export_tailwind_config($palette, $options) {
        $output = "module.exports = {\n";
        $output .= "  theme: {\n";
        $output .= "    colors: {\n";

        foreach ($palette as $name => $color) {
            if (is_string($color)) {
                $output .= "      '{$name}': '{$color}',\n";
            } elseif (is_array($color)) {
                $output .= "      '{$name}': {\n";
                foreach ($color as $shade => $value) {
                    $output .= "        '{$shade}': '{$value}',\n";
                }
                $output .= "      },\n";
            }
        }

        $output .= "    },\n";
        $output .= "  },\n";
        $output .= "};\n";
        return $output;
    }

    /**
     * Export design tool formats
     */
    private function export_design_tool($palette, $options) {
        $tool = $options['design_tool'] ?? 'sketch';

        switch ($tool) {
            case 'sketch':
                return $this->export_sketch_palette($palette, $options);
            case 'figma':
                return $this->export_figma_styles($palette, $options);
            case 'adobe_xd':
                return $this->export_xd_assets($palette, $options);
            case 'invision':
                return $this->export_invision_dsm($palette, $options);
        }
    }

    /**
     * Export code formats
     */
    private function export_code($palette, $options) {
        $language = $options['language'] ?? 'json';

        switch ($language) {
            case 'json':
                return $this->export_json($palette, $options);
            case 'js':
                return $this->export_javascript($palette, $options);
            case 'ts':
                return $this->export_typescript($palette, $options);
            case 'swift':
                return $this->export_swift($palette, $options);
            case 'kotlin':
                return $this->export_kotlin($palette, $options);
        }
    }

    /**
     * Export system formats
     */
    private function export_system($palette, $options) {
        $platform = $options['platform'] ?? 'android';

        switch ($platform) {
            case 'android':
                return $this->export_android_resources($palette, $options);
            case 'ios':
                return $this->export_ios_assets($palette, $options);
            case 'flutter':
                return $this->export_flutter_theme($palette, $options);
            case 'react-native':
                return $this->export_react_native_theme($palette, $options);
        }
    }

    /**
     * Export web formats
     */
    private function export_web($palette, $options) {
        $format = $options['web_format'] ?? 'html';

        switch ($format) {
            case 'html':
                return $this->export_html_preview($palette, $options);
            case 'svg':
                return $this->export_svg_swatches($palette, $options);
            case 'postcss':
                return $this->export_postcss_custom_properties($palette, $options);
            case 'styled-components':
                return $this->export_styled_components_theme($palette, $options);
        }
    }

    /**
     * Export Android resources
     */
    private function export_android_resources($palette, $options) {
        $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $output .= "<resources>\n";

        foreach ($palette as $name => $color) {
            if (is_string($color)) {
                $output .= "    <color name=\"{$name}\">{$color}</color>\n";
            } elseif (is_array($color)) {
                foreach ($color as $shade => $value) {
                    $output .= "    <color name=\"{$name}_{$shade}\">{$value}</color>\n";
                }
            }
        }

        $output .= "</resources>";
        return $output;
    }

    /**
     * Export iOS assets
     */
    private function export_ios_assets($palette, $options) {
        $output = "import UIKit\n\n";
        $output .= "extension UIColor {\n";
        $output .= "    struct Palette {\n";

        foreach ($palette as $name => $color) {
            if (is_string($color)) {
                $rgb = $this->formatter->hex_to_rgb($color);
                $output .= "        static let {$name} = UIColor(red: {$rgb['r']}/255, green: {$rgb['g']}/255, blue: {$rgb['b']}/255, alpha: 1)\n";
            } elseif (is_array($color)) {
                foreach ($color as $shade => $value) {
                    $rgb = $this->formatter->hex_to_rgb($value);
                    $output .= "        static let {$name}_{$shade} = UIColor(red: {$rgb['r']}/255, green: {$rgb['g']}/255, blue: {$rgb['b']}/255, alpha: 1)\n";
                }
            }
        }

        $output .= "    }\n";
        $output .= "}\n";
        return $output;
    }

    /**
     * Export Flutter theme
     */
    private function export_flutter_theme($palette, $options) {
        $output = "import 'package:flutter/material.dart';\n\n";
        $output .= "class AppColors {\n";

        foreach ($palette as $name => $color) {
            if (is_string($color)) {
                $hex = str_replace('#', '0xFF', $color);
                $output .= "  static const Color {$name} = Color({$hex});\n";
            } elseif (is_array($color)) {
                foreach ($color as $shade => $value) {
                    $hex = str_replace('#', '0xFF', $value);
                    $output .= "  static const Color {$name}_{$shade} = Color({$hex});\n";
                }
            }
        }

        $output .= "}\n";
        return $output;
    }
}
