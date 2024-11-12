<?php
/**
 * Color Palette Formatter Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteFormatterInterface;

/**
 * Handles color format conversions and validation.
 */
class ColorPaletteFormatter implements ColorPaletteFormatterInterface {
    /**
     * Supported color formats.
     *
     * @var array
     */
    private array $supported_formats = ['hex', 'rgb', 'rgba', 'hsl', 'hsla'];

    /**
     * Format regex patterns.
     *
     * @var array
     */
    private array $format_patterns = [
        'hex'  => '/^#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/',
        'rgb'  => '/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/',
        'rgba' => '/^rgba\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*([01]\.?\d*)\s*\)$/',
        'hsl'  => '/^hsl\(\s*(\d{1,3})\s*,\s*(\d{1,3})%\s*,\s*(\d{1,3})%\s*\)$/',
        'hsla' => '/^hsla\(\s*(\d{1,3})\s*,\s*(\d{1,3})%\s*,\s*(\d{1,3})%\s*,\s*([01]\.?\d*)\s*\)$/'
    ];

    /**
     * Formats a color to the specified format.
     *
     * @param string $color  Color value to format.
     * @param string $format Target format (hex, rgb, hsl, etc.).
     * @return string Formatted color value.
     * @throws \InvalidArgumentException If format is invalid.
     */
    public function formatColor(string $color, string $format): string {
        if (!in_array($format, $this->supported_formats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        // Normalize input color to RGB array
        $rgb = $this->toRgbArray($color);

        // Convert to target format
        return match ($format) {
            'hex'  => $this->rgbToHex($rgb),
            'rgb'  => $this->rgbToRgbString($rgb),
            'rgba' => $this->rgbToRgbaString($rgb),
            'hsl'  => $this->rgbToHslString($rgb),
            'hsla' => $this->rgbToHslaString($rgb),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Validates a color format.
     *
     * @param string $color  Color to validate.
     * @param string $format Format to validate against.
     * @return bool True if valid.
     */
    public function isValidFormat(string $color, string $format): bool {
        if (!isset($this->format_patterns[$format])) {
            return false;
        }

        return (bool) preg_match($this->format_patterns[$format], $color);
    }

    /**
     * Gets supported color formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array {
        return $this->supported_formats;
    }

    /**
     * Normalizes a color value to hex format.
     *
     * @param string $color Color to normalize.
     * @return string Normalized hex color value.
     * @throws \InvalidArgumentException If color is invalid.
     */
    public function normalizeColor(string $color): string {
        return $this->formatColor($color, 'hex');
    }

    /**
     * Converts any supported color format to RGB array.
     *
     * @param string $color Color value to convert.
     * @return array RGB values [r, g, b].
     * @throws \InvalidArgumentException If color format is invalid.
     */
    private function toRgbArray(string $color): array {
        $color = trim($color);

        // Try hex format
        if (preg_match($this->format_patterns['hex'], $color)) {
            return $this->hexToRgbArray($color);
        }

        // Try RGB format
        if (preg_match($this->format_patterns['rgb'], $color, $matches)) {
            return [
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            ];
        }

        // Try RGBA format
        if (preg_match($this->format_patterns['rgba'], $color, $matches)) {
            return [
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            ];
        }

        // Try HSL format
        if (preg_match($this->format_patterns['hsl'], $color, $matches)) {
            return $this->hslToRgbArray([
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            ]);
        }

        // Try HSLA format
        if (preg_match($this->format_patterns['hsla'], $color, $matches)) {
            return $this->hslToRgbArray([
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            ]);
        }

        throw new \InvalidArgumentException("Invalid color format: {$color}");
    }

    /**
     * Converts hex color to RGB array.
     *
     * @param string $hex Hex color value.
     * @return array RGB values [r, g, b].
     */
    private function hexToRgbArray(string $hex): array {
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
     * Converts HSL values to RGB array.
     *
     * @param array $hsl HSL values [h, s, l].
     * @return array RGB values [r, g, b].
     */
    private function hslToRgbArray(array $hsl): array {
        [$h, $s, $l] = $hsl;
        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = $this->hueToRgb($p, $q, $h + 1/3);
            $g = $this->hueToRgb($p, $q, $h);
            $b = $this->hueToRgb($p, $q, $h - 1/3);
        }

        return [
            round($r * 255),
            round($g * 255),
            round($b * 255)
        ];
    }

    /**
     * Helper function for HSL to RGB conversion.
     *
     * @param float $p First value.
     * @param float $q Second value.
     * @param float $t Third value.
     * @return float Resulting value.
     */
    private function hueToRgb(float $p, float $q, float $t): float {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    /**
     * Converts RGB array to hex string.
     *
     * @param array $rgb RGB values [r, g, b].
     * @return string Hex color value.
     */
    private function rgbToHex(array $rgb): string {
        return sprintf('#%02X%02X%02X', ...$rgb);
    }

    /**
     * Converts RGB array to RGB string.
     *
     * @param array $rgb RGB values [r, g, b].
     * @return string RGB color value.
     */
    private function rgbToRgbString(array $rgb): string {
        return sprintf('rgb(%d, %d, %d)', ...$rgb);
    }

    /**
     * Converts RGB array to RGBA string.
     *
     * @param array $rgb RGB values [r, g, b].
     * @param float $alpha Alpha value (0-1).
     * @return string RGBA color value.
     */
    private function rgbToRgbaString(array $rgb, float $alpha = 1.0): string {
        return sprintf('rgba(%d, %d, %d, %.2f)', ...array_merge($rgb, [$alpha]));
    }

    /**
     * Converts RGB array to HSL string.
     *
     * @param array $rgb RGB values [r, g, b].
     * @return string HSL color value.
     */
    private function rgbToHslString(array $rgb): string {
        $hsl = $this->rgbToHslArray($rgb);
        return sprintf('hsl(%d, %d%%, %d%%)', ...$hsl);
    }

    /**
     * Converts RGB array to HSLA string.
     *
     * @param array $rgb RGB values [r, g, b].
     * @param float $alpha Alpha value (0-1).
     * @return string HSLA color value.
     */
    private function rgbToHslaString(array $rgb, float $alpha = 1.0): string {
        $hsl = $this->rgbToHslArray($rgb);
        return sprintf('hsla(%d, %d%%, %d%%, %.2f)', ...array_merge($hsl, [$alpha]));
    }

    /**
     * Converts RGB array to HSL array.
     *
     * @param array $rgb RGB values [r, g, b].
     * @return array HSL values [h, s, l].
     */
    private function rgbToHslArray(array $rgb): array {
        [$r, $g, $b] = array_map(fn($val) => $val / 255, $rgb);

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            $h = match ($max) {
                $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
                $g => ($b - $r) / $d + 2,
                $b => ($r - $g) / $d + 4,
                default => 0
            };

            $h /= 6;
        }

        return [
            round($h * 360),
            round($s * 100),
            round($l * 100)
        ];
    }
} 
