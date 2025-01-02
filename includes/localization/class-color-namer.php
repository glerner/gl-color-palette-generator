<?php
namespace GL_Color_Palette_Generator;

/**
 * Color Namer Class
 *
 * Handles color naming using various services including local database,
 * OpenAI API, and color.pizza API.
 */
class ColorNamer {
    private $settings;
    private $cache;
    private $error_handler;
    private $local_database;
    private $openai_client;
    private $color_pizza_client;

    /**
     * Constructor
     *
     * @param SettingsManager|null $settings Settings manager instance
     */
    public function __construct($settings = null) {
        $this->settings = $settings ?? new SettingsManager();
        $this->cache = new ColorCache();
        $this->error_handler = new ErrorHandler();
        $this->local_database = $this->load_local_database();

        if ($this->settings->get_setting('naming_service') === 'openai') {
            $this->openai_client = new OpenAIClient(
                $this->settings->get_setting('api_key')
            );
        } elseif ($this->settings->get_setting('naming_service') === 'color_pizza') {
            $this->color_pizza_client = new \GL_Color_Palette_Generator\Providers\Color_Pizza_Provider();
        }
    }

    /**
     * Get color name based on hex value
     *
     * @param string $hex Hex color code
     * @param string $context Context for color naming
     * @return string Color name
     */
    public function get_color_name($hex, $context = 'general') {
        try {
            // Check cache first
            $cached_name = $this->cache->get_color_name($hex, $context);
            if ($cached_name) {
                return $cached_name;
            }

            // Get name based on selected service
            $name = match($this->settings->get_setting('naming_service')) {
                'openai' => $this->get_ai_color_name($hex, $context),
                'color_pizza' => $this->get_color_pizza_name($hex, $context),
                'custom' => $this->get_custom_api_color_name($hex, $context),
                default => $this->get_local_color_name($hex, $context)
            };

            // Cache the result
            $this->cache->set_color_name($hex, $name, $context);

            return $name;

        } catch (Exception $e) {
            $this->error_handler->handle_error(
                ErrorCodes::COLOR_NAME_NOT_FOUND,
                $e->getMessage(),
                ['hex' => $hex, 'context' => $context]
            );

            // Fallback to basic color naming
            return $this->get_basic_color_name($hex);
        }
    }

    /**
     * Get color name using OpenAI API
     *
     * @param string $hex Hex color code
     * @param string $context Context for color naming
     * @return string Color name
     */
    private function get_ai_color_name($hex, $context) {
        try {
            $prompt = $this->generate_color_prompt($hex, $context);
            $response = $this->openai_client->generate_name($prompt);

            return $this->process_ai_response($response);

        } catch (Exception $e) {
            throw new Exception(
                "AI naming service failed: " . $e->getMessage()
            );
        }
    }

    /**
     * Generate prompt for AI color naming
     *
     * @param string $hex Hex color code
     * @param string $context Context for color naming
     * @return string Prompt for AI color naming
     */
    private function generate_color_prompt($hex, $context) {
        $rgb = $this->hex_to_rgb($hex);
        $hsl = $this->rgb_to_hsl($rgb);

        return sprintf(
            "Generate a creative name for a color with these properties:\n" .
            "Hex: %s\n" .
            "RGB: r=%d, g=%d, b=%d\n" .
            "HSL: h=%.1f, s=%.1f%%, l=%.1f%%\n" .
            "Context: %s\n\n" .
            "The name should be evocative and suitable for web design, " .
            "avoiding references to specific artists or trademarks.",
            $hex,
            $rgb['r'], $rgb['g'], $rgb['b'],
            $hsl['h'], $hsl['s'] * 100, $hsl['l'] * 100,
            $context
        );
    }

