<?php

namespace GLColorPalette;

/**
 * Color Palette Formatter Class
 *
 * Handles formatting and conversion of color values between different formats.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteFormatter {
    /**
     * Supported color formats.
     *
     * @var array
     */
    private $supported_formats = [
        'hex' => '/^#(?:[0-9a-fA-F]{3}){1,2}$/',
        'rgb' => '/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/',
        'rgba' => '/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(?:0|1|0?\.\d+)\s*\)$/',
        'hsl' => '/^hsl\(\s*\d+\s*,\s*\d+%?\s*,\s*\d+%?\s*\)$/',
        'hsla' => '/^hsla\(\s*\d+\s*,\s*\d+%?\s*,\s*\d+%?\s*,\s*(?:0|1|0?\.\d+)\s*\)$/'
    ];

    /**
     * Formats a color value to specified format.
     *
     * @param string $color Color value to format.
     * @param string $format Target format.
     * @return string Formatted color value.
     * @throws \InvalidArgumentException If color or format is invalid.
     */
    public function format_color(string $color, string $format): string {
        $current_format = $this->detect_format($color);
        if (!$current_format) {
            throw new \InvalidArgumentException("Invalid color value: {$color}");
        }

        if (!array_key_exists($format, $this->supported_formats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        if ($current_format === $format) {
            return $color;
        }

        // Convert to RGB array as intermediate format
        $rgb = $this->to_rgb_array($color, $current_format);

        // Convert from RGB array to target format
        return $this->from_rgb_array($rgb, $format);
    }

    /**
     * Detects format of color value.
     *
     * @param string $color Color value.
     * @return string|null Format name or null if not detected.
     */
    public function detect_format(string $color): ?string {
        foreach ($this->supported_formats as $format => $pattern) {
            if (preg_match($pattern, $color)) {
                return $format;
            }
        }
        return null;
    }

    /**
     * Gets supported color formats.
     *
     * @return array Array of supported formats.
     */
    public function get_supported_formats(): array {
        return array_keys($this->supported_formats);
    }

    /**
     * Converts color to RGB array.
     *
     * @param string $color Color value.
     * @param string $format Color format.
     * @return array RGB values [r, g, b].
     */
    private function to_rgb_array(string $color, string $format): array {
        switch ($format) {
            case 'hex':
                return $this->hex_to_rgb($color);
            case 'rgb':
            case 'rgba':
                return $this->parse_rgb($color);
            case 'hsl':
            case 'hsla':
                return $this->hsl_to_rgb($color);
            default:
                throw new \InvalidArgumentException("Unsupported format: {$format}");
        }
    }

    /**
     * Converts RGB array to target format.
     *
     * @param array $rgb RGB values [r, g, b].
     * @param string $format Target format.
     * @return string Formatted color value.
     */
    private function from_rgb_array(array $rgb, string $format): string {
        switch ($format) {
            case 'hex':
                return $this->rgb_to_hex($rgb);
            case 'rgb':
                return sprintf('rgb(%d, %d, %d)', ...$rgb);
            case 'rgba':
                return sprintf('rgba(%d, %d, %d, 1)', ...$rgb);
            case 'hsl':
                $hsl = $this->rgb_to_hsl($rgb);
                return sprintf('hsl(%d, %d%%, %d%%)', ...$hsl);
            case 'hsla':
                $hsl = $this->rgb_to_hsl($rgb);
                return sprintf('hsla(%d, %d%%, %d%%, 1)', ...$hsl);
            default:
                throw new \InvalidArgumentException("Unsupported format: {$format}");
        }
    }

    /**
     * Converts hex color to RGB array.
     *
     * @param string $hex Hex color value.
     * @return array RGB values [r, g, b].
     */
    private function hex_to_rgb(string $hex): array {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Parses RGB/RGBA color to RGB array.
     *
     * @param string $color RGB/RGBA color value.
     * @return array RGB values [r, g, b].
     */
    private function parse_rgb(string $color): array {
        preg_match('/\((.+)\)/', $color, $matches);
        $values = explode(',', $matches[1]);
        return array_map(function($val) {
            return (int) trim($val);
        }, array_slice($values, 0, 3));
    }

    /**
     * Converts HSL/HSLA color to RGB array.
     *
     * @param string $color HSL/HSLA color value.
     * @return array RGB values [r, g, b].
     */
    private function hsl_to_rgb(string $color): array {
        preg_match('/\((.+)\)/', $color, $matches);
        $values = explode(',', $matches[1]);
        $h = (float) trim($values[0]);
        $s = (float) trim($values[1], '% ');
        $l = (float) trim($values[2], '% ');

        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s === 0) {
            $val = (int) round($l * 255);
            return [$val, $val, $val];
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        return [
            (int) round($this->hue_to_rgb($p, $q, $h + 1/3) * 255),
            (int) round($this->hue_to_rgb($p, $q, $h) * 255),
            (int) round($this->hue_to_rgb($p, $q, $h - 1/3) * 255)
        ];
    }

    /**
     * Helper function for HSL to RGB conversion.
     */
    private function hue_to_rgb(float $p, float $q, float $t): float {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    /**
     * Converts RGB array to hex color.
     *
     * @param array $rgb RGB values [r, g, b].
     * @return string Hex color value.
     */
    private function rgb_to_hex(array $rgb): string {
        return sprintf('#%02x%02x%02x', ...$rgb);
    }

    /**
     * Converts RGB array to HSL array.
     *
     * @param array $rgb RGB values [r, g, b].
     * @return array HSL values [h, s, l].
     */
    private function rgb_to_hsl(array $rgb): array {
        list($r, $g, $b) = array_map(function($val) {
            return $val / 255;
        }, $rgb);

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return [
            (int) round($h * 360),
            (int) round($s * 100),
            (int) round($l * 100)
        ];
    }
} 
