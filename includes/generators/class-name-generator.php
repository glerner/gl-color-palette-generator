<?php
namespace GLColorPalette;

class ColorNameGenerator {
    private $naming_preference;
    private $openai_api_key;
    private $cache_duration = 2592000; // 30 days in seconds

    /**
     * Constructor for ColorNameGenerator.
     *
     * @param string $preference The naming preference ('both', 'creative', 'descriptive').
     * @param string|null $openai_api_key The API key for OpenAI, if available.
     */
    public function __construct($preference = 'both', $openai_api_key = null) {
        $this->naming_preference = $preference;
        $this->openai_api_key = $openai_api_key;
    }

    /**
     * Generate names for a color and its variations.
     *
     * @param string $hex The base color in hex format.
     * @param array $variations An array of color variations.
     * @return array An array of generated names for each variation.
     */
    public function generate_names($hex, $variations) {
        $base_name = $this->get_color_name($hex);
        $names = [];

        foreach ($variations as $variation => $color) {
            if ($variation === 'original') {
                $names[$variation] = $base_name;
            } else {
                $variation_name = $this->get_color_name($color);
                $names[$variation] = $this->format_name($variation_name, $variation);
            }
        }

        return $names;
    }

    /**
     * Get color name using various APIs.
     *
     * @param string $hex The color in hex format.
     * @return string The determined color name.
     */
    private function get_color_name($hex) {
        $hex = ltrim($hex, '#');

        // First check the cache
        $cached_name = get_transient('color_name_' . $hex);
        if ($cached_name !== false) {
            return $cached_name;
        }

        // Try ColorNames.org API first
        $colornames_result = $this->get_colornames_api($hex);
        if ($colornames_result) {
            $this->cache_color_name($hex, $colornames_result);
            return $colornames_result;
        }

        // If ColorNames.org fails and OpenAI is configured, try that
        if ($this->openai_api_key) {
            $ai_result = $this->get_ai_color_name($hex);
            if ($ai_result) {
                $this->cache_color_name($hex, $ai_result);
                return $ai_result;
            }
        }

        // Fallback to basic color name
        return $this->get_basic_color_name($hex);
    }

    /**
     * Query ColorNames.org API
     */
    private function get_colornames_api($hex) {
        $url = "https://colornames.org/search/json/?hex={$hex}";

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log('ColorNames API Error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['name'])) {
            return $body['name'];
        }

        return false;
    }

    /**
     * Get color name from OpenAI
     */
    private function get_ai_color_name($hex) {
        if (!$this->openai_api_key) {
            return false;
        }

        $rgb = $this->hex_to_rgb($hex);

        $prompt = sprintf(
            "Generate a creative, memorable color name for RGB(%d,%d,%d). " .
            "The name should be brief (1-3 words) and evocative. " .
            "Return only the color name, nothing else.",
            $rgb[0], $rgb[1], $rgb[2]
        );

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openai_api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 50,
                'temperature' => 0.7
            ])
        ]);

        if (is_wp_error($response)) {
            error_log('OpenAI API Error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['choices'][0]['message']['content'])) {
            return trim($body['choices'][0]['message']['content']);
        }

        return false;
    }

    /**
     * Cache the color name
     */
    private function cache_color_name($hex, $name) {
        set_transient('color_name_' . $hex, $name, $this->cache_duration);
    }

    /**
     * Get basic color name as fallback
     */
    private function get_basic_color_name($hex) {
        $rgb = $this->hex_to_rgb($hex);

        // Basic color detection logic
        $r = $rgb[0];
        $g = $rgb[1];
        $b = $rgb[2];

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        if ($max == $min) {
            if ($max == 0) return 'Black';
            if ($max == 255) return 'White';
            return 'Gray';
        }

        if ($r > $g && $r > $b) return 'Red';
        if ($g > $r && $g > $b) return 'Green';
        if ($b > $r && $b > $g) return 'Blue';

        return 'Custom Color';
    }

    /**
     * Generate creative color names
     */
    public function generate_creative_names($colors) {
        $names = [];
        foreach ($colors as $hex) {
            $names[$hex] = [
                'creative_name' => $this->generate_single_name($hex),
                'descriptive_name' => $this->generate_descriptive_name($hex),
                'marketing_name' => $this->generate_marketing_name($hex),
                'alternatives' => $this->generate_alternative_names($hex)
            ];
        }

        return [
            'names' => $names,
            'metadata' => $this->generate_naming_metadata($names),
            'suggestions' => $this->generate_name_suggestions($names)
        ];
    }

    /**
     * Validate color names
     */
    public function validate_color_names($names) {
        $validator = new ColorNameValidator();
        $results = [];

        foreach ($names as $hex => $name) {
            $results[$hex] = [
                'is_valid' => $validator->validate_name($name),
                'uniqueness_score' => $validator->check_uniqueness($name),
                'marketing_effectiveness' => $validator->assess_marketing_value($name),
                'localization_status' => $validator->check_localization($name)
            ];
        }

        return $results;
    }

    /**
     * Generate localized names
     */
    public function generate_localized_names($colors, $languages) {
        $localizer = new ColorLocalizer();
        $localized_names = [];

        foreach ($colors as $hex) {
            $localized_names[$hex] = [];
            foreach ($languages as $lang) {
                $localized_names[$hex][$lang] = [
                    'name' => $localizer->get_localized_name($hex, $lang),
                    'description' => $localizer->get_localized_description($hex, $lang),
                    'cultural_context' => $localizer->get_cultural_context($hex, $lang)
                ];
            }
        }

        return $localized_names;
    }
}
