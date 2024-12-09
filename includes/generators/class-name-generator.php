<?php
namespace GL_Color_Palette_Generator\Generators;

class Name_Generator {
    private $openai_api_key;
    private $cache_duration = 2592000; // 30 days in seconds

    public function __construct($openai_api_key = null) {
        $this->openai_api_key = $openai_api_key;
    }

    public function generate_name($colors, $options = []) {
        $theme = $options['theme'] ?? '';
        $mood = $options['mood'] ?? '';

        if (empty($colors)) {
            return '';
        }

        $base_name = $this->get_base_name($colors[0], $theme, $mood);
        if (count($colors) === 1) {
            return $base_name;
        }

        return $this->combine_color_names($colors, $base_name, $theme, $mood);
    }

    public function validate_name($name) {
        if (empty($name)) {
            return false;
        }

        $max_length = 50;
        if (strlen($name) > $max_length) {
            return false;
        }

        return true;
    }

    public function sanitize_name($name) {
        $name = strip_tags($name);
        $name = wp_strip_all_tags($name);
        $name = sanitize_text_field($name);
        return $name;
    }

    public function generate_description($colors, $name) {
        if (empty($colors) || empty($name)) {
            return '';
        }

        $description = sprintf(
            'A harmonious palette named "%s" featuring %d colors. ',
            $name,
            count($colors)
        );

        $description .= $this->analyze_color_theme($colors);
        return $description;
    }

    public function validate_description($description) {
        if (empty($description)) {
            return false;
        }

        $max_length = 500;
        if (strlen($description) > $max_length) {
            return false;
        }

        return true;
    }

    public function get_name_suggestions($colors) {
        if (empty($colors)) {
            return [];
        }

        $suggestions = [];
        $themes = ['nature', 'ocean', 'sunset', 'urban', 'forest'];
        $moods = ['calm', 'energetic', 'professional', 'playful', 'elegant'];

        foreach ($themes as $theme) {
            $suggestions[] = $this->generate_name($colors, ['theme' => $theme]);
        }

        foreach ($moods as $mood) {
            $suggestions[] = $this->generate_name($colors, ['mood' => $mood]);
        }

        return array_unique(array_filter($suggestions));
    }

    public function analyze_color_theme($colors) {
        if (empty($colors)) {
            return '';
        }

        $themes = [];
        foreach ($colors as $color) {
            $rgb = $this->hex_to_rgb($color);
            $hsv = $this->rgb_to_hsv($rgb);

            if ($hsv['s'] < 0.2) {
                $themes[] = 'neutral';
            } elseif ($hsv['h'] >= 0 && $hsv['h'] < 30) {
                $themes[] = 'warm';
            } elseif ($hsv['h'] >= 180 && $hsv['h'] < 240) {
                $themes[] = 'cool';
            }
        }

        $themes = array_unique($themes);
        return implode(', ', $themes);
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

    private function get_base_name($color, $theme, $mood) {
        $cached_name = get_transient('color_name_' . $color);
        if ($cached_name !== false) {
            return $cached_name;
        }

        if ($this->openai_api_key) {
            $ai_name = $this->get_ai_name($color, $theme, $mood);
            if ($ai_name) {
                set_transient('color_name_' . $color, $ai_name, $this->cache_duration);
                return $ai_name;
            }
        }

        return $this->get_fallback_name($color);
    }

    private function get_ai_name($color, $theme, $mood) {
        // Implementation of AI name generation
        return false;
    }

    private function get_fallback_name($color) {
        $rgb = $this->hex_to_rgb($color);
        $hsv = $this->rgb_to_hsv($rgb);

        $color_ranges = [
            ['min' => 0, 'max' => 30, 'name' => 'Red'],
            ['min' => 30, 'max' => 60, 'name' => 'Orange'],
            ['min' => 60, 'max' => 120, 'name' => 'Yellow'],
            ['min' => 120, 'max' => 180, 'name' => 'Green'],
            ['min' => 180, 'max' => 240, 'name' => 'Blue'],
            ['min' => 240, 'max' => 300, 'name' => 'Purple'],
            ['min' => 300, 'max' => 360, 'name' => 'Pink']
        ];

        $hue = $hsv['h'];
        foreach ($color_ranges as $range) {
            if ($hue >= $range['min'] && $hue < $range['max']) {
                return $range['name'];
            }
        }

        return 'Gray'; // Fallback for achromatic colors
    }

    private function combine_color_names($colors, $base_name, $theme, $mood) {
        if ($theme) {
            return ucwords($theme . ' ' . $base_name);
        }
        if ($mood) {
            return ucwords($mood . ' ' . $base_name);
        }
        return $base_name;
    }
}
