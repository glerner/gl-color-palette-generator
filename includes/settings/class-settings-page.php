<?php
namespace GL_Color_Palette_Generator;

class Color_Palette_Generator_Settings {
	private $options_group = 'color_palette_generator';
	private $options_page  = 'color-palette-generator-settings';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add AJAX handlers
		add_action( 'wp_ajax_clear_color_cache', array( $this, 'handle_clear_cache' ) );
	}

	public function add_settings_page() {
		add_submenu_page(
			'color-palette-generator', // parent slug
			'Settings', // page title
			'Settings', // menu title
			'manage_options', // capability
			$this->options_page, // menu slug
			array( $this, 'render_settings_page' ) // callback
		);
	}

	public function register_settings() {
		register_setting(
			'color_palette_generator_settings',
			'color_palette_generator_options',
			array( $this, 'validate_settings' )
		);

		// General Settings
		add_settings_section(
			'general_settings',
			__( 'General Settings', 'color-palette-generator' ),
			array( $this, 'render_general_section' ),
			'color-palette-generator-settings'
		);

		// API Settings
		add_settings_section(
			'api_settings',
			__( 'API Configuration', 'color-palette-generator' ),
			array( $this, 'render_api_section' ),
			'color-palette-generator-settings'
		);

		// Add settings fields
		$this->add_settings_fields();
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if settings were saved
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'color_palette_messages',
				'color_palette_message',
				'Settings Saved',
				'updated'
			);
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors( 'color_palette_messages' ); ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( $this->options_group );
				do_settings_sections( $this->options_page );
				submit_button( 'Save Settings' );
				?>
			</form>

			<div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
				<h2>API Status</h2>
				<?php $this->render_api_status(); ?>
			</div>
		</div>
		<?php
	}

	public function render_api_section() {
		echo '<p>Configure your API keys for enhanced color naming capabilities.</p>';
	}

	public function render_naming_section() {
		echo '<p>Choose how you want color names to be displayed.</p>';
	}

	public function render_cache_section() {
		echo '<p>Configure how long to cache color names to reduce API calls.</p>';
	}

	public function render_openai_api_field() {
		$value = get_option( 'color_palette_generator_openai_key' );
		?>
		<input type="password"
				id="openai_api_key"
				name="color_palette_generator_openai_key"
				value="<?php echo esc_attr( $value ); ?>"
				class="regular-text"
		/>
		<p class="description">
			Enter your OpenAI API key for AI-powered color naming.
			<a href="https://platform.openai.com/api-keys" target="_blank">Get an API key</a>
		</p>
		<?php
	}

	public function render_naming_preference_field() {
		$value = get_option( 'color_naming_preference', 'both' );
		?>
		<select name="color_naming_preference" id="naming_preference">
			<option value="descriptive" <?php selected( $value, 'descriptive' ); ?>>
				Descriptive Names Only
			</option>
			<option value="functional" <?php selected( $value, 'functional' ); ?>>
				Functional Names Only
			</option>
			<option value="both" <?php selected( $value, 'both' ); ?>>
				Both Descriptive and Functional
			</option>
		</select>
		<p class="description">
			Choose how color names should be displayed in your palette
		</p>
		<?php
	}

	public function render_cache_duration_field() {
		$value = get_option( 'color_palette_generator_cache_duration', 30 );
		?>
		<input type="number"
				id="cache_duration"
				name="color_palette_generator_cache_duration"
				value="<?php echo esc_attr( $value ); ?>"
				min="1"
				max="365"
				class="small-text"
		/>
		<p class="description">
			Number of days to cache color names (1-365 days)
		</p>
		<?php
	}

	private function render_api_status() {
		$openai_key = get_option( 'color_palette_generator_openai_key' );
		?>
		<table class="widefat striped" style="margin-top: 10px;">
			<tbody>
				<tr>
					<td><strong>ColorNames.org API:</strong></td>
					<td><?php $this->check_colornames_api(); ?></td>
				</tr>
				<tr>
					<td><strong>OpenAI API:</strong></td>
					<td><?php $this->check_openai_api( $openai_key ); ?></td>
				</tr>
				<tr>
					<td><strong>Cache Status:</strong></td>
					<td><?php $this->check_cache_status(); ?></td>
				</tr>
			</tbody>
		</table>
		<p>
			<button type="button" class="button" id="clear-cache">
				Clear Color Name Cache
			</button>
		</p>
		<?php
	}

	private function check_colornames_api() {
		$response = wp_remote_get( 'https://colornames.org/search/json/?hex=FF0000' );

		if ( is_wp_error( $response ) ) {
			echo '<span class="dashicons dashicons-warning" style="color: #dc3232;"></span> Error connecting to ColorNames.org';
		} else {
			echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Connected';
		}
	}

	private function check_openai_api( $api_key ) {
		if ( empty( $api_key ) ) {
			echo '<span class="dashicons dashicons-warning" style="color: #dc3232;"></span> API key not configured';
			return;
		}

		// Simple API check
		$response = wp_remote_get(
			'https://api.openai.com/v1/models',
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $api_key ),
			)
		);

		if ( is_wp_error( $response ) ) {
			echo '<span class="dashicons dashicons-warning" style="color: #dc3232;"></span> Error connecting to OpenAI';
		} else {
			$code = wp_remote_retrieve_response_code( $response );
			if ( $code === 200 ) {
				echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Connected';
			} else {
				echo '<span class="dashicons dashicons-warning" style="color: #dc3232;"></span> Invalid API key';
			}
		}
	}

	private function check_cache_status() {
		$cache_duration = get_option( 'color_palette_generator_cache_duration', 30 );
		printf(
			'Caching enabled (%d days) - %d cached colors',
			$cache_duration,
			$this->count_cached_colors()
		);
	}

	private function count_cached_colors() {
		global $wpdb;
		return $wpdb->get_var(
			"SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_transient_color_name_%'"
		);
	}

	/**
	 * Enqueue admin scripts and localize data
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( $hook !== 'color-palette-generator_page_color-palette-generator-settings' ) {
			return;
		}

		// Enqueue CSS
		wp_enqueue_style(
			'color-palette-settings',
			plugin_dir_url( __DIR__ ) . 'assets/css/admin-settings.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'color-palette-settings',
			plugin_dir_url( __DIR__ ) . 'assets/js/admin-settings.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'color-palette-settings',
			'colorPaletteSettings',
			array(
				'nonce'         => wp_create_nonce( 'color_palette_cache_nonce' ),
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'clearingCache' => __( 'Clearing cache...', 'color-palette-generator' ),
				'cacheCleared'  => __( 'Cache cleared successfully!', 'color-palette-generator' ),
				'errorClearing' => __( 'Error clearing cache', 'color-palette-generator' ),
			)
		);
	}

	/**
	 * Handle AJAX cache clearing
	 */
	public function handle_clear_cache() {
		// Verify nonce
		if ( ! check_ajax_referer( 'color_palette_cache_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => 'Invalid security token',
				)
			);
		}

		// Verify user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => 'Insufficient permissions',
				)
			);
		}

		try {
			$this->clear_color_cache();
			wp_send_json_success(
				array(
					'message' => 'Cache cleared successfully',
					'count'   => 0,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Clear all color name caches
	 */
	private function clear_color_cache() {
		global $wpdb;

		// Get all color name transients
		$transients = $wpdb->get_results(
			"SELECT option_name
            FROM $wpdb->options
            WHERE option_name LIKE '_transient_color_name_%'"
		);

		$cleared = 0;
		foreach ( $transients as $transient ) {
			$name = str_replace( '_transient_', '', $transient->option_name );
			if ( delete_transient( $name ) ) {
				++$cleared;
			}
		}

		// Also clear any timeout values
		$wpdb->query(
			"DELETE FROM $wpdb->options
            WHERE option_name LIKE '_transient_timeout_color_name_%'"
		);

		return $cleared;
	}

	/**
	 * Initialize settings page
	 */
	public function init_settings_page() {
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
	}

	/**
	 * Add settings fields
	 */
	private function add_settings_fields() {
		// General Settings Fields
		add_settings_field(
			'default_palette_size',
			__( 'Default Palette Size', 'color-palette-generator' ),
			array( $this, 'render_number_field' ),
			'color-palette-generator-settings',
			'general_settings',
			array(
				'label_for' => 'default_palette_size',
				'min'       => 3,
				'max'       => 10,
				'default'   => 5,
			)
		);

		// API Settings Fields
		add_settings_field(
			'api_provider',
			__( 'AI Provider', 'color-palette-generator' ),
			array( $this, 'render_select_field' ),
			'color-palette-generator-settings',
			'api_settings',
			array(
				'label_for' => 'api_provider',
				'options'   => $this->get_available_providers(),
			)
		);
	}
}
