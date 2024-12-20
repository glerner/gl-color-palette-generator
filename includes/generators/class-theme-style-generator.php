<?php
namespace GL_Color_Palette_Generator\Generators;

/**
 * Theme Style Generator Class
 *
 * Generates WordPress theme style variations using different color relationships.
 * This is distinct from tints/shades - it focuses on creating complete theme styles
 * using different color relationships (complementary, analogous, etc).
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class Theme_Style_Generator {
    protected $settings;

    public function __construct() {
        $this->settings = get_option('gl_color_palette_generator_settings', []);
    }

    /**
     * Generate theme style variations
     *
     * @param array  $colors Base colors to generate variations from
     * @param array  $options Generation options
     * @return array Generated theme style variations
     */
    public function generate_theme_styles($colors, $options = []) {
        if (empty($colors)) {
            return [];
        }

        $type = $options['type'] ?? 'all';
        $count = $options['count'] ?? 3;

        $styles = [];
        foreach ($colors as $color) {
            switch ($type) {
                case 'monochromatic':
                    $styles[] = $this->generate_monochromatic_style($color);
                    break;
                case 'analogous':
                    $styles[] = $this->generate_analogous_style($color);
                    break;
                case 'complementary':
                    $styles[] = $this->generate_complementary_style($color);
                    break;
                case 'split_complementary':
                    $styles[] = $this->generate_split_complementary_style($color);
                    break;
                case 'triadic':
                    $styles[] = $this->generate_triadic_style($color);
                    break;
                case 'tetradic':
                    $styles[] = $this->generate_tetradic_style($color);
                    break;
                case 'all':
                default:
                    $styles[] = array_merge(
                        $this->generate_monochromatic_style($color),
                        $this->generate_analogous_style($color),
                        $this->generate_complementary_style($color)
                    );
            }
        }

        return array_slice(array_merge(...$styles), 0, $count);
    }

    /**
     * Generate monochromatic theme style
     *
     * @param string $color Base color
     * @return array Theme style colors
     */
    public function generate_monochromatic_style($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);
        $style_colors = [];

        // Generate lighter and darker variations
        for ($i = -2; $i <= 2; $i++) {
            if ($i === 0) continue;

            $new_hsv = [
                'h' => $hsv['h'],
                's' => $hsv['s'],
                'v' => max(0, min(1, $hsv['v'] + ($i * 0.2)))
            ];

            $style_colors[] = $this->hsv_to_hex($new_hsv);
        }

        return $style_colors;
    }

    /**
     * Generate analogous theme style
     *
     * @param string $color Base color
     * @return array Theme style colors
     */
    public function generate_analogous_style($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);
        $style_colors = [];

        // Generate variations with hue shifts
        $angles = [-30, -15, 15, 30];
        foreach ($angles as $angle) {
            $new_hsv = [
                'h' => fmod(($hsv['h'] + $angle + 360), 360),
                's' => $hsv['s'],
                'v' => $hsv['v']
            ];

            $style_colors[] = $this->hsv_to_hex($new_hsv);
        }

        return $style_colors;
    }

    /**
     * Generate complementary theme style
     *
     * @param string $color Base color
     * @return array Theme style colors
     */
    public function generate_complementary_style($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);
        $style_colors = [];

        // Generate complementary color and variations
        $complementary_h = fmod(($hsv['h'] + 180), 360);

        for ($i = -1; $i <= 1; $i++) {
            $new_hsv = [
                'h' => $complementary_h,
                's' => max(0, min(1, $hsv['s'] + ($i * 0.1))),
                'v' => max(0, min(1, $hsv['v'] + ($i * 0.1)))
            ];

            $style_colors[] = $this->hsv_to_hex($new_hsv);
        }

        return $style_colors;
    }

    /**
     * Generate split complementary theme style
     *
     * @param string $color Base color
     * @return array Theme style colors
     */
    public function generate_split_complementary_style($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);
        $style_colors = [];

        // Generate split complementary colors
        $angles = [150, 210];
        foreach ($angles as $angle) {
            $new_hsv = [
                'h' => fmod(($hsv['h'] + $angle), 360),
                's' => $hsv['s'],
                'v' => $hsv['v']
            ];

            $style_colors[] = $this->hsv_to_hex($new_hsv);
        }

        return $style_colors;
    }

    /**
     * Generate triadic theme style
     *
     * @param string $color Base color
     * @return array Theme style colors
     */
    public function generate_triadic_style($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);
        $style_colors = [];

        // Generate triadic colors
        $angles = [120, 240];
        foreach ($angles as $angle) {
            $new_hsv = [
                'h' => fmod(($hsv['h'] + $angle), 360),
                's' => $hsv['s'],
                'v' => $hsv['v']
            ];

            $style_colors[] = $this->hsv_to_hex($new_hsv);
        }

        return $style_colors;
    }

    /**
     * Generate tetradic theme style
     *
     * @param string $color Base color
     * @return array Theme style colors
     */
    public function generate_tetradic_style($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);
        $style_colors = [];

        // Generate tetradic (double complementary) colors
        $angles = [90, 180, 270];
        foreach ($angles as $angle) {
            $new_hsv = [
                'h' => fmod(($hsv['h'] + $angle), 360),
                's' => $hsv['s'],
                'v' => $hsv['v']
            ];

            $style_colors[] = $this->hsv_to_hex($new_hsv);
        }

        return $style_colors;
    }

    public function adjust_saturation($color, $amount) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);

        $new_hsv = [
            'h' => $hsv['h'],
            's' => max(0, min(1, $hsv['s'] + $amount)),
            'v' => $hsv['v']
        ];

        return $this->hsv_to_hex($new_hsv);
    }

    public function adjust_lightness($color, $amount) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);

        $new_hsv = [
            'h' => $hsv['h'],
            's' => $hsv['s'],
            'v' => max(0, min(1, $hsv['v'] + $amount))
        ];

        return $this->hsv_to_hex($new_hsv);
    }

    public function rotate_hue($color, $degrees) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);

        $new_hsv = [
            'h' => fmod(($hsv['h'] + $degrees + 360), 360),
            's' => $hsv['s'],
            'v' => $hsv['v']
        ];

        return $this->hsv_to_hex($new_hsv);
    }

    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    private function rgb_to_hsv($rgb) {
        $r = $rgb[0] / 255;
        $g = $rgb[1] / 255;
        $b = $rgb[2] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $diff = $max - $min;

        $h = 0;
        $s = ($max == 0) ? 0 : ($diff / $max);
        $v = $max;

        if ($diff != 0) {
            if ($max == $r) {
                $h = 60 * fmod(($g - $b) / $diff, 6);
            } elseif ($max == $g) {
                $h = 60 * (($b - $r) / $diff + 2);
            } elseif ($max == $b) {
                $h = 60 * (($r - $g) / $diff + 4);
            }
        }

        if ($h < 0) {
            $h += 360;
        }

        return [
            'h' => $h,
            's' => $s,
            'v' => $v
        ];
    }

    private function hsv_to_hex($hsv) {
        $h = $hsv['h'];
        $s = $hsv['s'];
        $v = $hsv['v'];

        $c = $v * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $v - $c;

        if ($h >= 0 && $h < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($h >= 60 && $h < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($h >= 120 && $h < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($h >= 180 && $h < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($h >= 240 && $h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }

        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
