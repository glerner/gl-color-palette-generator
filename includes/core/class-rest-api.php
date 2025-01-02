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

use GL_Color_Palette_Generator\Interfaces\Color_Palette_Storage_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Generator_Interface;
use GL_Color_Palette_Generator\Models\Color_Palette;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Validation\Color_Validation;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API class
 */
class REST_API {
    /** @var string */
    const API_NAMESPACE = 'gl-cpg/v1';

    /** @var Color_Utility */
    private Color_Utility $color_utility;

    /** @var Color_Validation */
    private Color_Validation $color_validation;

    /** @var Color_Palette_Generator_Interface */
    private Color_Palette_Generator_Interface $generator;

    /** @var Color_Palette_Storage_Interface */
    private Color_Palette_Storage_Interface $storage;

    /**
     * Constructor
     *
     * @param Color_Utility $color_utility Color utility instance
     * @param Color_Validation $color_validation Color validation instance
     * @param Color_Palette_Generator_Interface $generator Palette generator instance
     * @param Color_Palette_Storage_Interface $storage Palette storage instance
     */
    public function __construct(
        Color_Utility $color_utility,
        Color_Validation $color_validation,
        Color_Palette_Generator_Interface $generator,
        Color_Palette_Storage_Interface $storage
    ) {
        $this->color_utility = $color_utility;
        $this->color_validation = $color_validation;
        $this->generator = $generator;
        $this->storage = $storage;
    }

    /**
     * Initialize the REST API
     */
    public function init(): void {
        $this->register_routes();
    }

