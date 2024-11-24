<?php
/**
 * REST API Controller Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\API;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Exporter;
use GL_Color_Palette_Generator\Color_Management\Accessibility_Checker;

/**
 * Class Rest_Controller
 */
class Rest_Controller {
    /**
     * API namespace
     *
     * @var string
     */
    private $namespace = 'gl-cpg/v1';

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/generate',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'generate_palette'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'prompt' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/palettes',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_palettes'],
                    'permission_callback' => [$this, 'check_permission'],
                ],
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'save_palette'],
                    'permission_callback' => [$this, 'check_permission'],
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
                                'pattern' => '/^#[0-9a-f]{6}$/i',
                            ],
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/palettes/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_palette'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'type' => 'integer',
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/export',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'export_palettes'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'format' => [
                        'required' => true,
                        'type' => 'string',
                        'enum' => ['json', 'csv'],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/import',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'import_palettes'],
                'permission_callback' => [$this, 'check_permission'],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/check-contrast',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'check_contrast'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'text_color' => [
                        'required' => true,
                        'type' => 'string',
                        'pattern' => '^#[0-9a-fA-F]{6}$',
                    ],
                    'bg_color' => [
                        'required' => true,
                        'type' => 'string',
                        'pattern' => '^#[0-9a-fA-F]{6}$',
                    ],
                ],
            ]
        );
    }

    /**
     * Check API request permissions
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    public function check_permission($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                __('Sorry, you are not allowed to do that.', 'gl-color-palette-generator'),
                ['status' => 401]
            );
        }
        return true;
    }

    /**
     * Generate color palette
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function generate_palette($request) {
        try {
            $generator = new Color_Palette_Generator();
            $palette = $generator->generate_from_prompt($request['prompt']);

            return new WP_REST_Response([
                'palette' => $palette,
                'message' => __('Palette generated successfully.', 'gl-color-palette-generator'),
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
     * Get saved palettes
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_palettes($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        $palettes = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC",
            ARRAY_A
        );

        if ($palettes === null) {
            return new WP_Error(
                'database_error',
                __('Error retrieving palettes.', 'gl-color-palette-generator'),
                ['status' => 500]
            );
        }

        foreach ($palettes as &$palette) {
            $palette['colors'] = json_decode($palette['colors'], true);
        }

        return new WP_REST_Response($palettes, 200);
    }

    /**
     * Save color palette
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function save_palette($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        $result = $wpdb->insert(
            $table_name,
            [
                'name' => $request['name'],
                'colors' => wp_json_encode($request['colors']),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            return new WP_Error(
                'database_error',
                __('Error saving palette.', 'gl-color-palette-generator'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'id' => $wpdb->insert_id,
            'message' => __('Palette saved successfully.', 'gl-color-palette-generator'),
        ], 201);
    }

    /**
     * Delete color palette
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function delete_palette($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        $result = $wpdb->delete(
            $table_name,
            ['id' => $request['id']],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error(
                'database_error',
                __('Error deleting palette.', 'gl-color-palette-generator'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response([
            'message' => __('Palette deleted successfully.', 'gl-color-palette-generator'),
        ], 200);
    }

    /**
     * Export palettes
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function export_palettes($request) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'gl_color_palettes';

            $palettes = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC",
                ARRAY_A
            );

            if ($palettes === false) {
                return new WP_Error(
                    'database_error',
                    __('Error retrieving palettes.', 'gl-color-palette-generator'),
                    ['status' => 500]
                );
            }

            $exporter = new Color_Palette_Exporter();
            $format = $request->get_param('format');

            if ($format === 'csv') {
                $content = $exporter->export_to_csv($palettes);
                $mime_type = 'text/csv';
                $filename = 'color-palettes.csv';
            } else {
                $content = $exporter->export_to_json($palettes);
                $mime_type = 'application/json';
                $filename = 'color-palettes.json';
            }

            $response = new WP_REST_Response([
                'content' => $content,
                'filename' => $filename,
                'mime_type' => $mime_type
            ]);

            $response->header('Content-Type', $mime_type);
            $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

            return $response;

        } catch (\Exception $e) {
            return new WP_Error(
                'export_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Import palettes
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function import_palettes($request) {
        try {
            $file = $request->get_file_params()['file'];
            if (!$file || $file['error']) {
                return new WP_Error(
                    'upload_error',
                    __('Error uploading file.', 'gl-color-palette-generator'),
                    ['status' => 400]
                );
            }

            $content = file_get_contents($file['tmp_name']);
            $exporter = new Color_Palette_Exporter();

            // Determine format from file extension
            $format = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($format === 'csv') {
                $palettes = $exporter->import_from_csv($content);
            } else {
                $palettes = $exporter->import_from_json($content);
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'gl_color_palettes';
            $imported = 0;

            foreach ($palettes as $palette) {
                $result = $wpdb->insert(
                    $table_name,
                    [
                        'name' => $palette['name'],
                        'colors' => wp_json_encode($palette['colors']),
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%s', '%s', '%s', '%s']
                );

                if ($result) {
                    $imported++;
                }
            }

            return new WP_REST_Response([
                'message' => sprintf(
                    __('Successfully imported %d palettes.', 'gl-color-palette-generator'),
                    $imported
                ),
                'imported_count' => $imported
            ], 200);

        } catch (\Exception $e) {
            return new WP_Error(
                'import_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Check color contrast
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object.
     */
    public function check_contrast($request) {
        try {
            $text_color = $request->get_param('text_color');
            $bg_color = $request->get_param('bg_color');

            if (!$text_color || !$bg_color) {
                return new WP_Error(
                    'missing_colors',
                    __('Both text and background colors are required.', 'gl-color-palette-generator'),
                    ['status' => 400]
                );
            }

            $checker = new Accessibility_Checker();
            $results = $checker->check_combination($text_color, $bg_color);

            return new WP_REST_Response($results, 200);

        } catch (\Exception $e) {
            return new WP_Error(
                'contrast_check_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }
} 
