<?php
/**
 * Color Palette Admin Class
 *
 * Handles the WordPress admin interface for the color palette generator.
 * Manages admin menus, settings pages, and dashboard integration.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Admin
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Admin;

use GL_Color_Palette_Generator\Settings\Settings_Manager;

/**
 * Class Color_Palette_Admin
 *
 * Implements the WordPress admin interface functionality for the plugin.
 * Handles menu creation, settings registration, and admin page rendering.
 *
 * @since 1.0.0
 */
class Color_Palette_Admin {
	/**
	 * Settings manager instance
	 *
	 * @var Settings_Manager The settings manager object
	 * @since 1.0.0
	 */
	protected $settings;

	/**
	 * Initialize the admin
	 *
	 * Sets up the admin interface by registering menus, settings, and hooks.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		$this->settings = new Settings_Manager();

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_gl_cpg_generate_palette', array( $this, 'handle_generate_palette' ) );
	}

	/**
	 * Add admin menu items
	 *
	 * Registers the main menu and settings submenu for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'Color Palette Generator', 'gl-color-palette-generator' ),
			__( 'Color Palettes', 'gl-color-palette-generator' ),
			'manage_options',
			'gl-color-palette-generator',
			array( $this, 'render_main_page' ),
			'dashicons-art',
			30
		);

		add_submenu_page(
			'gl-color-palette-generator',
			__( 'Settings', 'gl-color-palette-generator' ),
			__( 'Settings', 'gl-color-palette-generator' ),
			'manage_options',
			'gl-color-palette-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * Loads the required CSS and JavaScript files for the admin interface.
	 *
	 * @param string $hook Current admin page.
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets( $hook ): void {
		if ( ! strpos( $hook, 'gl-color-palette' ) ) {
			return;
		}

		wp_enqueue_style(
			'gl-cpg-admin',
			GL_CPG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			GL_CPG_VERSION
		);

		wp_enqueue_script(
			'gl-cpg-admin',
			GL_CPG_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			GL_CPG_VERSION,
			true
		);

		wp_localize_script(
			'gl-cpg-admin',
			'glCpgAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gl_cpg_admin' ),
				'i18n'    => array(
					'generateError' => __( 'Error generating palette. Please try again.', 'gl-color-palette-generator' ),
					'saveSuccess'   => __( 'Palette saved successfully.', 'gl-color-palette-generator' ),
					'saveError'     => __( 'Error saving palette. Please try again.', 'gl-color-palette-generator' ),
				),
			)
		);
	}

	/**
	 * Render main admin page
	 *
	 * Displays the main admin page for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function render_main_page(): void {
		$palettes = $this->get_saved_palettes();
		include GL_CPG_PLUGIN_DIR . 'templates/admin/main-page.php';
	}

	/**
	 * Render settings page
	 *
	 * Displays the settings page for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page(): void {
		include GL_CPG_PLUGIN_DIR . 'templates/admin/settings-page.php';
	}

	/**
	 * Handle palette generation AJAX request
	 *
	 * Processes the AJAX request for generating a color palette.
	 *
	 * @since 1.0.0
	 */
	public function handle_generate_palette(): void {
		check_ajax_referer( 'gl_cpg_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'gl-color-palette-generator' ) );
		}

		$prompt = sanitize_text_field( $_POST['prompt'] ?? '' );
		if ( $prompt === '' ) {
			wp_send_json_error( __( 'Prompt is required.', 'gl-color-palette-generator' ) );
		}

		try {
			$color_utility = new \GL_Color_Palette_Generator\Color_Management\Color_Utility();
			$generator     = new \GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator( $color_utility );
			$palette       = $generator->generate_from_prompt( $prompt );
			wp_send_json_success(
				array(
					'palette' => $palette,
					'message' => __( 'Palette generated successfully.', 'gl-color-palette-generator' ),
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Get saved color palettes
	 *
	 * Retrieves the saved color palettes from the database.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function get_saved_palettes(): array {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gl_color_palettes';

		$palettes = $wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY created_at DESC",
			ARRAY_A
		);

		return array_map(
			function ( $palette ) {
				$palette['colors'] = json_decode( $palette['colors'], true );
				return $palette;
			},
			$palettes ?? array()
		);
	}

	/**
	 * Save color palette
	 *
	 * Saves a color palette to the database.
	 *
	 * @param string $name Palette name.
	 * @param array  $colors Palette colors.
	 * @return int|false The number of rows inserted, or false on error.
	 * @since 1.0.0
	 */
	public function save_palette( string $name, array $colors ): int|false {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gl_color_palettes';

		return $wpdb->insert(
			$table_name,
			array(
				'name'       => sanitize_text_field( $name ),
				'colors'     => wp_json_encode( $colors ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Delete color palette
	 *
	 * Deletes a color palette from the database.
	 *
	 * @param int $id Palette ID.
	 * @return int|false The number of rows deleted, or false on error.
	 * @since 1.0.0
	 */
	public function delete_palette( int $id ): int|false {
		global $wpdb;
		$table_name = $wpdb->prefix . 'gl_color_palettes';

		return $wpdb->delete(
			$table_name,
			array( 'id' => $id ),
			array( '%d' )
		);
	}
}
