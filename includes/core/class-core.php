<?php
/**
 * Core Plugin Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

use GL_Color_Palette_Generator\Admin\Admin_Interface;
use GL_Color_Palette_Generator\Admin\Admin_Notices;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Color_Management\Color_Wheel;
use GL_Color_Palette_Generator\Color_Management\Color_Metrics;
use GL_Color_Palette_Generator\Color_Management\Color_Accessibility;
use GL_Color_Palette_Generator\Color_Management\Color_Palette_Generator;
use GL_Color_Palette_Generator\Color_Management\Color_Exporter;
use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\Utils\Error_Handler;
use GL_Color_Palette_Generator\Utils\Cache_Manager;
use GL_Color_Palette_Generator\Providers\AI_Provider_Factory;
use GL_Color_Palette_Generator\Types\Provider_Config;
use GL_Color_Palette_Generator\Interfaces\Plugin_Lifecycle;
use GL_Color_Palette_Generator\Interfaces\Component_Registry;
use GL_Color_Palette_Generator\Interfaces\WordPress_Integration;

/**
 * Main plugin core class
 */
class Core implements Plugin_Lifecycle, Component_Registry, WordPress_Integration {
	/**
	 * Registered components
	 *
	 * @var array<string, object>
	 */
	private array $components = array();

	/**
	 * Settings manager instance
	 */
	private Settings_Manager $settings;

	/**
	 * Error handler instance
	 */
	private Error_Handler $error_handler;

	/**
	 * Initialize the plugin
	 *
	 * @return bool Whether initialization was successful
	 */
	public function init(): bool {
		try {
			// Initialize core services
			$this->init_core_services();

			// Load and register components
			$this->load_components();

			// Register WordPress hooks
			$this->register_hooks();

			return true;
		} catch ( \Exception $e ) {
			$this->error_handler->log_error( $e->getMessage() );
			add_action( 'admin_notices', array( $this, 'show_initialization_error' ) );
			return false;
		}
	}

	/**
	 * Initialize core services required for the plugin
	 */
	private function init_core_services(): void {
		$this->settings      = new Settings_Manager();
		$this->error_handler = new Error_Handler();

		// Initialize settings
		$this->settings->init();
	}

	/**
	 * Load and register all plugin components
	 */
	private function load_components(): void {
		// Core utilities
		$color_utility = new Color_Utility();
		$this->register_component( 'color_utility', $color_utility );

		// Color management components
		$color_wheel         = new Color_Wheel( $color_utility );
		$color_metrics       = new Color_Metrics( $color_utility );
		$color_accessibility = new Color_Accessibility();

		$this->register_component( 'color_wheel', $color_wheel );
		$this->register_component( 'color_metrics', $color_metrics );
		$this->register_component( 'color_accessibility', $color_accessibility );
		$this->register_component( 'palette_generator', new Color_Palette_Generator( $color_utility ) );

		// Admin components
		$this->register_component( 'admin_interface', new Admin_Interface() );
		$this->register_component( 'admin_notices', new Admin_Notices() );

		// Cache and utilities
		$this->register_component( 'cache_manager', new Cache_Manager() );
		$this->register_component( 'color_exporter', new Color_Exporter() );

		// AI Provider
		$provider_factory = new AI_Provider_Factory();
		$provider_name    = $this->settings->get_option( 'ai_provider' ) ?? 'openai';
		$provider_config  = new Provider_Config(
			array(
				'api_key'     => $this->settings->get_option( 'ai_api_key' ),
				'model'       => $this->settings->get_option( 'ai_model' ),
				'temperature' => (float) ( $this->settings->get_option( 'ai_temperature' ) ?? 0.7 ),
			)
		);

		$provider = $provider_factory->get_provider( $provider_name, $provider_config );
		if ( ! ( $provider instanceof \WP_Error ) ) {
			$this->register_component( 'ai_provider', $provider );
		}
	}

	/**
	 * Register WordPress hooks
	 */
	public function register_hooks(): void {
		// Admin hooks
		add_action( 'admin_menu', array( $this->get_component( 'admin_interface' ), 'register_menus' ) );
		add_action( 'admin_notices', array( $this->get_component( 'admin_notices' ), 'display_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this->get_component( 'admin_interface' ), 'enqueue_assets' ) );

		// AJAX handlers
		add_action( 'wp_ajax_gl_generate_palette', array( $this->get_component( 'palette_generator' ), 'ajax_generate' ) );
		add_action( 'wp_ajax_gl_analyze_palette', array( $this->get_component( 'color_metrics' ), 'analyze_palette' ) );
		add_action( 'wp_ajax_gl_export_palette', array( $this->get_component( 'color_exporter' ), 'ajax_export' ) );

		// REST API
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Cron jobs
		add_action( 'gl_daily_cleanup', array( $this->get_component( 'cache_manager' ), 'cleanup' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes(): void {
		foreach ( array( 'palette_generator', 'color_metrics', 'color_exporter' ) as $component ) {
			if ( $this->has_component( $component ) ) {
				$this->get_component( $component )->register_routes();
			}
		}
	}

	/**
	 * Component Registry Implementation
	 */
	public function register_component( string $name, object $instance ): void {
		$this->components[ $name ] = $instance;
	}

	public function get_component( string $name ): ?object {
		return $this->components[ $name ] ?? null;
	}

	public function has_component( string $name ): bool {
		return isset( $this->components[ $name ] );
	}

	/**
	 * Plugin Lifecycle Implementation
	 */
	public function activate(): void {
		global $wpdb;

		// Create tables
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Color palettes table
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gl_color_palettes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            colors text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		dbDelta( $sql );

		// Set default options
		$default_options = array(
			'ai_provider'    => 'openai',
			'cache_duration' => 86400, // 24 hours
			'max_palettes'   => 100,
			'default_format' => 'hex',
		);

		foreach ( $default_options as $key => $value ) {
			if ( get_option( "gl_cpg_{$key}" ) === false ) {
				add_option( "gl_cpg_{$key}", $value );
			}
		}

		// Clear rewrite rules
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		// Clear scheduled events
		wp_clear_scheduled_hook( 'gl_daily_cleanup' );

		// Clear rewrite rules
		flush_rewrite_rules();
	}

	public function uninstall(): void {
		// This is typically called from uninstall.php
		global $wpdb;

		// Remove tables
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gl_color_palettes" );

		// Remove options
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'gl_cpg_%'" );
	}

	/**
	 * Display initialization error
	 */
	public function show_initialization_error(): void {
		$errors = $this->error_handler->get_errors();
		if ( $errors !== array() ) {
			echo '<div class="error"><p>';
			echo '<strong>GL Color Palette Generator Error:</strong><br>';
			echo wp_kses_post( implode( '<br>', $errors ) );
			echo '</p></div>';
		}
	}
}
