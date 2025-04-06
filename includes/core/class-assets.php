<?php
/**
 * Assets Management Class
 *
 * Handles the registration and enqueuing of all plugin assets.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class
 */
class Assets {
	/**
	 * Asset version for cache busting
	 *
	 * @var string
	 */
	private static $version;

	/**
	 * Initialize the class
	 *
	 * @return void
	 */
	public static function init() {
		self::$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GL_CPG_VERSION;
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets() {
		// Only load on our plugin pages
		$screen = get_current_screen();
		if ( ! $screen || ! self::is_plugin_page( $screen ) ) {
			return;
		}

		// CSS
		wp_enqueue_style(
			'gl-cpg-admin',
			GL_CPG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			self::$version
		);

		// JavaScript
		wp_enqueue_script(
			'gl-cpg-admin',
			GL_CPG_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			self::$version,
			true
		);

		wp_localize_script(
			'gl-cpg-admin',
			'glCpgAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gl_cpg_admin' ),
				'i18n'    => array(
					'generatePalette' => __( 'Generate Palette', 'gl-color-palette-generator' ),
					'savePalette'     => __( 'Save Palette', 'gl-color-palette-generator' ),
					'deletePalette'   => __( 'Delete Palette', 'gl-color-palette-generator' ),
					'confirmDelete'   => __( 'Are you sure you want to delete this palette?', 'gl-color-palette-generator' ),
				),
			)
		);

		// WordPress color picker
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @return void
	 */
	public static function enqueue_frontend_assets() {
		// Only load if shortcode or block is present
		if ( ! self::should_load_frontend_assets() ) {
			return;
		}

		// CSS
		wp_enqueue_style(
			'gl-cpg-frontend',
			GL_CPG_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			self::$version
		);

		// JavaScript
		wp_enqueue_script(
			'gl-cpg-frontend',
			GL_CPG_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			self::$version,
			true
		);

		wp_localize_script(
			'gl-cpg-frontend',
			'glCpgFrontend',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gl_cpg_frontend' ),
				'i18n'    => array(
					'copyColor'   => __( 'Copy Color', 'gl-color-palette-generator' ),
					'colorCopied' => __( 'Color Copied!', 'gl-color-palette-generator' ),
				),
			)
		);
	}

	/**
	 * Check if current screen is a plugin page
	 *
	 * @param \WP_Screen $screen Current screen object
	 * @return bool
	 */
	private static function is_plugin_page( $screen ) {
		$plugin_pages = array(
			'toplevel_page_gl-color-palette-generator',
			'color-palette_page_gl-cpg-settings',
			'color-palette_page_gl-cpg-palettes',
		);

		return in_array( $screen->id, $plugin_pages, true );
	}

	/**
	 * Check if frontend assets should be loaded
	 *
	 * @return bool
	 */
	private static function should_load_frontend_assets() {
		global $post;

		if ( ! is_singular() ) {
			return false;
		}

		// Check for shortcode
		if ( has_shortcode( $post->post_content, 'gl_color_palette' ) ) {
			return true;
		}

		// Check for Gutenberg block
		if ( has_block( 'gl-color-palette-generator/palette', $post ) ) {
			return true;
		}

		return false;
	}
}
