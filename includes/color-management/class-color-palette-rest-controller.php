<?php
/**
 * Color Palette REST Controller
 *
 * Handles REST API endpoints for palette generation, analysis, optimization,
 * and export functionality. Provides a RESTful interface for the plugin's
 * core features.
 *
 * @package GLColorPalette
 * @since   1.0.0
 *
 * @example
 * // Generate a new palette
 * POST /wp-json/gl-color-palette/v1/palettes
 * {
 *     "theme": "modern",
 *     "count": 5
 * }
 *
 * // Analyze a palette
 * GET /wp-json/gl-color-palette/v1/palettes/{id}/analyze
 */

namespace GLColorPalette;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Color_Palette_REST_Controller
 *
 * @since 1.0.0
 */
class Color_Palette_REST_Controller extends WP_REST_Controller {
    /**
     * API namespace
     * @var string
     */
    protected string $namespace = 'gl-color-palette/v1';

    /**
     * Base resource path
     * @var string
     */
    protected string $rest_base = 'palettes';

    /**
     * Generator instance
     * @var Color_Palette_Generator
     */
    private Color_Palette_Generator $generator;

    /**
     * Analyzer instance
     * @var Color_Palette_Analyzer
     */
    private $analyzer;

    /**
     * Optimizer instance
     * @var Color_Palette_Optimizer
     */
    private $optimizer;

    /**
     * Exporter instance
     * @var Color_Palette_Exporter
     */
    private $exporter;

