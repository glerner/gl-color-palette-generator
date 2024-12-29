<?php
/**
 * Color Metrics Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Metrics_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;

/**
 * Class Color_Metrics
 */
class Color_Metrics implements Color_Metrics_Interface {
    private Color_Utility $color_util;

    /**
     * Constructor
     */
    public function __construct(Color_Utility $color_util) {
        $this->color_util = $color_util;
    }

    /**
     * Calculate perceived brightness
     *
     * @param string $color Color in hex format
     * @return float Brightness value (0-1)
     */
    public function calculate_brightness(string $color): float {
        $rgb = $this->color_util->hex_to_rgb($color);
        return ($rgb['r'] * 0.299 + $rgb['g'] * 0.587 + $rgb['b'] * 0.114) / 255;
    }

    /**
     * Calculate color saturation
     *
     * @param string $color Color in hex format
     * @return float Saturation value (0-1)
     */
    public function calculate_saturation(string $color): float {
        $rgb = $this->color_util->hex_to_rgb($color);
        $hsl = $this->color_util->rgb_to_hsl($rgb);
        return $hsl['s'];
    }

    /**
     * Calculate color balance in a palette
     *
     * @param array $colors Array of colors in hex format
     * @return array Balance metrics
     */
    public function calculate_balance(array $colors): array {
        $total_brightness = 0;
        $total_saturation = 0;
        $light_colors = 0;
        $dark_colors = 0;

        foreach ($colors as $color) {
            $brightness = $this->calculate_brightness($color);
            $saturation = $this->calculate_saturation($color);

            $total_brightness += $brightness;
            $total_saturation += $saturation;

            if ($brightness > 0.6) {
                $light_colors++;
            } elseif ($brightness < 0.4) {
                $dark_colors++;
            }
        }

        $count = count($colors);
        return [
            'average_brightness' => $total_brightness / $count,
            'average_saturation' => $total_saturation / $count,
            'light_dark_ratio' => $count > 0 ? $light_colors / $dark_colors : 0,
            'balance_score' => $this->calculate_balance_score($colors)
        ];
    }

    /**
     * Calculate color weight
     *
     * @param string $color Color in hex format
     * @return float Weight value (0-1)
     */
    public function calculate_weight(string $color): float {
        $brightness = $this->calculate_brightness($color);
        $saturation = $this->calculate_saturation($color);

        // Weight is influenced by both darkness and saturation
        return (1 - $brightness) * (1 + $saturation) / 2;
    }

    /**
     * Calculate color contrast ratio
     *
     * @deprecated 2.0.0 Use Color_Utility::get_contrast_ratio() instead
     * @see Color_Utility::get_contrast_ratio()
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float Contrast ratio between 1 and 21
     */
    public function calculate_contrast_ratio(string $color1, string $color2): float {
        trigger_error(
            'Method ' . __METHOD__ . ' is deprecated. Use Color_Utility::get_contrast_ratio() instead.',
            E_USER_DEPRECATED
        );
        return $this->color_util->get_contrast_ratio($color1, $color2);
    }

    /**
     * Calculate balance score for a color palette
     *
     * @param array $colors Array of colors
     * @return float Balance score between 0 and 1
     */
    private function calculate_balance_score(array $colors): float {
        if (count($colors) < 2) {
            return 1.0;
        }

        $brightness_variance = 0;
        $saturation_variance = 0;
        $total_brightness = 0;
        $total_saturation = 0;

        // Calculate means
        foreach ($colors as $color) {
            $brightness = $this->calculate_brightness($color);
            $saturation = $this->calculate_saturation($color);
            $total_brightness += $brightness;
            $total_saturation += $saturation;
        }

        $mean_brightness = $total_brightness / count($colors);
        $mean_saturation = $total_saturation / count($colors);

        // Calculate variances
        foreach ($colors as $color) {
            $brightness = $this->calculate_brightness($color);
            $saturation = $this->calculate_saturation($color);
            $brightness_variance += pow($brightness - $mean_brightness, 2);
            $saturation_variance += pow($saturation - $mean_saturation, 2);
        }

        $brightness_variance /= count($colors);
        $saturation_variance /= count($colors);

        // Lower variance means better balance
        return 1 - (($brightness_variance + $saturation_variance) / 2);
    }
}
