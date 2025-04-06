<?php
/**
 * Color Palette REST Controller
 *
 * Handles REST API endpoints for palette generation, analysis, optimization,
 * and export functionality.
 *
 * @package GL_Color_Palette_Generator
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Core\Rate_Limiter;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Generator_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Analyzer_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Optimizer_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Exporter_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Storage_Interface;
use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class Color_Palette_REST_Controller extends WP_REST_Controller {
	/**
	 * API namespace
	 *
	 * @var string
	 */
	protected $namespace = 'gl-color-palette/v1';

	/**
	 * Base resource path
	 *
	 * @var string
	 */
	protected $rest_base = 'palettes';

	/**
	 * Generator instance
	 *
	 * @var Color_Palette_Generator_Interface
	 */
	private Color_Palette_Generator_Interface $generator;

	/**
	 * Analyzer instance
	 *
	 * @var Color_Palette_Analyzer_Interface
	 */
	private Color_Palette_Analyzer_Interface $analyzer;

	/**
	 * Optimizer instance
	 *
	 * @var Color_Palette_Optimizer_Interface
	 */
	private Color_Palette_Optimizer_Interface $optimizer;

	/**
	 * Exporter instance
	 *
	 * @var Color_Exporter_Interface
	 */
	private Color_Exporter_Interface $exporter;

	/**
	 * Storage instance
	 *
	 * @var Color_Palette_Storage_Interface
	 */
	private Color_Palette_Storage_Interface $storage;

	/**
	 * Rate limiter instance
	 *
	 * @var Rate_Limiter
	 */
	private Rate_Limiter $rate_limiter;

	/**
	 * Constructor
	 */
	public function __construct(
		Color_Palette_Generator_Interface $generator,
		Color_Palette_Analyzer_Interface $analyzer,
		Color_Palette_Optimizer_Interface $optimizer,
		Color_Exporter_Interface $exporter,
		Color_Palette_Storage_Interface $storage,
		Rate_Limiter $rate_limiter
	) {
		$this->generator    = $generator;
		$this->analyzer     = $analyzer;
		$this->optimizer    = $optimizer;
		$this->exporter     = $exporter;
		$this->storage      = $storage;
		$this->rate_limiter = $rate_limiter;
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generate_palette' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_generate_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)/analyze',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'analyze_palette' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_analyze_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)/optimize',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'optimize_palette' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_optimize_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[a-zA-Z0-9-]+)/export',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'export_palette' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_export_args(),
				),
			)
		);

		// Add list endpoint
		register_rest_route(
			$this->namespace,
			'/palettes',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_palettes' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_list_args(),
				),
			)
		);

		// Add search endpoint
		register_rest_route(
			$this->namespace,
			'/palettes/search',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'search_palettes' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_search_args(),
				),
			)
		);
	}

	/**
	 * Get endpoint arguments for palette generation
	 *
	 * @return array Endpoint arguments
	 */
	private function get_generate_args(): array {
		return array(
			'theme'            => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'Theme or mood for the palette', 'gl-color-palette-generator' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'count'            => array(
				'required'    => false,
				'type'        => 'integer',
				'default'     => 5,
				'minimum'     => 2,
				'maximum'     => 10,
				'description' => __( 'Number of colors in the palette', 'gl-color-palette-generator' ),
			),
			'provider'         => array(
				'required'    => false,
				'type'        => 'string',
				'default'     => 'openai',
				'enum'        => array( 'openai', 'local', 'custom' ),
				'description' => __( 'Color generation provider', 'gl-color-palette-generator' ),
			),
			'provider_options' => array(
				'required'    => false,
				'type'        => 'object',
				'default'     => array(),
				'description' => __( 'Provider-specific options', 'gl-color-palette-generator' ),
			),
		);
	}

	/**
	 * Get endpoint arguments for palette analysis
	 *
	 * @return array Endpoint arguments
	 */
	private function get_analyze_args(): array {
		return array(
			'aspects' => array(
				'required'    => false,
				'type'        => 'array',
				'items'       => array(
					'type' => 'string',
					'enum' => array( 'contrast', 'harmony', 'accessibility', 'all' ),
				),
				'default'     => array( 'all' ),
				'description' => __( 'Aspects to analyze', 'gl-color-palette-generator' ),
			),
		);
	}

	/**
	 * Get endpoint arguments for palette optimization
	 *
	 * @return array Endpoint arguments
	 */
	private function get_optimize_args(): array {
		return array(
			'target_wcag'            => array(
				'required'    => false,
				'type'        => 'string',
				'enum'        => array( 'AA', 'AAA' ),
				'default'     => 'AA',
				'description' => __( 'Target WCAG compliance level', 'gl-color-palette-generator' ),
			),
			'max_adjustment'         => array(
				'required'    => false,
				'type'        => 'number',
				'minimum'     => 0,
				'maximum'     => 1,
				'default'     => 0.5,
				'description' => __( 'Maximum color adjustment allowed (0-1)', 'gl-color-palette-generator' ),
			),
			'preserve_relationships' => array(
				'required'    => false,
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Preserve color relationships during optimization', 'gl-color-palette-generator' ),
			),
		);
	}

	/**
	 * Get endpoint arguments for palette export
	 *
	 * @return array Endpoint arguments
	 */
	private function get_export_args(): array {
		return array(
			'format'  => array(
				'required'    => true,
				'type'        => 'string',
				'enum'        => array( 'css', 'scss', 'json', 'ase', 'sketch', 'svg' ),
				'description' => __( 'Export format', 'gl-color-palette-generator' ),
			),
			'options' => array(
				'required'    => false,
				'type'        => 'object',
				'default'     => array(),
				'description' => __( 'Format-specific export options', 'gl-color-palette-generator' ),
			),
		);
	}

	/**
	 * Add pagination headers to response
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Response $response Response object.
	 * @param int              $total_items Total number of items.
	 * @param array            $args Query arguments.
	 * @return WP_REST_Response Response with headers.
	 */
	private function add_pagination_headers( WP_REST_Response $response, int $total_items, array $args ): WP_REST_Response {
		$total_pages  = ceil( $total_items / $args['per_page'] );
		$current_page = $args['page'];

		$response->header( 'X-WP-Total', (string) $total_items );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );

		$links = array();
		if ( $current_page > 1 ) {
			$links[] = sprintf(
				'<%s>; rel="prev"',
				$this->get_paginated_url( $args, $current_page - 1 )
			);
		}
		if ( $current_page < $total_pages ) {
			$links[] = sprintf(
				'<%s>; rel="next"',
				$this->get_paginated_url( $args, $current_page + 1 )
			);
		}

		if ( count( $links ) > 0 ) {
			$response->header( 'Link', implode( ', ', $links ) );
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
	private function get_paginated_url( array $args, int $page ): string {
		$args['page'] = $page;
		return add_query_arg( $args, rest_url( $this->namespace . '/palettes' ) );
	}

	/**
	 * List palettes with pagination headers
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function list_palettes( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$per_page = $request->get_param( 'per_page' );
		if ( $per_page === null ) {
			$per_page = 10;
		}

		$page = $request->get_param( 'page' );
		if ( $page === null ) {
			$page = 1;
		}

		$order = $request->get_param( 'order' );
		if ( $order === null ) {
			$order = 'DESC';
		}

		$meta = $request->get_param( 'meta' );
		if ( $meta === null ) {
			$meta = array();
		}

		$args = array(
			'per_page' => (int) $per_page,
			'page'     => (int) $page,
			'order'    => $order,
			'meta'     => $meta,
		);

		$total_items = $this->storage->count_palettes();
		if ( $total_items instanceof WP_Error ) {
			return $total_items;
		}

		$palettes = $this->storage->list_palettes( $args );
		if ( $palettes instanceof WP_Error ) {
			return $palettes;
		}

		$response = rest_ensure_response( array_map( array( $this, 'prepare_palette_for_response' ), $palettes ) );

		return $this->add_pagination_headers( $response, $total_items, $args );
	}

	/**
	 * Search palettes
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function search_palettes( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$query = $request->get_param( 'query' );
		if ( $query === null || $query === '' ) {
			return new WP_Error(
				'missing_query',
				__( 'Search query is required.', 'gl-color-palette-generator' ),
				array( 'status' => 400 )
			);
		}

		$field = $request->get_param( 'field' );
		if ( $field === null ) {
			$field = 'metadata';
		}

		$limit = $request->get_param( 'per_page' );
		if ( $limit === null ) {
			$limit = 10;
		}

		$order = $request->get_param( 'order' );
		if ( $order === null ) {
			$order = 'DESC';
		}

		$args = array(
			'field' => $field,
			'limit' => (int) $limit,
			'order' => $order,
		);

		$palettes = $this->storage->search_palettes( $query, $args );
		if ( $palettes instanceof WP_Error ) {
			return $palettes;
		}

		return rest_ensure_response( array_map( array( $this, 'prepare_palette_for_response' ), $palettes ) );
	}

	/**
	 * Get list endpoint arguments
	 *
	 * @since 1.0.0
	 *
	 * @return array Endpoint arguments.
	 */
	private function get_list_args(): array {
		return array(
			'page'     => array(
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
				'description' => __( 'Current page of the collection.', 'gl-color-palette-generator' ),
			),
			'per_page' => array(
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
				'description' => __( 'Maximum number of items to be returned in result set.', 'gl-color-palette-generator' ),
			),
			'order'    => array(
				'type'        => 'string',
				'default'     => 'DESC',
				'enum'        => array( 'ASC', 'DESC' ),
				'description' => __( 'Order sort attribute ascending or descending.', 'gl-color-palette-generator' ),
			),
			'meta'     => array(
				'type'        => 'object',
				'default'     => array(),
				'description' => __( 'Filter by metadata values.', 'gl-color-palette-generator' ),
			),
		);
	}

	/**
	 * Get search endpoint arguments
	 *
	 * @since 1.0.0
	 *
	 * @return array Endpoint arguments.
	 */
	private function get_search_args(): array {
		return array(
			'query'    => array(
				'type'              => 'string',
				'required'          => true,
				'description'       => __( 'Search query string.', 'gl-color-palette-generator' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'field'    => array(
				'type'        => 'string',
				'default'     => 'metadata',
				'enum'        => array( 'name', 'metadata', 'colors' ),
				'description' => __( 'Field to search in.', 'gl-color-palette-generator' ),
			),
			'per_page' => array(
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
				'description' => __( 'Maximum number of items to be returned in result set.', 'gl-color-palette-generator' ),
			),
			'order'    => array(
				'type'        => 'string',
				'default'     => 'DESC',
				'enum'        => array( 'ASC', 'DESC' ),
				'description' => __( 'Order sort attribute ascending or descending.', 'gl-color-palette-generator' ),
			),
		);
	}

	/**
	 * Check rate limit before processing request
	 *
	 * @param string $identifier Rate limit identifier
	 * @return bool|WP_Error True if within limit, WP_Error if exceeded
	 */
	private function check_rate_limit( string $identifier ): bool|WP_Error {
		$result = $this->rate_limiter->check_rate_limit( $identifier );
		if ( $result instanceof WP_Error ) {
			return $result;
		}
		return true;
	}

	/**
	 * Add rate limit headers to response
	 *
	 * @param WP_REST_Response $response Response object
	 * @param string           $identifier Rate limit identifier
	 * @return WP_REST_Response Response with headers
	 */
	private function add_rate_limit_headers( WP_REST_Response $response, string $identifier ): WP_REST_Response {
		$headers = $this->rate_limiter->get_headers( $identifier );
		foreach ( $headers as $name => $value ) {
			$response->header( $name, $value );
		}
		return $response;
	}

	/**
	 * Analyze a palette
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function analyze_palette( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$palette_id = $request->get_param( 'id' );

		$aspects = $request->get_param( 'aspects' );
		if ( $aspects === null ) {
			$aspects = array();
		}

		$palette = $this->get_palette( $palette_id );
		if ( $palette instanceof WP_Error ) {
			return $palette;
		}

		if ( $palette === null ) {
			return new WP_Error(
				'palette_not_found',
				__( 'Palette not found.', 'gl-color-palette-generator' ),
				array( 'status' => 404 )
			);
		}

		$analysis = $this->analyzer->analyze_palette( $palette, $aspects );
		if ( $analysis instanceof WP_Error ) {
			return $analysis;
		}

		return new WP_REST_Response( $analysis );
	}

	/**
	 * Optimize a palette
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function optimize_palette( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$palette_id = $request->get_param( 'id' );

		$options = $request->get_param( 'options' );
		if ( $options === null ) {
			$options = array();
		}

		$palette = $this->get_palette( $palette_id );
		if ( $palette instanceof WP_Error ) {
			return $palette;
		}

		if ( $palette === null ) {
			return new WP_Error(
				'palette_not_found',
				__( 'Palette not found.', 'gl-color-palette-generator' ),
				array( 'status' => 404 )
			);
		}

		$optimized = $this->optimizer->optimize_palette( $palette, $options );
		if ( $optimized instanceof WP_Error ) {
			return $optimized;
		}

		$result = $this->storage->update_palette( $palette_id, $optimized );
		if ( $result instanceof WP_Error ) {
			return $result;
		}

		return new WP_REST_Response( $this->prepare_palette_for_response( $optimized ) );
	}

	/**
	 * Export a palette
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function export_palette( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$palette_id = $request->get_param( 'id' );
		$format     = $request->get_param( 'format' );

		$options = $request->get_param( 'options' );
		if ( $options === null ) {
			$options = array();
		}

		$palette = $this->get_palette( $palette_id );
		if ( $palette instanceof WP_Error ) {
			return $palette;
		}

		if ( $palette === null ) {
			return new WP_Error(
				'palette_not_found',
				__( 'Palette not found.', 'gl-color-palette-generator' ),
				array( 'status' => 404 )
			);
		}

		$exported = $this->exporter->export_palette( $palette, $format, $options );
		if ( $exported instanceof WP_Error ) {
			return $exported;
		}

		return new WP_REST_Response( $exported );
	}

	/**
	 * Prepare palette for response
	 *
	 * @param Color_Palette $palette Palette to prepare
	 * @return array Prepared palette data
	 */
	private function prepare_palette_for_response( Color_Palette $palette ): array {
		return array(
			'id'         => $palette->get_id(),
			'colors'     => $palette->get_colors(),
			'metadata'   => $palette->get_metadata(),
			'created_at' => $palette->get_created_at(),
			'updated_at' => $palette->get_updated_at() ?? $palette->get_created_at(),
		);
	}

	/**
	 * Get a palette by ID
	 *
	 * @param string $id Palette ID
	 * @return Color_Palette|WP_Error|null Palette object, null if not found, or error
	 */
	protected function get_palette( string $id ): Color_Palette|WP_Error|null {
		return $this->storage->get_palette( (int) $id );
	}

	/**
	 * Generate a new color palette
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function generate_palette( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$identifier = get_current_user_id();
		if ( $identifier <= 0 ) {
			$identifier = $_SERVER['REMOTE_ADDR'] ?? '';
			if ( $identifier === '' ) {
				return new WP_Error(
					'invalid_user',
					__( 'Could not identify user.', 'gl-color-palette-generator' ),
					array( 'status' => 400 )
				);
			}
			$identifier = 'ip_' . $identifier;
		} else {
			$identifier = 'user_' . $identifier;
		}

		$rate_limit_check = $this->check_rate_limit( $identifier );
		if ( $rate_limit_check instanceof WP_Error ) {
			return $rate_limit_check;
		}

		$theme = $request->get_param( 'theme' );
		if ( $theme === null || $theme === '' ) {
			return new WP_Error(
				'missing_theme',
				__( 'Theme parameter is required.', 'gl-color-palette-generator' ),
				array( 'status' => 400 )
			);
		}

		$count            = $request->get_param( 'count' );
		$provider         = $request->get_param( 'provider' );
		$provider_options = $request->get_param( 'provider_options' );

		$options = array(
			'count'            => $count ? (int) $count : 5,
			'provider'         => $provider ? (string) $provider : 'openai',
			'provider_options' => $provider_options ? (array) $provider_options : array(),
		);

		$palette = $this->generator->generate_palette( $options );
		if ( $palette instanceof WP_Error ) {
			return $palette;
		}

		$response = new WP_REST_Response(
			array(
				'id'       => $palette->get_id(),
				'colors'   => $palette->get_colors(),
				'metadata' => $palette->get_metadata(),
			)
		);

		$identifier = get_current_user_id() > 0 ? 'user_' . get_current_user_id() : 'ip_' . $_SERVER['REMOTE_ADDR'];
		return $this->add_rate_limit_headers( $response, $identifier );
	}
}
