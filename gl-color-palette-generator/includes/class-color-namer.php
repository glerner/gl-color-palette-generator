<?php

class ColorNamer {
    private $settings;
    private $cache;
    private $error_handler;
    private $local_database;
    private $openai_client;

    // Basic color name mapping for local processing
    private $basic_colors = [
        'red' => ['#FF0000', '#FF4444', '#CC0000'],
        'blue' => ['#0000FF', '#4444FF', '#0000CC'],
        'green' => ['#00FF00', '#44FF44', '#00CC00'],
        'yellow' => ['#FFFF00', '#FFFF44', '#CCCC00'],
        'purple' => ['#800080', '#FF00FF', '#CC00CC'],
        'orange' => ['#FFA500', '#FF8C00', '#FF7F50'],
        'brown' => ['#8B4513', '#A0522D', '#D2691E'],
        'pink' => ['#FFC0CB', '#FFB6C1', '#FF69B4'],
        'gray' => ['#808080', '#A9A9A9', '#D3D3D3'],
        'black' => ['#000000', '#222222', '#333333'],
        'white' => ['#FFFFFF', '#FAFAFA', '#F5F5F5']
    ];

    public function __construct($settings = null) {
        $this->settings = $settings ?? new SettingsManager();
        $this->cache = new ColorCache();
        $this->error_handler = new ErrorHandler();
        $this->local_database = $this->load_local_database();

        if ($this->settings->get_setting('naming_service') === 'openai') {
            $this->openai_client = new OpenAIClient(
                $this->settings->get_setting('api_key')
            );
        }
    }

    /**
     * Get color name based on hex value
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
     */
    private function generate_color_prompt($hex, $context) {
        $rgb = $this->hex_to_rgb($hex);
        $hsl = $this->rgb_to_hsl($rgb);

        return sprintf(
            "Generate a creative and memorable name for a color with these properties:\n" .
            "HEX: %s\n" .
            "RGB: %d, %d, %d\n" .
            "HSL: %d°, %d%%, %d%%\n" .
            "Context: %s\n" .
            "Requirements:\n" .
            "- Name should be 1-3 words\n" .
            "- Should be evocative and descriptive\n" .
            "- Appropriate for the context\n" .
            "- Easy to remember and pronounce",
            $hex,
            $rgb['r'], $rgb['g'], $rgb['b'],
            $hsl['h'], $hsl['s'], $hsl['l'],
            $context
        );
    }

    /**
     * Get color name from local database
     */
    private function get_local_color_name($hex, $context) {
        $rgb = $this->hex_to_rgb($hex);
        $closest_color = null;
        $min_distance = PHP_FLOAT_MAX;

        foreach ($this->local_database as $name => $data) {
            $distance = $this->calculate_color_distance(
                $rgb,
                $this->hex_to_rgb($data['hex'])
            );

            if ($distance < $min_distance) {
                $min_distance = $distance;
                $closest_color = $name;
            }
        }

        // If distance is too large, fall back to basic naming
        if ($min_distance > 50) {
            return $this->get_basic_color_name($hex);
        }

        return $closest_color;
    }

    /**
     * Get basic color name
     */
    private function get_basic_color_name($hex) {
        $rgb = $this->hex_to_rgb($hex);
        $closest_basic = null;
        $min_distance = PHP_FLOAT_MAX;

        foreach ($this->basic_colors as $name => $variations) {
            foreach ($variations as $variation) {
                $distance = $this->calculate_color_distance(
                    $rgb,
                    $this->hex_to_rgb($variation)
                );

                if ($distance < $min_distance) {
                    $min_distance = $distance;
                    $closest_basic = $name;
                }
            }
        }

        return $closest_basic;
    }

    /**
     * Calculate color distance (using CIEDE2000)
     */
    private function calculate_color_distance($rgb1, $rgb2) {
        // Convert to Lab color space for more accurate comparison
        $lab1 = $this->rgb_to_lab($rgb1);
        $lab2 = $this->rgb_to_lab($rgb2);

        // Calculate CIEDE2000 color difference
        return $this->calculate_ciede2000($lab1, $lab2);
    }

    /**
     * Load local color database
     */
    private function load_local_database() {
        $json_file = plugin_dir_path(__FILE__) . 'data/color-names.json';

        if (!file_exists($json_file)) {
            throw new Exception("Color database not found");
        }

        $data = json_decode(file_get_contents($json_file), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid color database format");
        }

        return $data;
    }

    /**
     * Color space conversion utilities
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    private function rgb_to_hsl($rgb) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch($max) {
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
            'h' => round($h * 360),
            's' => round($s * 100),
            'l' => round($l * 100)
        ];
    }

    private function rgb_to_lab($rgb) {
        // RGB to XYZ
        $xyz = $this->rgb_to_xyz($rgb);

        // XYZ to Lab
        return $this->xyz_to_lab($xyz);
    }

    private function calculate_ciede2000($lab1, $lab2) {
        // Implementation of CIEDE2000 color difference formula
        // This is a complex calculation that provides very accurate
        // color difference measurements
        // ... (implementation details omitted for brevity)

        return $delta_e;
    }
} 