    /**
     * Register REST API routes
     */
    private function register_routes(): void {
        register_rest_route(
            self::API_NAMESPACE,
            '/generate',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'generate_new_palette'],
                'permission_callback' => [$this, 'check_generate_permission'],
                'args' => [
                    'base_color' => [
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => [$this->color_validation, 'is_valid_hex_color']
                    ],
                    'scheme' => [
                        'required' => false,
                        'type' => 'string',
                        'default' => 'analogous'
                    ],
                    'count' => [
                        'required' => false,
                        'type' => 'integer',
                        'default' => 5,
                        'minimum' => 2,
                        'maximum' => 10
                    ]
                ]
            ]
        );

        register_rest_route(
            self::API_NAMESPACE,
            '/palettes',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_all_palettes'],
                    'permission_callback' => [$this, 'check_read_permission'],
                    'args' => [
                        'per_page' => [
                            'required' => false,
                            'type' => 'integer',
                            'default' => 10,
                            'minimum' => 1,
                            'maximum' => 100
                        ],
                        'page' => [
                            'required' => false,
                            'type' => 'integer',
                            'default' => 1,
                            'minimum' => 1
                        ],
                        'orderby' => [
                            'required' => false,
                            'type' => 'string',
                            'default' => 'id',
                            'enum' => ['id', 'name', 'created']
                        ],
                        'order' => [
                            'required' => false,
                            'type' => 'string',
                            'default' => 'asc',
                            'enum' => ['asc', 'desc']
                        ]
                    ]
                ],
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'save_palette'],
                    'permission_callback' => [$this, 'check_create_permission'],
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
                                'validate_callback' => [$this->color_validation, 'is_valid_hex_color'],
                            ],
                        ],
                    ],
                ]
            ]
        );

        register_rest_route(
            self::API_NAMESPACE,
            '/palettes/(?P<id>\d+)',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_palette'],
                    'permission_callback' => [$this, 'check_read_permission']
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update_palette'],
                    'permission_callback' => [$this, 'check_update_permission'],
                    'args' => [
                        'name' => [
                            'required' => false,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'colors' => [
                            'required' => false,
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'validate_callback' => [$this->color_validation, 'is_valid_hex_color'],
                            ],
                        ],
                    ],
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'delete_palette'],
                    'permission_callback' => [$this, 'check_delete_permission']
                ]
            ]
        );
    }

    /**
     * Generate a new palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function generate_new_palette(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $options = $request->get_params();

        // Generate palette
        $palette = $this->generator->generate_palette($options);
        if ($palette instanceof WP_Error) {
            return $palette;
        }

        // Store palette
        $result = $this->storage->store_palette($palette);
        if ($result instanceof WP_Error) {
            return $result;
        }

        return rest_ensure_response($palette);
    }

    /**
     * Get all palettes
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function get_all_palettes(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $per_page = $request->get_param('per_page');
        $page = $request->get_param('page');

        $args = [
            'per_page' => $per_page ? (int) $per_page : 10,
            'page' => $page ? (int) $page : 1,
        ];

        /** @var Color_Palette[]|WP_Error $result */
        $result = $this->storage->list_palettes($args);

        if ($result instanceof WP_Error) {
            return $result;
        }

        if ($result === []) {
            return new WP_Error(
                'no_palettes',
                __('No palettes found.', 'gl-color-palette-generator'),
                ['status' => 404]
            );
        }

        return rest_ensure_response($result);
    }

    /**
     * Get palette by ID
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function get_palette(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = $request->get_param('id');
        if (!$id || !is_numeric($id) || (int) $id <= 0) {
            return new WP_Error(
                'invalid_id',
                __('Invalid palette ID.', 'gl-color-palette-generator'),
                ['status' => 400]
            );
        }

        /** @var Color_Palette|null|WP_Error $result */
        $result = $this->storage->get_palette((int) $id);

        if ($result instanceof WP_Error) {
            return $result;
        }

        if ($result === null) {
            return new WP_Error(
                'palette_not_found',
                __('Palette not found.', 'gl-color-palette-generator'),
                ['status' => 404]
            );
        }

        return rest_ensure_response($result);
    }

    /**
     * Save a new palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function save_palette(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $data = $request->get_json_params();
        if (!is_array($data)) {
            return new WP_Error(
                'invalid_data',
                __('Invalid request data.', 'gl-color-palette-generator'),
                ['status' => 400]
            );
        }

        // Validate colors using color utility
        if (isset($data['colors']) && is_array($data['colors'])) {
            foreach ($data['colors'] as $color) {
                if (!$this->color_utility->is_valid_hex_color($color)) {
                    return new WP_Error(
                        'invalid_color',
                        sprintf(__('Invalid color format: %s', 'gl-color-palette-generator'), $color),
                        ['status' => 400]
                    );
                }
            }
        }

        try {
            // Convert array data to Color_Palette model
            $palette = new Color_Palette(
                (string) time(), // Generate ID from timestamp
                $data['colors'],
                $data['metadata'] ?? [],
                time()
            );

            /** @var bool|WP_Error $result */
            $result = $this->storage->store_palette($palette);
            if ($result instanceof WP_Error) {
                return $result;
            }
            if (!$result) {
                return new WP_Error(
                    'save_failed',
                    __('Failed to save palette.', 'gl-color-palette-generator'),
                    ['status' => 500]
                );
            }
            return rest_ensure_response($palette);
        } catch (\Exception $e) {
            return new WP_Error(
                'save_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Update a palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function update_palette(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = $request->get_param('id');
        if (!$id || !is_numeric($id) || (int) $id <= 0) {
            return new WP_Error(
                'invalid_id',
                __('Invalid palette ID.', 'gl-color-palette-generator'),
                ['status' => 400]
            );
        }

        $data = $request->get_json_params();
        if (!is_array($data)) {
            return new WP_Error(
                'invalid_data',
                __('Invalid request data.', 'gl-color-palette-generator'),
                ['status' => 400]
            );
        }

        /** @var Color_Palette|null|WP_Error $result */
        $result = $this->storage->get_palette((int) $id);
        if ($result instanceof WP_Error) {
            return $result;
        }
        if ($result === null) {
            return new WP_Error(
                'not_found',
                __('Palette not found.', 'gl-color-palette-generator'),
                ['status' => 404]
            );
        }

        // Create updated palette
        try {
            $updated = new Color_Palette(
                (string) $id,
                $data['colors'] ?? $result->get_colors(),
                $data['metadata'] ?? $result->get_metadata(),
                $result->get_created_at()
            );
        } catch (\Exception $e) {
            return new WP_Error(
                'invalid_palette',
                $e->getMessage(),
                ['status' => 400]
            );
        }

        /** @var bool|WP_Error $update_result */
        $update_result = $this->storage->store_palette($updated);
        if ($update_result instanceof WP_Error) {
            return $update_result;
        }
        if (!$update_result) {
            return new WP_Error(
                'update_failed',
                __('Failed to update palette.', 'gl-color-palette-generator'),
                ['status' => 500]
            );
        }

        return rest_ensure_response($updated);
    }

    /**
     * Delete a palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function delete_palette(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = $request->get_param('id');

        try {
            $success = $this->storage->delete_palette($id);
            if (!$success) {
                return new WP_Error(
                    'palette_not_found',
                    'Palette not found',
                    ['status' => 404]
                );
            }
            return rest_ensure_response(['success' => true]);
        } catch (\Exception $e) {
            return new WP_Error(
                'palette_delete_failed',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Check if user has permission to generate palettes
     *
     * @return bool True if user has permission
     */
    public function check_generate_permission(): bool {
        return current_user_can('edit_posts');
    }

    /**
     * Check if user has permission to read palettes
     *
     * @return bool True if user has permission
     */
    public function check_read_permission(): bool {
        return current_user_can('read');
    }

    /**
     * Check if user has permission to create palettes
     *
     * @return bool True if user has permission
     */
    public function check_create_permission(): bool {
        return current_user_can('edit_posts');
    }

    /**
     * Check if user has permission to update palettes
     *
     * @return bool True if user has permission
     */
    public function check_update_permission(): bool {
        return current_user_can('edit_posts');
    }

    /**
     * Check if user has permission to delete palettes
     *
     * @return bool True if user has permission
     */
    public function check_delete_permission(): bool {
        return current_user_can('delete_posts');
    }
}
