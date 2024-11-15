<?php
/**
 * Color Palette REST Controller
 *
 * @package GLColorPalette
 * @since 1.0.0
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
 * Handles REST API endpoints for:
 * - Generating palettes
 * - Analyzing palettes
 * - Optimizing palettes
 * - Exporting palettes
 *
 * @since 1.0.0
 */
class Color_Palette_REST_Controller extends WP_REST_Controller {
    /**
     * Generator instance
     * @var Color_Palette_Generator
     */
    private $generator;

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
        $this->namespace = 'gl-color-palette/v1';
        $this->rest_base = 'palettes';

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
    }

    // ... (to be continued with endpoint implementations and argument definitions)
} 
