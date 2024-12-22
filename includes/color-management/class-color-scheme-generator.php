<?php
/**
 * Color Scheme Generator Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Scheme_Generator_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;

/**
 * Class Color_Scheme_Generator
 * 
 * Implements color scheme generation functionality
 */
class Color_Scheme_Generator implements Color_Scheme_Generator_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Color metrics instance
     *
     * @var Color_Metrics
     */
    private $color_metrics;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
        $this->color_metrics = new Color_Metrics();
    }

    /**
     * Generate a color scheme from a base color
     *
     * @param string $base_color Base color in hex format
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_scheme($base_color, $options = []) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $scheme_type = isset($options['type']) ? $options['type'] : 'monochromatic';
        $count = isset($options['count']) ? $options['count'] : 5;

        // Validate scheme type
        $valid_schemes = array_keys(Color_Constants::COLOR_SCHEMES);
        if (!in_array($scheme_type, $valid_schemes)) {
            return new WP_Error('invalid_scheme', 'Invalid scheme type provided');
        }

        switch ($scheme_type) {
            case 'monochromatic':
                return $this->generate_monochromatic($base_color, $count);
            case 'analogous':
                return $this->generate_analogous($base_color, $count);
            case 'complementary':
                return $this->generate_complementary($base_color, $count);
            case 'split-complementary':
                return $this->generate_split_complementary($base_color, $count);
            case 'triadic':
                return $this->generate_triadic($base_color, $count);
            case 'tetradic':
                return $this->generate_tetradic($base_color, $count);
            default:
                return new WP_Error('invalid_scheme', 'Invalid scheme type provided');
        }
    }

    /**
     * Generate a monochromatic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_monochromatic($base_color, $count = 5) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $hsv = $this->color_utility->hex_to_hsv($base_color);
        $colors = [];

        // Keep hue constant, vary saturation and value
        for ($i = 0; $i < $count; $i++) {
            $saturation = max(0, min(100, $hsv['s'] + (($i - floor($count / 2)) * 20)));
            $value = max(0, min(100, $hsv['v'] + (($i - floor($count / 2)) * 20)));
            $colors[] = $this->color_utility->hsv_to_hex(['h' => $hsv['h'], 's' => $saturation, 'v' => $value]);
        }

        return $colors;
    }

    /**
     * Generate an analogous scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_analogous($base_color, $count = 5) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $hsv = $this->color_utility->hex_to_hsv($base_color);
        $colors = [];
        $hue_step = Color_Constants::COLOR_HARMONY_RULES['analogous']['angle'];

        for ($i = 0; $i < $count; $i++) {
            $hue = ($hsv['h'] + ($i - floor($count / 2)) * $hue_step + 360) % 360;
            $colors[] = $this->color_utility->hsv_to_hex(['h' => $hue, 's' => $hsv['s'], 'v' => $hsv['v']]);
        }

        return $colors;
    }

    /**
     * Generate a complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_complementary($base_color, $count = 4) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $hsv = $this->color_utility->hex_to_hsv($base_color);
        $complement_angle = Color_Constants::COLOR_HARMONY_RULES['complementary']['angle'];
        $complement_hue = ($hsv['h'] + $complement_angle) % 360;
        $colors = [$base_color];

        // Add variations of the base color and its complement
        for ($i = 1; $i < $count / 2; $i++) {
            $saturation = max(Color_Constants::MIN_SATURATION_RANGE, min(Color_Constants::MAX_SATURATION, $hsv['s'] - ($i * 20)));
            $colors[] = $this->color_utility->hsv_to_hex(['h' => $hsv['h'], 's' => $saturation, 'v' => $hsv['v']]);
            $colors[] = $this->color_utility->hsv_to_hex(['h' => $complement_hue, 's' => $saturation, 'v' => $hsv['v']]);
        }

        $colors[] = $this->color_utility->hsv_to_hex(['h' => $complement_hue, 's' => $hsv['s'], 'v' => $hsv['v']]);

        return array_slice($colors, 0, $count);
    }

    /**
     * Generate a split complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_split_complementary($base_color, $count = 3) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $hsv = $this->color_utility->hex_to_hsv($base_color);
        $colors = [$base_color];

        // Add split complements using the defined angle from constants
        $split_angle = Color_Constants::COLOR_HARMONY_RULES['split-complementary']['angle'];
        $colors[] = $this->color_utility->hsv_to_hex(['h' => ($hsv['h'] + $split_angle) % 360, 's' => $hsv['s'], 'v' => $hsv['v']]);
        $colors[] = $this->color_utility->hsv_to_hex(['h' => ($hsv['h'] - $split_angle + 360) % 360, 's' => $hsv['s'], 'v' => $hsv['v']]);

        return array_slice($colors, 0, $count);
    }

    /**
     * Generate a triadic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_triadic($base_color, $count = 3) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $hsv = $this->color_utility->hex_to_hsv($base_color);
        $colors = [$base_color];

        // Add colors using the triadic angle from constants
        $triadic_angle = Color_Constants::COLOR_HARMONY_RULES['triadic']['angle'];
        $colors[] = $this->color_utility->hsv_to_hex(['h' => ($hsv['h'] + $triadic_angle) % 360, 's' => $hsv['s'], 'v' => $hsv['v']]);
        $colors[] = $this->color_utility->hsv_to_hex(['h' => ($hsv['h'] + 2 * $triadic_angle) % 360, 's' => $hsv['s'], 'v' => $hsv['v']]);

        return array_slice($colors, 0, $count);
    }

    /**
     * Generate a tetradic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_tetradic($base_color, $count = 4) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        $hsv = $this->color_utility->hex_to_hsv($base_color);
        $colors = [$base_color];

        // Add colors at 90, 180, and 270 degrees
        for ($i = 1; $i <= 3; $i++) {
            $hue = ($hsv['h'] + ($i * 90)) % 360;
            $colors[] = $this->color_utility->hsv_to_hex(['h' => $hue, 's' => $hsv['s'], 'v' => $hsv['v']]);
        }

        return array_slice($colors, 0, $count);
    }

    /**
     * Generate a custom scheme based on color theory rules
     *
     * @param string $base_color Base color in hex format
     * @param array  $rules Color theory rules to apply
     * @return array|WP_Error Array of colors or error
     */
    public function generate_custom_scheme($base_color, $rules) {
        if (!$this->color_utility->is_valid_hex_color($base_color)) {
            return new WP_Error('invalid_color', 'Invalid base color provided');
        }

        if (!is_array($rules) || empty($rules)) {
            return new WP_Error('invalid_rules', 'Invalid or empty rules provided');
        }

        $colors = [$base_color];
        $hsv = $this->color_utility->hex_to_hsv($base_color);

        foreach ($rules as $rule) {
            if (!isset($rule['type'], $rule['value'])) {
                continue;
            }

            switch ($rule['type']) {
                case 'hue_shift':
                    $new_hue = ($hsv['h'] + $rule['value']) % 360;
                    $colors[] = $this->color_utility->hsv_to_hex(['h' => $new_hue, 's' => $hsv['s'], 'v' => $hsv['v']]);
                    break;
                case 'saturation_shift':
                    $new_saturation = max(0, min(100, $hsv['s'] + $rule['value']));
                    $colors[] = $this->color_utility->hsv_to_hex(['h' => $hsv['h'], 's' => $new_saturation, 'v' => $hsv['v']]);
                    break;
                case 'value_shift':
                    $new_value = max(0, min(100, $hsv['v'] + $rule['value']));
                    $colors[] = $this->color_utility->hsv_to_hex(['h' => $hsv['h'], 's' => $hsv['s'], 'v' => $new_value]);
                    break;
            }
        }

        return $colors;
    }

    /**
     * Generate a scheme from an image
     *
     * @param string $image_path Path to image file
     * @param array  $options Extraction options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_from_image($image_path, $options = []) {
        if (!file_exists($image_path)) {
            return new WP_Error('invalid_image', 'Image file does not exist');
        }

        $count = isset($options['count']) ? $options['count'] : 5;
        $colors = [];

        try {
            // Create image resource based on file type
            $image_info = getimagesize($image_path);
            if ($image_info === false) {
                return new WP_Error('invalid_image', 'Invalid image file');
            }

            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($image_path);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($image_path);
                    break;
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($image_path);
                    break;
                default:
                    return new WP_Error('unsupported_type', 'Unsupported image type');
            }

            if ($image === false) {
                return new WP_Error('image_creation_failed', 'Failed to create image resource');
            }

            // Resize image for faster processing
            $thumb_width = 150;
            $thumb_height = (int) ($image_info[1] * ($thumb_width / $image_info[0]));
            $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $image_info[0], $image_info[1]);

            // Create color map
            $color_map = [];
            for ($x = 0; $x < $thumb_width; $x++) {
                for ($y = 0; $y < $thumb_height; $y++) {
                    $rgb = imagecolorat($thumb, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $hex = sprintf('#%02x%02x%02x', $r, $g, $b);
                    if (!isset($color_map[$hex])) {
                        $color_map[$hex] = 0;
                    }
                    $color_map[$hex]++;
                }
            }

            // Sort colors by frequency
            arsort($color_map);
            $colors = array_slice(array_keys($color_map), 0, $count);

            // Clean up
            imagedestroy($image);
            imagedestroy($thumb);

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('image_processing_error', $e->getMessage());
        }
    }

    /**
     * Generate a scheme based on a theme or mood
     *
     * @param string $theme Theme or mood name
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_theme_scheme($theme, $options = []) {
        $theme = strtolower($theme);
        $count = isset($options['count']) ? $options['count'] : 5;

        // Predefined base colors for different themes
        $theme_bases = [
            'warm' => '#ff9900',
            'cool' => '#0099cc',
            'natural' => '#7ab55c',
            'elegant' => '#4a4a4a',
            'vibrant' => '#ff3366',
            'pastel' => '#ffb3ba',
            'dark' => '#2c2c2c',
            'light' => '#f5f5f5',
            'autumn' => '#d35400',
            'spring' => '#2ecc71',
            'summer' => '#f1c40f',
            'winter' => '#3498db',
        ];

        if (!isset($theme_bases[$theme])) {
            return new WP_Error('invalid_theme', 'Invalid theme name provided');
        }

        // Generate scheme based on theme's base color
        return $this->generate_scheme($theme_bases[$theme], [
            'type' => isset($options['type']) ? $options['type'] : 'monochromatic',
            'count' => $count,
        ]);
    }
}
