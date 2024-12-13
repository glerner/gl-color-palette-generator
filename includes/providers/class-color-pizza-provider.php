<?php
/**
 * Color.pizza API Provider
 *
 * @package    GL_Color_Palette_Generator
 * @subpackage Providers
 * @since      1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use WP_Error;

/**
 * Color.pizza API Provider
 * 
 * Integrates with the color.pizza API to fetch creative color names
 * from their extensive database.
 */
class Color_Pizza_Provider {
    private const API_BASE_URL = 'https://api.color.pizza/v1/';
    private const BATCH_SIZE = 50; // Maximum colors per request

    /**
     * Get color names for a list of hex colors
     *
     * @param array $hex_colors Array of hex color codes without #
     * @param string $list Optional. The name list to use (default: 'bestOf')
     * @return array Array of color names keyed by hex code
     * @throws \Exception If API request fails
     */
    public function get_names($hex_colors, $list = 'bestOf') {
        $results = [];

        // Process colors in batches to avoid URL length limits
        foreach (array_chunk($hex_colors, self::BATCH_SIZE) as $batch) {
            $url = self::API_BASE_URL . '?' . http_build_query([
                'values' => implode(',', $batch),
                'list' => $list
            ]);

            $response = wp_remote_get($url);

            if (is_wp_error($response)) {
                throw new \Exception('Failed to fetch color names: ' . $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid response from color.pizza API');
            }

            if (!isset($data['colors']) || !is_array($data['colors'])) {
                throw new \Exception('Unexpected response format from color.pizza API');
            }

            foreach ($data['colors'] as $color) {
                if (isset($color['hex'], $color['name'])) {
                    $results[strtolower($color['hex'])] = $color['name'];
                }
            }
        }

        return $results;
    }

    /**
     * Get a single color name
     *
     * @param string $hex_color Hex color code without #
     * @param string $list Optional. The name list to use
     * @return string|null Color name or null if not found
     * @throws \Exception If API request fails
     */
    public function get_name($hex_color, $list = 'bestOf') {
        $names = $this->get_names([$hex_color], $list);
        return $names[strtolower($hex_color)] ?? null;
    }
}
