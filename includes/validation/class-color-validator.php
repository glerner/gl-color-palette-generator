<?php
/**
 * Color Validator Class
 *
 * Handles low-level color validation and conversion operations:
 * - Validates color format syntax (hex, RGB, HSL, etc.)
 * - Converts between different color formats
 * - Provides utility functions for color space transformations
 *
 * Note: This class focuses on individual color validation and conversion.
 * For higher-level color palette validation, see Color_Validation class.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Validation
 * @since 1.0.0
 * @todo Consider merging with Color_Validation class in future versions
 */

namespace GL_Color_Palette_Generator\Validation;

use GL_Color_Palette_Generator\Core\Logger;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Color_Validator
 *
 * Validates color values and formats for the palette generator
 *
 * @since 1.0.0
 */
class Color_Validator {
    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * Validate color value
     *
     * @param string $color Color value to validate
     * @param string $format Specific format to validate against (optional)
     * @return bool Whether color is valid
     */
    public function is_valid(string $color, string $format = ''): bool {
        if ($format !== '' && isset(Color_Constants::COLOR_SPACE_CONVERSION['formats'][$format])) {
            return (bool) preg_match(Color_Constants::COLOR_SPACE_CONVERSION['formats'][$format], $color);
        }

        foreach (Color_Constants::COLOR_SPACE_CONVERSION['formats'] as $pattern) {
            if (preg_match($pattern, $color)) {
                return true;
            }
        }

        $this->logger->debug('Invalid color format', [
            'color' => $color,
            'requested_format' => $format
        ]);

        return false;
    }

    /**
     * Get color format
     *
     * @param string $color Color value
     * @return string|null Format name or null if invalid
     */
    public function get_format(string $color): ?string {
        foreach (Color_Constants::COLOR_SPACE_CONVERSION['formats'] as $format => $pattern) {
            if (preg_match($pattern, $color)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Convert color to hex format
     *
     * @param string $color Color value
     * @return string|null Hex color or null if conversion fails
     */
    public function to_hex(string $color): ?string {
        $format = $this->get_format($color);
        if ($format === null) {
            return null;
        }

        switch ($format) {
            case 'hex':
                return $this->normalize_hex($color);

            case 'rgb':
            case 'rgba':
                return $this->rgb_to_hex($color);

            case 'hsl':
            case 'hsla':
                return $this->hsl_to_hex($color);

            default:
                return null;
        }
    }

    /**
     * Normalize hex color
     *
     * @param string $hex Hex color value
     * @return string Normalized hex color
     */
    private function normalize_hex(string $hex): string {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return '#' . strtoupper($hex);
    }

    /**
     * Convert RGB(A) to hex
     *
     * @param string $rgb RGB(A) color value
     * @return string|null Hex color or null if conversion fails
     */
    private function rgb_to_hex(string $rgb): ?string {
        if (!preg_match(Color_Constants::COLOR_SPACE_CONVERSION['formats']['rgb'], $rgb, $matches) &&
            !preg_match(Color_Constants::COLOR_SPACE_CONVERSION['formats']['rgba'], $rgb, $matches)) {
            return null;
        }

        $r = str_pad(dechex((int) $matches[1]), 2, '0', STR_PAD_LEFT);
        $g = str_pad(dechex((int) $matches[2]), 2, '0', STR_PAD_LEFT);
        $b = str_pad(dechex((int) $matches[3]), 2, '0', STR_PAD_LEFT);

        return "#{$r}{$g}{$b}";
    }

    /**
     * Convert HSL(A) to hex
     *
     * @param string $hsl HSL(A) color value
     * @return string|null Hex color or null if conversion fails
     */
    private function hsl_to_hex(string $hsl): ?string {
        if (!preg_match(Color_Constants::COLOR_SPACE_CONVERSION['formats']['hsl'], $hsl, $matches) &&
            !preg_match(Color_Constants::COLOR_SPACE_CONVERSION['formats']['hsla'], $hsl, $matches)) {
            return null;
        }

        $h = (float) $matches[1];
        $s = (float) $matches[2];
        $l = (float) $matches[3];

        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s === 0.0) {
            $r = $g = $b = (int) ($l * 255);
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = (int) (255 * $this->hue_to_rgb($p, $q, $h + 1/3));
            $g = (int) (255 * $this->hue_to_rgb($p, $q, $h));
            $b = (int) (255 * $this->hue_to_rgb($p, $q, $h - 1/3));
        }

        $hex = sprintf("#%02X%02X%02X", $r, $g, $b);
        return $hex;
    }

    /**
     * Helper function for HSL to RGB conversion
     *
     * @param float $p First parameter
     * @param float $q Second parameter
     * @param float $t Third parameter
     * @return float Resulting value
     */
    private function hue_to_rgb(float $p, float $q, float $t): float {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }
}
