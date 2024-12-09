<?php
/**
 * AI Color Service
 *
 * Handles AI-powered color naming and description generation using OpenAI's API.
 *
 * @package GLColorPalette
 * @subpackage Services
 * @since 1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\AIColorServiceInterface;
use GLColorPalette\Validation\ColorNameValidator;

class AIColorService implements AIColorServiceInterface {
    /** 
     * @var array Service configuration
     */
    private $config = [
        'max_retries' => 3,
        'timeout' => 30,
        'cache_duration' => 86400, // 24 hours
        'rate_limit' => [
            'requests' => 60,
            'period' => 3600 // 1 hour
        ],
        'ai_model' => 'gpt-4', // Default model
        'temperature' => 0.7
    ];

    /** @var ColorNameValidator */
    private $validator;

    /** @var array Rate limiting data */
    private $rate_limit_data = [
        'count' => 0,
        'reset_time' => 0
    ];

    /**
     * Constructor
     *
     * @param array $config Optional. Service configuration to override defaults.
     */
    public function __construct(array $config = []) {
        $this->config = array_merge($this->config, $config);
        $this->validator = new ColorNameValidator();
        $this->init_rate_limiter();
    }

    /**
     * Generate creative color name using AI
     *
     * @param string $hex_color Hex color code (e.g., '#FF5733')
     * @param array  $options   Generation options {
     *     Optional. An array of generation options.
     *     @type string $style   Style of naming (e.g., 'natural', 'creative', 'technical')
     *     @type string $context Usage context (e.g., 'web', 'print', 'fashion')
     *     @type string $model   AI model to use (defaults to config value)
     * }
     * @return array{
     *     name: string,
     *     hex: string,
     *     source: string,
     *     timestamp: int,
     *     metadata: array,
     *     error?: bool,
     *     message?: string,
     *     fallback_name?: string
     * } Generated name and metadata
     * @throws \Exception When rate limit is exceeded or API fails
     */
    public function generate_color_name($hex_color, $options = []) {
        try {
            // Check rate limits
            if (!$this->check_rate_limit()) {
                throw new \Exception('Rate limit exceeded');
            }

            // Check cache first
            $cached = $this->get_cached_name($hex_color);
            if ($cached) {
                return $cached;
            }

            // Generate prompt
            $prompt = $this->build_prompt($hex_color, $options);

            // Get AI response
            $response = $this->get_ai_response($prompt);
            if (empty($response)) {
                throw new \Exception('Failed to get AI response');
            }

            // Process and validate response
            $result = $this->process_response($response, $hex_color);
            if (!$this->validator->validate($result['name'])) {
                throw new \Exception('Generated name failed validation');
            }

            // Update rate limiter
            $this->update_rate_limit();

            // Cache result
            $this->cache_result($hex_color, $result);

            return $result;

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'fallback_name' => $this->get_fallback_name($hex_color)
            ];
        }
    }

    /**
     * Build AI prompt for color name generation
     *
     * @param string $hex_color Hex color code
     * @param array $options Prompt options
     * @return string Generated prompt
     */
    private function build_prompt($hex_color, $options) {
        $base_prompt = "Generate a creative and memorable name for the color {$hex_color}.";
        
        if (!empty($options['style'])) {
            $base_prompt .= " Style: {$options['style']}.";
        }
        
        if (!empty($options['context'])) {
            $base_prompt .= " Context: {$options['context']}.";
        }
        
        $base_prompt .= " Requirements: unique, evocative, no trademark names.";
        
        return $base_prompt;
    }

    /**
     * Get response from AI service
     *
     * @param string $prompt Generated prompt
     * @return string|null AI response
     * @throws \Exception When API request fails or returns invalid response
     */
    private function get_ai_response($prompt) {
        $api_key = defined('GL_COLOR_OPENAI_KEY') ? GL_COLOR_OPENAI_KEY : '';
        if (empty($api_key)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = wp_remote_post('https://api.openai.com/v1/completions', [
            'timeout' => $this->config['timeout'],
            'headers' => [
                'Authorization' => "Bearer {$api_key}",
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => $this->config['ai_model'],
                'prompt' => $prompt,
                'max_tokens' => 50,
                'temperature' => $this->config['temperature']
            ])
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['choices'][0]['text'])) {
            throw new \Exception('Invalid API response format');
        }

        return $body['choices'][0]['text'];
    }

    /**
     * Process AI response into structured result
     *
     * @param string $response AI response text
     * @param string $hex_color Original hex color
     * @return array Processed result
     */
    private function process_response($response, $hex_color) {
        $name = trim(preg_replace('/[^a-zA-Z0-9\s\-]/', '', $response));
        
        return [
            'name' => $name,
            'hex' => $hex_color,
            'source' => 'ai',
            'timestamp' => time(),
            'metadata' => [
                'raw_response' => $response,
                'processed' => true
            ]
        ];
    }

    /**
     * Get fallback color name
     *
     * @param string $hex_color Hex color code
     * @return string Basic color name
     */
    private function get_fallback_name($hex_color) {
        $basic_colors = [
            '#FF0000' => 'Red',
            '#00FF00' => 'Green',
            '#0000FF' => 'Blue',
            '#FFFF00' => 'Yellow',
            '#FF00FF' => 'Magenta',
            '#00FFFF' => 'Cyan',
            '#000000' => 'Black',
            '#FFFFFF' => 'White',
            '#808080' => 'Gray'
        ];

        // Find closest basic color
        $min_distance = PHP_FLOAT_MAX;
        $closest_name = 'Color';

        foreach ($basic_colors as $basic_hex => $name) {
            $distance = $this->calculate_color_distance($hex_color, $basic_hex);
            if ($distance < $min_distance) {
                $min_distance = $distance;
                $closest_name = $name;
            }
        }

        return $closest_name;
    }

    /**
     * Calculate color distance using CIE76 formula
     *
     * This method uses the CIE76 color difference formula which is more
     * perceptually accurate than simple RGB Euclidean distance.
     *
     * @param string $color1 First hex color
     * @param string $color2 Second hex color
     * @return float Color distance
     */
    private function calculate_color_distance($color1, $color2) {
        $lab1 = $this->rgb_to_lab($this->hex_to_rgb($color1));
        $lab2 = $this->rgb_to_lab($this->hex_to_rgb($color2));

        return sqrt(
            pow($lab1['l'] - $lab2['l'], 2) +
            pow($lab1['a'] - $lab2['a'], 2) +
            pow($lab1['b'] - $lab2['b'], 2)
        );
    }

    /**
     * Convert RGB to CIE-L*ab color space
     *
     * @param array $rgb RGB values
     * @return array L*ab values
     */
    private function rgb_to_lab($rgb) {
        // First convert RGB to XYZ
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        // Convert to XYZ
        $x = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
        $y = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
        $z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;

        // Convert XYZ to Lab
        $x = $x / 0.95047;
        $y = $y / 1.00000;
        $z = $z / 1.08883;

        $x = $x > 0.008856 ? pow($x, 1/3) : (7.787 * $x) + 16/116;
        $y = $y > 0.008856 ? pow($y, 1/3) : (7.787 * $y) + 16/116;
        $z = $z > 0.008856 ? pow($z, 1/3) : (7.787 * $z) + 16/116;

        return [
            'l' => (116 * $y) - 16,
            'a' => 500 * ($x - $y),
            'b' => 200 * ($y - $z)
        ];
    }

    /**
     * Convert hex to RGB values
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
     * Initialize rate limiter
     */
    private function init_rate_limiter() {
        $this->rate_limit_data = get_transient('gl_color_ai_rate_limit');
        if (!$this->rate_limit_data) {
            $this->rate_limit_data = [
                'count' => 0,
                'reset_time' => time() + $this->config['rate_limit']['period']
            ];
        }
    }

    /**
     * Check if within rate limits
     *
     * @return bool True if within limits
     */
    private function check_rate_limit() {
        if (time() >= $this->rate_limit_data['reset_time']) {
            $this->rate_limit_data = [
                'count' => 0,
                'reset_time' => time() + $this->config['rate_limit']['period']
            ];
            return true;
        }

        return $this->rate_limit_data['count'] < $this->config['rate_limit']['requests'];
    }

    /**
     * Update rate limit counter
     */
    private function update_rate_limit() {
        $this->rate_limit_data['count']++;
        set_transient(
            'gl_color_ai_rate_limit',
            $this->rate_limit_data,
            $this->config['rate_limit']['period']
        );
    }

    /**
     * Get cached color name
     *
     * @param string $hex_color Hex color code
     * @return array|null Cached result
     */
    private function get_cached_name($hex_color) {
        return get_transient("gl_color_ai_name_{$hex_color}");
    }

    /**
     * Cache color name result
     *
     * @param string $hex_color Hex color code
     * @param array $result Result to cache
     */
    private function cache_result($hex_color, $result) {
        set_transient(
            "gl_color_ai_name_{$hex_color}",
            $result,
            $this->config['cache_duration']
        );
    }
}