    /**
     * Get color name from local database
     *
     * @param string $hex Hex color code
     * @param string $context Context for color naming
     * @return string Color name
     */
    private function get_local_color_name($hex, $context) {
        $rgb = $this->hex_to_rgb($hex);
        $min_distance = PHP_FLOAT_MAX;
        $closest_name = null;

        foreach ($this->local_database as $name => $color) {
            $distance = $this->calculate_color_distance(
                $rgb,
                $this->hex_to_rgb($color)
            );

            if ($distance < $min_distance) {
                $min_distance = $distance;
                $closest_name = $name;
            }
        }

        // If distance is too large, fall back to basic naming
        if ($min_distance > 50) {
            return $this->get_basic_color_name($hex);
        }

        return $closest_name;
    }

    /**
     * Get basic color name
     *
     * @param string $hex Hex color code
     * @return string Color name
     */
    private function get_basic_color_name($hex) {
        $rgb = $this->hex_to_rgb($hex);
        $hsl = $this->rgb_to_hsl($rgb);
        
        // Check for neutrals first
        if ($hsl['s'] < Color_Constants::COLOR_METRICS['saturation']['neutral_threshold']) {
            if ($hsl['l'] < Color_Constants::COLOR_METRICS['lightness']['dark_threshold']) {
                return 'black';
            } elseif ($hsl['l'] > Color_Constants::COLOR_METRICS['lightness']['light_threshold']) {
                return 'white';
            }
            return 'gray';
        }

        // Find closest color name based on hue ranges
        $hue_ranges = Color_Constants::COLOR_WHEEL_CONFIG['hue_ranges'];
        foreach ($hue_ranges as $color_name => $range) {
            if ($hsl['h'] >= $range['start'] && $hsl['h'] <= $range['end']) {
                return $color_name;
            }
        }

        return 'undefined';
    }

    /**
     * Calculate color distance using weighted Euclidean distance
     *
     * This is a simpler alternative to CIEDE2000 that still accounts for human
     * color perception by weighting RGB components based on how humans perceive them.
     *
     * @param array $rgb1 RGB color values
     * @param array $rgb2 RGB color values
     * @return float Color distance
     */
    private function calculate_color_distance($rgb1, $rgb2) {
        // Convert associative arrays to numeric arrays
        $r1 = $rgb1['r']; $g1 = $rgb1['g']; $b1 = $rgb1['b'];
        $r2 = $rgb2['r']; $g2 = $rgb2['g']; $b2 = $rgb2['b'];
        
        // Calculate weighted Euclidean distance
        $r_mean = ($r1 + $r2) / 2;
        $r = $r1 - $r2;
        $g = $g1 - $g2;
        $b = $b1 - $b2;
        
        return sqrt(
            (2 + $r_mean / 256) * $r * $r +
            4 * $g * $g +
            (2 + (255 - $r_mean) / 256) * $b * $b
        );
    }

    /**
     * Convert hex color to RGB values
     *
     * @param string $hex Hex color code
     * @return array RGB values
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Convert RGB to HSL color space
     *
     * @param array $rgb RGB color values
     * @return array HSL color values
     */
    private function rgb_to_hsl($rgb) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            $h = match($max) {
                $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
                $g => ($b - $r) / $d + 2,
                $b => ($r - $g) / $d + 4
            };

            $h = $h / 6;
        }

        return [
            'h' => $h * 360,
            's' => $s,
            'l' => $l
        ];
    }

    /**
     * Get color name using color.pizza API
     *
     * @param string $hex Hex color code
     * @param string $context Context for color naming
     * @return string Color name
     */
    private function get_color_pizza_name($hex, $context) {
        try {
            $hex_without_hash = ltrim($hex, '#');
            $name = $this->color_pizza_client->get_name($hex_without_hash);

            if (!$name) {
                throw new \Exception("No name found for color {$hex}");
            }

            return $name;

        } catch (\Exception $e) {
            throw new \Exception(
                "Color.pizza naming service failed: " . $e->getMessage()
            );
        }
    }

    /**
     * Load local color database
     *
     * @return array Local color database
     */
    private function load_local_database() {
        $json_file = plugin_dir_path(__FILE__) . 'data/color-names.json';
        if (!file_exists($json_file)) {
            return [];
        }

        $json = file_get_contents($json_file);
        if ($json === false) {
            return [];
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
}
