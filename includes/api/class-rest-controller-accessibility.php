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
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Validation\Color_Validation;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * REST Controller for Accessibility endpoints
 */
class Rest_Controller_Accessibility extends WP_REST_Controller {
    /** @var Color_Utility */
    private Color_Utility $color_utility;

    /** @var Color_Validation */
    private Color_Validation $color_validation;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
        $this->color_validation = new Color_Validation();
    }

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
                    'colors' => [
                        'required' => true,
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'validate_callback' => [$this->color_validation, 'validate_hex_color']
                        ]
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
    public function check_contrast(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $colors = $request->get_param('colors');

        // Check if color values are valid
        foreach ($colors as $color) {
            if ($color === null || !$this->color_utility->is_valid_hex_color($color)) {
                return new WP_Error(
                    'invalid_color',
                    sprintf(__('Invalid color value: %s', 'gl-color-palette-generator'), $color)
                );
            }
        }

        // Get accessibility analysis
        $accessibility_results = [];
        foreach ($colors as $color1) {
            foreach ($colors as $color2) {
                if ($color1 === $color2) {
                    continue;
                }

                $contrast = $this->color_utility->get_contrast_ratio($color1, $color2);
                $accessibility_results[] = [
                    'color1' => $color1,
                    'color2' => $color2,
                    'contrast_ratio' => $contrast,
                    'meets_wcag_aa' => $contrast >= Color_Constants::WCAG_CONTRAST_AA,
                    'meets_wcag_aaa' => $contrast >= Color_Constants::WCAG_CONTRAST_AAA
                ];
            }
        }

        return rest_ensure_response($accessibility_results);
    }
}
