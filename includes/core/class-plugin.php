<?php

namespace GL_Color_Palette_Generator\Core;

/**
 * Main plugin class
 */
class Plugin {
	/**
	 * Plugin instance
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Initialize plugin components
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin
	 *
	 * @return void
	 */
	public function init(): void {
		// Register shortcodes
		add_shortcode( 'gl_color_palette', array( $this, 'render_color_palette' ) );

		// Register settings
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
	}

	/**
	 * Register admin menu
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			'GL Color Palette Generator',
			'Color Palette',
			'manage_options',
			'gl-color-palette-generator',
			array( $this, 'render_admin_page' ),
			'dashicons-art'
		);
	}

	/**
	 * Register settings
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting( 'gl_cpg_settings', 'gl_cpg_settings' );

		add_settings_section(
			'gl_cpg_settings',
			'General Settings',
			array( $this, 'render_settings_section' ),
			'gl_cpg_settings'
		);
	}

	/**
	 * Render admin page
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		// Admin page content will be added later
		echo '<div class="wrap"><h1>GL Color Palette Generator</h1></div>';
	}

	/**
	 * Render settings section
	 *
	 * @return void
	 */
	public function render_settings_section(): void {
		// Settings section content will be added later
	}

	/**
	 * Render color palette shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_color_palette( $atts ): string {
		// Shortcode rendering will be added later
		return '';
	}
}
