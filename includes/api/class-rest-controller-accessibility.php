<?php
/**
 * REST Controller for Accessibility
 *
 * @package GL_Color_Palette_Generator
 * @subpackage API
 */

namespace GL_Color_Palette_Generator\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST Controller for Accessibility endpoints
 */
class Rest_Controller_Accessibility extends WP_REST_Controller {
    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route('gl-color-palette/v1', '/accessibility/contrast', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'check_contrast'],
                'permission_callback' => '__return_true',
                'args' => [
                    'color1' => [
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => [$this, 'validate_color']
                    ],
                    'color2' => [
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => [$this, 'validate_color']
                    ]
                ]
            ]
        ]);
    }

    /**
     * Check contrast between two colors
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function check_contrast(WP_REST_Request $request) {
        $color1 = $request->get_param('color1');
        $color2 = $request->get_param('color2');

        // Calculate contrast ratio
        $ratio = $this->calculate_contrast_ratio($color1, $color2);

        return rest_ensure_response([
            'ratio' => $ratio,
            'passes_aa' => $ratio >= 4.5,
            'passes_aaa' => $ratio >= 7
        ]);
    }

    /**
     * Validate color parameter
     *
     * @param string $color Color value to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_color($color) {
        return preg_match('/^#[0-9a-f]{6}$/i', $color);
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color in hex format
     * @param string $color2 Second color in hex format
     * @return float Contrast ratio
     */
    private function calculate_contrast_ratio($color1, $color2) {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get relative luminance of a color
     *
     * @param string $color Color in hex format
     * @return float Relative luminance
     */
    private function get_relative_luminance($color) {
        $rgb = $this->hex_to_rgb($color);
        $r = $this->get_luminance_value($rgb['r'] / 255);
        $g = $this->get_luminance_value($rgb['g'] / 255);
        $b = $this->get_luminance_value($rgb['b'] / 255);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Convert hex color to RGB values
     *
     * @param string $hex Color in hex format
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
     * Get luminance value
     *
     * @param float $value RGB value (0-1)
     * @return float Luminance value
     */
    private function get_luminance_value($value) {
        return $value <= 0.03928
            ? $value / 12.92
            : pow(($value + 0.055) / 1.055, 2.4);
    }
}