    /**
     * Constructor
     */
    public function __construct() {
        $this->generator = new Color_Palette_Generator();
        $this->analyzer = new Color_Palette_Analyzer();
        $this->optimizer = new Color_Palette_Optimizer();
        $this->exporter = new Color_Palette_Exporter();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'generate_palette'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_generate_args()
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)/analyze', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'analyze_palette'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => $this->get_analyze_args()
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)/optimize', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'optimize_palette'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_optimize_args()
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)/export', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'export_palette'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => $this->get_export_args()
            ]
        ]);

        // Add list endpoint
        register_rest_route($this->namespace, '/palettes', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'list_palettes'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_list_args()
            ]
        ]);

        // Add search endpoint
        register_rest_route($this->namespace, '/palettes/search', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'search_palettes'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_search_args()
            ]
        ]);
    }

    /**
     * Get endpoint arguments for palette generation
     *
     * @return array Endpoint arguments
     */
    private function get_generate_args(): array {
        return [
            'theme' => [
                'required' => true,
                'type' => 'string',
                'description' => __('Theme or mood for the palette', 'gl-color-palette-generator'),
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'count' => [
                'required' => false,
                'type' => 'integer',
                'default' => 5,
                'minimum' => 2,
                'maximum' => 10,
                'description' => __('Number of colors in the palette', 'gl-color-palette-generator')
            ],
            'provider' => [
                'required' => false,
                'type' => 'string',
                'default' => 'openai',
                'enum' => ['openai', 'local', 'custom'],
                'description' => __('Color generation provider', 'gl-color-palette-generator')
            ],
            'provider_options' => [
                'required' => false,
                'type' => 'object',
                'default' => [],
                'description' => __('Provider-specific options', 'gl-color-palette-generator')
            ]
        ];
    }

    /**
     * Get endpoint arguments for palette analysis
     *
     * @return array Endpoint arguments
     */
    private function get_analyze_args(): array {
        return [
            'aspects' => [
                'required' => false,
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => ['contrast', 'harmony', 'accessibility', 'all']
                ],
                'default' => ['all'],
                'description' => __('Aspects to analyze', 'gl-color-palette-generator')
            ]
        ];
    }

    /**
     * Get endpoint arguments for palette optimization
     *
     * @return array Endpoint arguments
     */
    private function get_optimize_args(): array {
        return [
            'target_wcag' => [
                'required' => false,
                'type' => 'string',
                'enum' => ['AA', 'AAA'],
                'default' => 'AA',
                'description' => __('Target WCAG compliance level', 'gl-color-palette-generator')
            ],
            'max_adjustment' => [
                'required' => false,
                'type' => 'number',
                'minimum' => 0,
                'maximum' => 1,
                'default' => 0.5,
                'description' => __('Maximum color adjustment allowed (0-1)', 'gl-color-palette-generator')
            ],
            'preserve_relationships' => [
                'required' => false,
                'type' => 'boolean',
                'default' => true,
                'description' => __('Preserve color relationships during optimization', 'gl-color-palette-generator')
            ]
        ];
    }

    /**
     * Get endpoint arguments for palette export
     *
     * @return array Endpoint arguments
     */
    private function get_export_args(): array {
        return [
            'format' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['css', 'scss', 'json', 'ase', 'sketch', 'svg'],
                'description' => __('Export format', 'gl-color-palette-generator')
            ],
            'options' => [
                'required' => false,
                'type' => 'object',
                'default' => [],
                'description' => __('Format-specific export options', 'gl-color-palette-generator')
            ]
        ];
    }

    /**
     * Add pagination headers to response
     *
     * @since 1.0.0
     *
     * @param WP_REST_Response $response Response object.
     * @param int             $total_items Total number of items.
     * @param array           $args Query arguments.
     * @return WP_REST_Response Response with headers.
     */
    private function add_pagination_headers(WP_REST_Response $response, int $total_items, array $args): WP_REST_Response {
        $total_pages = ceil($total_items / $args['per_page']);
        $current_page = $args['page'];

        $response->header('X-WP-Total', $total_items);
        $response->header('X-WP-TotalPages', $total_pages);

        $links = [];
        if ($current_page > 1) {
            $links[] = sprintf(
                '<%s>; rel="prev"',
                $this->get_paginated_url($args, $current_page - 1)
            );
        }
        if ($current_page < $total_pages) {
            $links[] = sprintf(
                '<%s>; rel="next"',
                $this->get_paginated_url($args, $current_page + 1)
            );
        }

        if (!empty($links)) {
            $response->header('Link', implode(', ', $links));
        }

        return $response;
    }

    /**
     * Get paginated URL
     *
     * @since 1.0.0
     *
     * @param array $args Query arguments.
     * @param int   $page Page number.
     * @return string Paginated URL.
     */
    private function get_paginated_url(array $args, int $page): string {
        $args['page'] = $page;
        return add_query_arg($args, rest_url($this->namespace . '/palettes'));
    }

    /**
     * List palettes with pagination headers
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object or error.
     */
    public function list_palettes(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $args = [
            'per_page' => $request->get_param('per_page'),
            'page' => $request->get_param('page'),
            'order' => $request->get_param('order'),
            'meta' => $request->get_param('meta')
        ];

        $total_items = $this->storage->count($args['meta']);
        $palettes = $this->storage->list($args);

        $response = rest_ensure_response(array_map(function($palette) {
            return [
                'id' => $palette->get_metadata('id'),
                'colors' => $palette->get_colors(),
                'metadata' => $palette->get_metadata()
            ];
        }, $palettes));

        return $this->add_pagination_headers($response, $total_items, $args);
    }

    /**
     * Search palettes
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object or error.
     */
    public function search_palettes(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $query = $request->get_param('query');
        $args = [
            'field' => $request->get_param('field'),
            'limit' => $request->get_param('per_page'),
            'order' => $request->get_param('order')
        ];

        $palettes = $this->storage->search($query, $args);

        return rest_ensure_response(array_map(function($palette) {
            return [
                'id' => $palette->get_metadata('id'),
                'colors' => $palette->get_colors(),
                'metadata' => $palette->get_metadata()
            ];
        }, $palettes));
    }

    /**
     * Get list endpoint arguments
     *
     * @since 1.0.0
     *
     * @return array Endpoint arguments.
     */
    private function get_list_args(): array {
        return [
            'page' => [
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1,
                'description' => __('Current page of the collection.', 'gl-color-palette-generator'),
            ],
            'per_page' => [
                'type' => 'integer',
                'default' => 20,
                'minimum' => 1,
                'maximum' => 100,
                'description' => __('Maximum number of items to be returned in result set.', 'gl-color-palette-generator'),
            ],
            'order' => [
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
                'description' => __('Order sort attribute ascending or descending.', 'gl-color-palette-generator'),
            ],
            'meta' => [
                'type' => 'object',
                'default' => [],
                'description' => __('Filter by metadata values.', 'gl-color-palette-generator'),
            ]
        ];
    }

    /**
     * Get search endpoint arguments
     *
     * @since 1.0.0
     *
     * @return array Endpoint arguments.
     */
    private function get_search_args(): array {
        return [
            'query' => [
                'type' => 'string',
                'required' => true,
                'description' => __('Search query string.', 'gl-color-palette-generator'),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'field' => [
                'type' => 'string',
                'default' => 'metadata',
                'enum' => ['name', 'metadata', 'colors'],
                'description' => __('Field to search in.', 'gl-color-palette-generator'),
            ],
            'per_page' => [
                'type' => 'integer',
                'default' => 20,
                'minimum' => 1,
                'maximum' => 100,
                'description' => __('Maximum number of items to be returned in result set.', 'gl-color-palette-generator'),
            ],
            'order' => [
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
                'description' => __('Order sort attribute ascending or descending.', 'gl-color-palette-generator'),
            ]
        ];
    }

    /**
     * Analyze a palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function analyze_palette(WP_REST_Request $request) {
        $palette_id = $request->get_param('id');
        $aspects = $request->get_param('aspects');

        // Get palette from storage/cache
        $palette = $this->get_palette($palette_id);
        if (is_wp_error($palette)) {
            return $palette;
        }

        $analysis = [];

        if (in_array('all', $aspects) || in_array('contrast', $aspects)) {
            $analysis['contrast'] = $this->analyzer->analyze_contrast($palette);
        }

        if (in_array('all', $aspects) || in_array('harmony', $aspects)) {
            $analysis['harmony'] = $this->analyzer->analyze_harmony($palette);
        }

        if (in_array('all', $aspects) || in_array('accessibility', $aspects)) {
            $analysis['accessibility'] = $this->analyzer->analyze_accessibility($palette);
        }

        return rest_ensure_response($analysis);
    }

    /**
     * Optimize a palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function optimize_palette(WP_REST_Request $request) {
        $palette_id = $request->get_param('id');
        $options = [
            'target_wcag' => $request->get_param('target_wcag'),
            'max_adjustment' => $request->get_param('max_adjustment'),
            'preserve_relationships' => $request->get_param('preserve_relationships')
        ];

        // Get palette from storage/cache
        $palette = $this->get_palette($palette_id);
        if (is_wp_error($palette)) {
            return $palette;
        }

        $optimized = $this->optimizer->optimize($palette, $options);
        if (is_wp_error($optimized)) {
            return $optimized;
        }

        return rest_ensure_response([
            'id' => $optimized->get_metadata('id'),
            'colors' => $optimized->get_colors(),
            'metadata' => $optimized->get_metadata()
        ]);
    }

    /**
     * Export a palette
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function export_palette(WP_REST_Request $request) {
        $palette_id = $request->get_param('id');
        $format = $request->get_param('format');
        $options = $request->get_param('options');

        // Get palette from storage/cache
        $palette = $this->get_palette($palette_id);
        if (is_wp_error($palette)) {
            return $palette;
        }

        $exported = $this->exporter->export($palette, $format, $options);
        if (is_wp_error($exported)) {
            return $exported;
        }

        return rest_ensure_response([
            'format' => $format,
            'content' => $exported
        ]);
    }

    /**
     * Get a palette by ID
     *
     * @param string $id Palette ID
     * @return Color_Palette|WP_Error Palette object or error
     */
    private function get_palette(string $id) {
        // Implementation depends on storage mechanism
        // This is a placeholder
        return new \WP_Error(
            'not_implemented',
            __('Palette retrieval not implemented', 'gl-color-palette-generator')
        );
    }
}
