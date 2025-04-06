<?php
/**
 * REST API Controller Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\API;

use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Exporter;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Accessibility\Accessibility_Checker;
use GL_Color_Palette_Generator\Interfaces\Accessibility_Checker as Accessibility_Checker_Interface;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

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
	 * Color utility instance
	 *
	 * @var Color_Utility
	 */
	private $color_utility;

	/**
	 * Accessibility checker instance
	 *
	 * @var Accessibility_Checker_Interface
	 */
	private $accessibility_checker;
	private $palette_generator;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->color_utility         = new Color_Utility();
		$this->accessibility_checker = new Accessibility_Checker();
		$this->palette_generator     = new Color_Palette_Generator( $this->color_utility );
	}
	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/contrast',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'check_contrast' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'color1' => array(
						'required' => true,
						'type'     => 'string',
						'pattern'  => '/^#[0-9a-f]{6}$/i',
					),
					'color2' => array(
						'required' => true,
						'type'     => 'string',
						'pattern'  => '/^#[0-9a-f]{6}$/i',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/generate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'generate_palette' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'prompt' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/palettes',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_palettes' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_palette' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'name'   => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'colors' => array(
							'required' => true,
							'type'     => 'array',
							'items'    => array(
								'type'    => 'string',
								'pattern' => '/^#[0-9a-f]{6}$/i',
							),
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/palettes/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_palette' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/export',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'export_palettes' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'format' => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => array( 'json', 'csv' ),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/import',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_palettes' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/check-contrast',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'check_contrast' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'text_color' => array(
						'required' => true,
						'type'     => 'string',
						'pattern'  => '^#[0-9a-fA-F]{6}$',
					),
					'bg_color'   => array(
						'required' => true,
						'type'     => 'string',
						'pattern'  => '^#[0-9a-fA-F]{6}$',
					),
				),
			)
		);
	}

	/**
	 * Check API request permissions
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error
	 */
	public function check_permission( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to do that.', 'gl-color-palette-generator' ),
				array( 'status' => 401 )
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
	public function generate_palette( WP_REST_Request $request ) {
		$params    = $request->get_params();
		$generator = $this->palette_generator;
		try {
			$palette = $generator->generate_from_prompt( $params['prompt'] );

			return new WP_REST_Response(
				array(
					'palette' => $palette,
					'message' => __( 'Palette generated successfully.', 'gl-color-palette-generator' ),
				),
				200
			);
		} catch ( \Exception $e ) {
			return new WP_Error(
				'palette_generation_failed',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Get saved palettes
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_palettes( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gl_color_palettes';

		$palettes = $wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY created_at DESC",
			ARRAY_A
		);

		if ( $palettes === null ) {
			return new WP_Error(
				'database_error',
				__( 'Error retrieving palettes.', 'gl-color-palette-generator' ),
				array( 'status' => 500 )
			);
		}

		foreach ( $palettes as &$palette ) {
			$palette['colors'] = json_decode( $palette['colors'], true );
		}

		return new WP_REST_Response( $palettes, 200 );
	}

	/**
	 * Save color palette
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_palette( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gl_color_palettes';

		$result = $wpdb->insert(
			$table_name,
			array(
				'name'       => $request['name'],
				'colors'     => wp_json_encode( $request['colors'] ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( $result === false ) {
			return new WP_Error(
				'database_error',
				__( 'Error saving palette.', 'gl-color-palette-generator' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'id'      => $wpdb->insert_id,
				'message' => __( 'Palette saved successfully.', 'gl-color-palette-generator' ),
			),
			201
		);
	}

	/**
	 * Delete color palette
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_palette( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gl_color_palettes';

		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $request['id'] ),
			array( '%d' )
		);

		if ( $result === false ) {
			return new WP_Error(
				'database_error',
				__( 'Error deleting palette.', 'gl-color-palette-generator' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'message' => __( 'Palette deleted successfully.', 'gl-color-palette-generator' ),
			),
			200
		);
	}

	/**
	 * Export palettes
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function export_palettes( $request ) {
		try {
			global $wpdb;
			$table_name = $wpdb->prefix . 'gl_color_palettes';

			$palettes = $wpdb->get_results(
				"SELECT * FROM {$table_name} ORDER BY created_at DESC",
				ARRAY_A
			);

			if ( $palettes === false ) {
				return new WP_Error(
					'database_error',
					__( 'Error retrieving palettes.', 'gl-color-palette-generator' ),
					array( 'status' => 500 )
				);
			}

			$exporter = new Color_Palette_Exporter();
			$format   = $request->get_param( 'format' );

			if ( $format === 'csv' ) {
				$content   = $exporter->export_to_csv( $palettes );
				$mime_type = 'text/csv';
				$filename  = 'color-palettes.csv';
			} else {
				$content   = $exporter->export_to_json( $palettes );
				$mime_type = 'application/json';
				$filename  = 'color-palettes.json';
			}

			$response = new WP_REST_Response(
				array(
					'content'   => $content,
					'filename'  => $filename,
					'mime_type' => $mime_type,
				)
			);

			$response->header( 'Content-Type', $mime_type );
			$response->header( 'Content-Disposition', 'attachment; filename="' . $filename . '"' );

			return $response;

		} catch ( \Exception $e ) {
			return new WP_Error(
				'export_error',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Import palettes
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function import_palettes( $request ) {
		try {
			$file = $request->get_file_params()['file'];
			if ( ! $file || $file['error'] ) {
				return new WP_Error(
					'upload_error',
					__( 'Error uploading file.', 'gl-color-palette-generator' ),
					array( 'status' => 400 )
				);
			}

			$content  = file_get_contents( $file['tmp_name'] );
			$exporter = new Color_Palette_Exporter();

			// Determine format from file extension
			$format = pathinfo( $file['name'], PATHINFO_EXTENSION );
			if ( $format === 'csv' ) {
				$palettes = $exporter->import_from_csv( $content );
			} else {
				$palettes = $exporter->import_from_json( $content );
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'gl_color_palettes';
			$imported   = 0;

			foreach ( $palettes as $palette ) {
				$result = $wpdb->insert(
					$table_name,
					array(
						'name'       => $palette['name'],
						'colors'     => wp_json_encode( $palette['colors'] ),
						'created_at' => current_time( 'mysql' ),
						'updated_at' => current_time( 'mysql' ),
					),
					array( '%s', '%s', '%s', '%s' )
				);

				if ( $result ) {
					++$imported;
				}
			}

			return new WP_REST_Response(
				array(
					'message'        => sprintf(
						__( 'Successfully imported %d palettes.', 'gl-color-palette-generator' ),
						$imported
					),
					'imported_count' => $imported,
				),
				200
			);

		} catch ( \Exception $e ) {
			return new WP_Error(
				'import_error',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Check color contrast
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function check_contrast( $request ) {
		try {
			$text_color = $request->get_param( 'color1' );
			$bg_color   = $request->get_param( 'color2' );

			if ( ! $text_color || ! $bg_color ) {
				return new WP_Error(
					'missing_colors',
					__( 'Both colors are required.', 'gl-color-palette-generator' ),
					array( 'status' => 400 )
				);
			}

			$results = $this->accessibility_checker->check_combination( $text_color, $bg_color );

			if ( $results === false ) {
				return new WP_Error(
					'contrast_check_error',
					__( 'Error checking contrast.', 'gl-color-palette-generator' ),
					array( 'status' => 500 )
				);
			}

			return new WP_REST_Response( $results, 200 );

		} catch ( \Exception $e ) {
			return new WP_Error(
				'contrast_check_error',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}
}
