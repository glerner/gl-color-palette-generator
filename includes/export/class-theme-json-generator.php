<?php
namespace GL_Color_Palette_Generator\Export;

use GL_Color_Palette_Generator\Color_Management\Color_Metrics;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Interfaces\Color_Metrics_Interface;
use GL_Color_Palette_Generator\Localization\Theme_Namer;
use GL_Color_Palette_Generator\Traits\Color_Shade_Generator_Trait;
use GL_Color_Palette_Generator\Color_Management\Color_Shade_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Formatter;
use GL_Color_Palette_Generator\Export\CSS_Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme JSON Generator
 *
 * Generates theme.json variations for color palettes
 */
class Theme_Json_Generator implements Color_Constants {
	use Color_Shade_Generator_Trait;

	/** @var Color_Shade_Generator */
	private $shade_generator;
	private $color_utility;
	private $color_metrics;
	private $formatter;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->shade_generator = new Color_Shade_Generator();
		$this->color_utility   = new Color_Utility();
		$this->color_metrics   = new Color_Metrics();
		$this->formatter       = new Color_Palette_Formatter();
	}

	/**
	 * Generate theme.json files for color variations
	 *
	 * Takes base colors for each role and generates theme.json files
	 * with color palettes including tints and shades.
	 *
	 * @param array  $colors Array of colors with roles ['primary' => '#hex', 'secondary' => '#hex', etc.]
	 * @param string $scheme Color scheme type
	 * @return array|\WP_Error      Array of theme.json data or error
	 */
	public function generate_theme_json_variations( $colors, $scheme = 'complementary' ) {
		// Validate colors for scheme
		if ( ! isset( self::REQUIRED_ROLES[ $scheme ] ) ) {
			return new \WP_Error(
				'invalid_scheme',
				__( 'Invalid scheme type', 'gl-color-palette-generator' )
			);
		}

		$required_roles = self::REQUIRED_ROLES[ $scheme ];
		foreach ( $required_roles as $role ) {
			if ( ! isset( $colors[ $role ] ) && ! in_array( $role, array( 'base', 'contrast', 'neutral' ) ) ) {
				return new \WP_Error(
					'missing_color_role',
					sprintf( __( 'Missing required color role: %1$s for scheme: %2$s', 'gl-color-palette-generator' ), $role, $scheme )
				);
			}
		}

		$theme_jsons = array();

		// Generate light mode variations
		$light_variations = array();

		// Generate base colors for light mode
		$base_colors        = $this->shade_generator->generate_theme_base_colors( $colors['primary'], false );
		$colors['base']     = $base_colors['base'];
		$colors['contrast'] = $base_colors['contrast'];
		foreach ( $base_colors['neutral'] as $key => $color ) {
			$colors[ $key ] = $color;
		}

		foreach ( $colors as $role => $color ) {
			$light_variations[ $role ] = $this->generate_accessible_shades(
				$color,
				array(
					'contrast_level' => self::WCAG_CONTRAST_MIN,
					'small_text'     => true,
					'include_base'   => true,
				)
			);
		}
		$theme_jsons = array_merge( $theme_jsons, $this->create_theme_json_data( $light_variations, $scheme, 'light' ) );

		// Generate dark mode variations
		$dark_variations = array();

		// Generate base colors for dark mode
		$base_colors        = $this->shade_generator->generate_theme_base_colors( $colors['primary'], true );
		$colors['base']     = $base_colors['base'];
		$colors['contrast'] = $base_colors['contrast'];
		foreach ( $base_colors['neutral'] as $key => $color ) {
			$colors[ $key ] = $color;
		}

		foreach ( $colors as $role => $color ) {
			$dark_variations[ $role ] = $this->generate_accessible_shades(
				$color,
				array(
					'contrast_level' => self::WCAG_CONTRAST_MIN,
					'small_text'     => true,
					'include_base'   => true,
				)
			);
		}
		$theme_jsons = array_merge( $theme_jsons, $this->create_theme_json_data( $dark_variations, $scheme, 'dark' ) );

		return $theme_jsons;
	}

	/**
	 * Create theme.json data from color variations
	 *
	 * @param array  $variations Color variations
	 * @param string $scheme     Color scheme type
	 * @param string $mode       'light' or 'dark'
	 * @return array        Array of theme.json data
	 */
	private function create_theme_json_data( $variations, $scheme, $mode ) {
		$theme_jsons = array();
		$base_theme  = $this->get_base_theme_json();

		foreach ( $variations as $role => $colors ) {
			$theme                                 = $base_theme;
			$theme['settings']['color']['palette'] = $this->generate_color_palette( $variations, $scheme );

			$name           = $this->theme_namer->generate_theme_name( $variations, $scheme );
			$theme['title'] = sprintf( '%s %s', $name, ucfirst( $mode ) );

			$theme_jsons[ $theme['title'] ] = $theme;
		}

		return $theme_jsons;
	}

	/**
	 * Create color palette entries for theme.json
	 *
	 * @param array  $variations Color variations by role
	 * @param string $scheme_type Color scheme type
	 * @return array Color palette entries
	 */
	private function generate_color_palette( array $variations, string $scheme_type ): array {
		$palette = array();

		// Add variations for each role
		foreach ( $variations as $role => $colors ) {
			foreach ( self::COLOR_VARIATIONS as $variation => $label ) {
				if ( isset( $colors[ $variation ] ) ) {
					$palette[] = array(
						'slug'  => $variation === 'base' ? $role : "{$role}-{$variation}",
						'color' => $colors[ $variation ],
						'name'  => $this->formatter->format_color_name( $role, $variation ),
					);
				}
			}
		}

		// Add standard colors at the end
		$palette[] = array(
			'slug'  => 'white',
			'color' => self::COLOR_WHITE,
			'name'  => __( 'White', 'gl-color-palette-generator' ),
		);
		$palette[] = array(
			'slug'  => 'black',
			'color' => self::COLOR_NEAR_BLACK,
			'name'  => __( 'Black', 'gl-color-palette-generator' ),
		);
		$palette[] = array(
			'slug'  => 'transparent',
			'color' => 'transparent',
			'name'  => __( 'Transparent', 'gl-color-palette-generator' ),
		);

		return $palette;
	}

	/**
	 * Get base theme.json structure
	 *
	 * @return array Base theme.json structure
	 */
	private function get_base_theme_json() {
		return array(
			'$schema'  => 'https://schemas.wp.org/wp/6.5/theme.json',
			'version'  => 2,
			'title'    => '',
			'settings' => array(
				'color' => array(
					'palette' => array(),
				),
			),
		);
	}

	/**
	 * Save theme.json variations to files
	 *
	 * @param array  $variations Theme.json variations to save
	 * @param string $scheme     Color scheme type
	 * @param string $mode       'light' or 'dark'
	 * @return string|WP_Error Path to zip file or error
	 */
	public function save_theme_json_files( $variations, $scheme, $mode ) {
		$export_dir = $this->get_export_directory();
		$temp_dir   = trailingslashit( $export_dir ) . 'temp-' . time();

		if ( ! wp_mkdir_p( $temp_dir ) ) {
			return new \WP_Error(
				'export_failed',
				__( 'Failed to create temporary directory', 'gl-color-palette-generator' )
			);
		}

		// Save variations to files
		foreach ( $variations as $name => $variation ) {
			$file_name = sanitize_file_name( $name ) . '.json';
			$file_path = trailingslashit( $temp_dir ) . $file_name;

			if ( file_put_contents( $file_path, wp_json_encode( $variation, JSON_PRETTY_PRINT ) ) === false ) {
				return new \WP_Error(
					'export_failed',
					__( 'Failed to save variation file', 'gl-color-palette-generator' )
				);
			}
		}

		// Create zip file
		$zip_name = 'theme-variations-' . time() . '.zip';
		$zip_path = trailingslashit( $export_dir ) . $zip_name;

		$zip = new \ZipArchive();
		if ( $zip->open( $zip_path, \ZipArchive::CREATE ) !== true ) {
			return new \WP_Error(
				'export_failed',
				__( 'Failed to create zip file', 'gl-color-palette-generator' )
			);
		}

		// Add files to zip
		$files = glob( $temp_dir . '/*.json' );
		foreach ( $files as $file ) {
			$zip->addFile( $file, basename( $file ) );
		}

		$zip->close();

		// Clean up temp directory
		array_map( 'unlink', glob( "$temp_dir/*.*" ) );
		rmdir( $temp_dir );

		return $zip_path;
	}

	/**
	 * Get export directory path
	 *
	 * @return string Export directory path
	 */
	private function get_export_directory() {
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'gl-color-palette-generator';

		// Create directory if it doesn't exist
		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );
		}

		// Add index.php for security
		$index_file = trailingslashit( $export_dir ) . 'index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, '<?php // Silence is golden' );
		}

		return $export_dir;
	}
}
