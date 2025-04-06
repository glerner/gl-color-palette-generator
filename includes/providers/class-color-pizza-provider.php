<?php
/**
 * Color Pizza Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * Color Pizza Provider implementation
 */
class Color_Pizza_Provider extends AI_Provider_Base {
	/** @var string */
	private $api_key;

	/**
	 * Constructor
	 *
	 * @param Provider_Config|null $config Provider configuration
	 */
	public function __construct( ?Provider_Config $config = null ) {
		parent::__construct( $config );
		$config        = $config ?? new Provider_Config();
		$this->api_key = $config->get_api_key();
	}

	/**
	 * Validate provider credentials
	 *
	 * @return bool|WP_Error True if valid, WP_Error otherwise
	 */
	public function validate_credentials(): bool|WP_Error {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', 'Color Pizza API key is required' );
		}

		// Make a simple API call to validate the key
		$response = wp_remote_get(
			'https://api.color.pizza/v1/colors/1',
			array(
				'headers' => array(
					'Authorization' => "Bearer {$this->api_key}",
					'Content-Type'  => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			$body  = wp_remote_retrieve_body( $response );
			$error = json_decode( $body, true );
			return new WP_Error(
				'invalid_credentials',
				$error['message'] ?? 'Invalid API key or configuration'
			);
		}

		return true;
	}

	/**
	 * Get provider requirements
	 *
	 * @return array Array of requirements
	 */
	public function get_requirements(): array {
		return array(
			'api_key' => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'Color Pizza API key',
				'link'        => 'https://api.color.pizza/',
			),
		);
	}

	/**
	 * Generate color palette
	 *
	 * @param array $params Generation parameters
	 * @return array|WP_Error Generated colors or error
	 */
	public function generate_palette( array $params ): array|WP_Error {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', 'Color Pizza API key is required' );
		}

		$response = wp_remote_get(
			'https://api.color.pizza/v1/colors/' . $params['num_colors'] ?? 5,
			array(
				'headers' => array(
					'Authorization' => "Bearer {$this->api_key}",
					'Content-Type'  => 'application/json',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['colors'] ) ) {
			return new WP_Error( 'invalid_response', 'Invalid response from Color Pizza' );
		}

		try {
			$colors = array_map(
				function ( $color ) {
					return $color['hex'];
				},
				$data['colors']
			);
			return $this->validate_colors( $colors );
		} catch ( \Exception $e ) {
			return new WP_Error( 'parse_error', 'Failed to parse Color Pizza response' );
		}
	}

	/**
	 * Get provider name
	 *
	 * @return string Provider name
	 */
	public function get_name(): string {
		return 'color-pizza';
	}

	/**
	 * Get provider display name
	 *
	 * @return string Provider display name
	 */
	public function get_display_name(): string {
		return 'Color Pizza';
	}

	/**
	 * Get provider capabilities
	 *
	 * @return array Provider capabilities
	 */
	public function get_capabilities(): array {
		return array(
			'max_colors'         => 100,
			'supports_streaming' => false,
			'supports_batch'     => false,
		);
	}

	/**
	 * Validate generated colors
	 *
	 * @param array $colors Colors to validate
	 * @return array Validated colors
	 * @throws \Exception If colors are invalid
	 */
	private function validate_colors( $colors ) {
		if ( ! is_array( $colors ) ) {
			throw new \Exception( 'Invalid colors array' );
		}

		foreach ( $colors as $color ) {
			if ( ! preg_match( '/^#[0-9A-F]{6}$/i', $color ) ) {
				throw new \Exception( sprintf( 'Invalid color code: %s', $color ) );
			}
		}

		return $colors;
	}
}
