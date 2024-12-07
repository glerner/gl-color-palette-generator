<?php
/**
 * REST API Class
 *
 * Handles all REST API endpoints for the plugin.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API class
 */
class REST_API {
    /**
     * The namespace for our REST API endpoints
     *
     * @var string
     */
    const API_NAMESPACE = 'gl-cpg/v1';

    /**
     * Register REST API routes
     *
     * @return void
     */
    public static function register_routes() {
        // Generate palette endpoint
        register_rest_route(
            self::API_NAMESPACE,
            '/generate',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [self::class, 'generate_palette'],
                'permission_callback' => [self::class, 'check_generate_permission'],
                'args' => [
                    'base_color' => [
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => [self::class, 'validate_color'],
                    ],
                    'palette_type' => [
                        'required' => true,
                        'type' => 'string',
                        'enum' => ['analogous', 'complementary', 'triadic', 'tetradic', 'monochromatic'],
                    ],
                    'num_colors' => [
                        'required' => false,
                        'type' => 'integer',
                        'default' => 5,
                        'minimum' => 2,
                        'maximum' => 10,
                    ],
                ],
            ]
        );

        // Save palette endpoint
        register_rest_route(
            self::API_NAMESPACE,
            '/palettes',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [self::class, 'get_palettes'],
                    'permission_callback' => [self::class, 'check_read_permission'],
                ],
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [self::class, 'save_palette'],
                    'permission_callback' => [self::class, 'check_create_permission'],
                    'args' => [
                        'name' => [
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'colors' => [
                            'required' => true,
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'validate_callback' => [self::class, 'validate_color'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Single palette endpoints
        register_rest_route(
            self::API_NAMESPACE,
            '/palettes/(?P<id>[\d]+)',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [self::class, 'get_palette'],
                    'permission_callback' => [self::class, 'check_read_permission'],
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [self::class, 'update_palette'],
                    'permission_callback' => [self::class, 'check_update_permission'],
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [self::class, 'delete_palette'],
                    'permission_callback' => [self::class, 'check_delete_permission'],
                ],
            ]
        );
    }

    /**
     * Generate a new color palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public static function generate_palette($request) {
        try {
            $generator = new \GL_Color_Palette_Generator\Generators\ML_Color_Engine();
            $palette = $generator->generate_palette(
                $request['base_color'],
                $request['palette_type'],
                $request['num_colors']
            );

            return new WP_REST_Response([
                'success' => true,
                'data' => [
                    'colors' => $palette->get_colors(),
                    'palette_type' => $request['palette_type'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return new WP_Error(
                'palette_generation_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Get all palettes
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public static function get_palettes($request) {
        try {
            $storage = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage();
            $palettes = $storage->get_all_palettes();

            return new WP_REST_Response([
                'success' => true,
                'data' => $palettes,
            ], 200);
        } catch (\Exception $e) {
            return new WP_Error(
                'get_palettes_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Get a single palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public static function get_palette($request) {
        try {
            $storage = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage();
            $palette = $storage->get_palette($request['id']);

            if (!$palette) {
                return new WP_Error(
                    'palette_not_found',
                    __('Palette not found', 'gl-color-palette-generator'),
                    ['status' => 404]
                );
            }

            return new WP_REST_Response([
                'success' => true,
                'data' => $palette,
            ], 200);
        } catch (\Exception $e) {
            return new WP_Error(
                'get_palette_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Save a new palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public static function save_palette($request) {
        try {
            $storage = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage();
            $palette_id = $storage->save_palette(
                $request['name'],
                $request['colors']
            );

            return new WP_REST_Response([
                'success' => true,
                'data' => [
                    'id' => $palette_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return new WP_Error(
                'save_palette_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Update an existing palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public static function update_palette($request) {
        try {
            $storage = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage();
            $success = $storage->update_palette(
                $request['id'],
                $request['name'],
                $request['colors']
            );

            if (!$success) {
                return new WP_Error(
                    'palette_not_found',
                    __('Palette not found', 'gl-color-palette-generator'),
                    ['status' => 404]
                );
            }

            return new WP_REST_Response([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return new WP_Error(
                'update_palette_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Delete a palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_palette($request) {
        try {
            $storage = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Storage();
            $success = $storage->delete_palette($request['id']);

            if (!$success) {
                return new WP_Error(
                    'palette_not_found',
                    __('Palette not found', 'gl-color-palette-generator'),
                    ['status' => 404]
                );
            }

            return new WP_REST_Response([
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return new WP_Error(
                'delete_palette_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Check generate permission
     *
     * @return bool
     */
    public static function check_generate_permission() {
        return current_user_can('edit_posts');
    }

    /**
     * Check read permission
     *
     * @return bool
     */
    public static function check_read_permission() {
        return true; // Public access
    }

    /**
     * Check create permission
     *
     * @return bool
     */
    public static function check_create_permission() {
        return current_user_can('edit_posts');
    }

    /**
     * Check update permission
     *
     * @return bool
     */
    public static function check_update_permission() {
        return current_user_can('edit_posts');
    }

    /**
     * Check delete permission
     *
     * @return bool
     */
    public static function check_delete_permission() {
        return current_user_can('edit_posts');
    }

    /**
     * Validate color format
     *
     * @param string $color Color value to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_color($color) {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1;
    }
}
