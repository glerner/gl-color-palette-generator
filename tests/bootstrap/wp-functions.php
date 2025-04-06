<?php
/**
 * WordPress Function Definitions for Testing
 *
 * Provides mock implementations of WordPress functions for unit and WP-Mock tests.
 * All functions are declared in root namespace to match WordPress core.
 *
 * Note: Translation, Escaping, and Hook functions require existence checks (!function_exists)
 * as they may be provided by WP_Mock in mock testing contexts.
 *
 * Functions are grouped by type:
 * - Translation Functions
 * - Escaping Functions
 * - Hook Functions
 * - Admin Functions
 * - Settings Functions
 * - Cache Functions
 * - Asset Functions
 * - Plugin Functions
 * - Transient Functions
 * - User Functions
 * - Filesystem Functions
 * - AJAX/JSON Functions
 * - Form Functions
 * - Security Functions
 * - Error Handling
 * - Scheduled Tasks
 * - WordPress Classes
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace {

	/*
	* All WordPress function mocks must be in root namespace to properly intercept calls.
	* See docs/development-guidelines.md for a grep command to find required functions.
	*/

	// Translation Functions
	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = 'default' ) {
			return $text; }
	}
	if ( ! function_exists( '_e' ) ) {
		function _e( $text, $domain = 'default' ) {
			echo $text; }
	}
	if ( ! function_exists( '_x' ) ) {
		function _x( $text, $context, $domain = 'default' ) {
			return $text; }
	}

	// Escaping Functions
	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
	}
	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
	}
	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text, $domain = 'default' ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); }
	}
	if ( ! function_exists( 'wp_kses_post' ) ) {
		function wp_kses_post( $data ) {
			return $data; }
	}
	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $str ) {
			return trim( strip_tags( $str ) ); }
	}

	// Hook Functions
	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			return true; }
	}
	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			return true; }
	}
	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $tag, $value, ...$args ) {
			return $value; }
	}
	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $tag, ...$args ) {
			return true; }
	}
	if ( ! function_exists( 'has_action' ) ) {
		function has_action( $tag, $function_to_check = false ) {
			return false; }
	}

	// Admin Functions
	function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
		return true; }
	function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null ) {
		return true; }
	function admin_url( $path = '', $scheme = 'admin' ) {
		return 'http://example.com/wp-admin/' . ltrim( $path, '/' ); }
	function add_settings_error( $setting, $code, $message, $type = 'error' ) {
		return true; }
	function set_current_screen( $screen = '' ) {
		return true; }

	// Settings Functions
	function add_settings_section( $id, $title, $callback, $page ) {
		return true; }
	function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = array() ) {
		return true; }
	function add_option( $option, $value = '', $deprecated = '', $autoload = 'yes' ) {
		return true; }
	function get_option( $option, $default = false ) {
		return $default; }
	function delete_option( $option ) {
		return true; }
	function do_settings_sections( $page ) {
		return true; }
	function register_setting( $option_group, $option_name, $args = array() ) {
		return true; }
	function settings_fields( $option_group ) {
		return true; }
	function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {
		echo '<input type="submit" name="' . esc_attr( $name ) . '" value="' . esc_attr( $text ) . '" class="button button-' . esc_attr( $type ) . '">';
	}
	function update_option( $option, $value, $autoload = null ) {
		return true; }

	// Cache Functions
	function wp_cache_add_global_groups( $groups ) {
		return true; }
	function wp_cache_delete_group( $group ) {
		return true; }
	function wp_cache_flush() {
		return true; }
	function wp_cache_init() {
		return true; }
	function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
		return true; }

	// Asset Functions
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		return true; }
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		return true; }
	function wp_localize_script( $handle, $object_name, $l10n ) {
		return true; }
	function wp_script_is( $handle, $list = 'enqueued' ) {
		return true; }
	function wp_style_is( $handle, $list = 'enqueued' ) {
		return true; }

	// Plugin Functions
	function add_shortcode( $tag, $callback ) {
		return true; }
	function check_ajax_referer( $action = -1, $query_arg = false, $die = true ) {
		return true; }
	function deactivate_plugins( $plugins, $silent = false, $network_wide = null ) {
		return true; }
	function flush_rewrite_rules( $hard = true ) {
		return true; }
	function plugin_basename( $file ) {
		return basename( $file ); }
	function plugin_dir_url( $file ) {
		return 'http://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/'; }
	function plugins_url( $path = '', $plugin = '' ) {
		return 'http://example.com/wp-content/plugins/' . ltrim( $path, '/' ); }
	function register_activation_hook( $file, $callback ) {
		return true; }
	function register_deactivation_hook( $file, $callback ) {
		return true; }
	function register_rest_route( $namespace, $route, $args = array() ) {
		return true; }

	// Transient Functions
	function delete_transient( $transient ) {
		return true; }
	function get_transient( $transient ) {
		return false; }
	function set_transient( $transient, $value, $expiration = 0 ) {
		return true; }

	// User Functions
	function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
		return true; }

	// Filesystem Functions
	function wp_mkdir_p( $dir ) {
		return true; }
	function wp_upload_dir( $time = null, $create_dir = true, $refresh_cache = false ) {
		return array(
			'path'    => '/tmp',
			'url'     => 'http://example.com',
			'subdir'  => '',
			'basedir' => '/tmp',
			'baseurl' => 'http://example.com',
			'error'   => false,
		);
	}

	// AJAX/JSON Functions
	function wp_send_json_error( $data = null, $status_code = null ) {
		return true; }
	function wp_send_json_success( $data = null, $status_code = null ) {
		return true; }
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth ); }

	// Form Functions
	function selected( $selected, $current = true, $echo = true ) {
		$result = $selected == $current ? ' selected="selected"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
	function checked( $checked, $current = true, $echo = true ) {
		$result = $checked == $current ? ' checked="checked"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
	function disabled( $disabled, $current = true, $echo = true ) {
		$result = $disabled == $current ? ' disabled="disabled"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$parsed_args = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$parsed_args = $args;
		} else {
			parse_str( $args, $parsed_args );
		}
		return array_merge( $defaults, $parsed_args );
	}

	// Security Functions
	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		$nonce = '<input type="hidden" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . wp_create_nonce( $action ) . '" />';
		if ( $referer ) {
			$nonce .= wp_referer_field( false );
		}
		if ( $echo ) {
			echo $nonce;
		}
		return $nonce;
	}
	function wp_create_nonce( $action = -1 ) {
		return 'test-nonce'; }
	function wp_referer_field( $echo = true ) {
		$referer = '<input type="hidden" name="_wp_http_referer" value="" />';
		if ( $echo ) {
			echo $referer;
		}
		return $referer;
	}

	// Error Handling
	function wp_die( $message = '', $title = '', $args = array() ) {
		throw new \Exception( $message ); }

	// Scheduled Tasks
	function wp_clear_scheduled_hook( $hook ) {
		return true; }

	// WordPress Classes
	class WP_REST_Controller {
		public function register_routes() {}
		public function get_item_schema() {}
		public function get_collection_params() {
			return array(); }
		protected function prepare_item_for_response( $item, $request ) {
			return $item; }
		protected function prepare_items_for_response( $items, $request ) {
			return $items; }
		public function get_namespace() {
			return ''; }
		public function get_rest_base() {
			return ''; }
	}

	// end of namespace
}
