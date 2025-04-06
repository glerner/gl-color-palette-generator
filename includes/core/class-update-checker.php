<?php
/**
 * Update Checker Class
 *
 * Handles checking for plugin updates and notifying WordPress of available updates.
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
 * Update Checker class
 */
class Update_Checker {
	/**
	 * The update manifest URL
	 *
	 * @var string
	 */
	private $update_url;

	/**
	 * The main plugin file
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * The plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * The update data
	 *
	 * @var object|null
	 */
	private $update_data = null;

	/**
	 * Constructor
	 *
	 * @param string $update_url  The URL to check for updates
	 * @param string $plugin_file The main plugin file path
	 * @param string $plugin_slug The plugin slug
	 */
	public function __construct( $update_url, $plugin_file, $plugin_slug ) {
		$this->update_url  = $update_url;
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = $plugin_slug;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
	}

	/**
	 * Check for updates
	 *
	 * @param object $transient The WordPress update transient
	 * @return object Modified transient with our update data
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote_data = $this->get_remote_data();
		if ( ! $remote_data ) {
			return $transient;
		}

		$current_version = get_plugin_data( $this->plugin_file )['Version'];
		if ( version_compare( $current_version, $remote_data->version, '<' ) ) {
			$transient->response[ $this->plugin_slug ] = (object) array(
				'slug'          => $this->plugin_slug,
				'plugin'        => $this->plugin_slug,
				'new_version'   => $remote_data->version,
				'url'           => $remote_data->homepage,
				'package'       => $remote_data->download_url,
				'icons'         => $remote_data->icons ?? array(),
				'banners'       => $remote_data->banners ?? array(),
				'tested'        => $remote_data->tested ?? '',
				'requires_php'  => $remote_data->requires_php ?? '',
				'compatibility' => $remote_data->compatibility ?? new \stdClass(),
			);
		}

		return $transient;
	}

	/**
	 * Get plugin information for the WordPress updates screen
	 *
	 * @param false|object|array $result  The result object or array
	 * @param string             $action  The API action being performed
	 * @param object             $args    Plugin arguments
	 * @return false|object Plugin information
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( $args->slug !== $this->plugin_slug ) {
			return $result;
		}

		$remote_data = $this->get_remote_data();
		if ( ! $remote_data ) {
			return $result;
		}

		return (object) array(
			'name'           => $remote_data->name,
			'slug'           => $this->plugin_slug,
			'version'        => $remote_data->version,
			'author'         => $remote_data->author,
			'author_profile' => $remote_data->author_profile,
			'requires'       => $remote_data->requires,
			'tested'         => $remote_data->tested,
			'requires_php'   => $remote_data->requires_php,
			'sections'       => array(
				'description'  => $remote_data->sections->description,
				'installation' => $remote_data->sections->installation,
				'changelog'    => $remote_data->sections->changelog,
			),
			'download_link'  => $remote_data->download_url,
			'banners'        => $remote_data->banners ?? array(),
			'icons'          => $remote_data->icons ?? array(),
		);
	}

	/**
	 * Get remote update data
	 *
	 * @return object|false Remote update data or false on failure
	 */
	private function get_remote_data() {
		if ( $this->update_data !== null ) {
			return $this->update_data;
		}

		$response = wp_remote_get(
			$this->update_url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! is_object( $data ) ) {
			return false;
		}

		$this->update_data = $data;
		return $data;
	}
}
