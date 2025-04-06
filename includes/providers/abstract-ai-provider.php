<?php
declare(strict_types=1);

/**
 * Abstract AI Provider Class
 *
 * Base implementation for AI providers with common functionality.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;
use GL_Color_Palette_Generator\Types\Color_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract AI Provider class
 */
abstract class Abstract_AI_Provider implements AI_Provider {
	/**
	 * Provider configuration
	 *
	 * @var array{
	 *     api_key: string,
	 *     organization?: string,
	 *     base_url?: string,
	 *     timeout?: int,
	 *     max_retries?: int
	 * }
	 */
	protected array $config;

	/**
	 * Constructor
	 *
	 * @param array{
	 *     api_key: string,
	 *     organization?: string,
	 *     base_url?: string,
	 *     timeout?: int,
	 *     max_retries?: int
	 * } $config Provider configuration
	 * @throws \InvalidArgumentException If config is invalid
	 */
	public function __construct( array $config ) {
		if ( ! isset( $config['api_key'] ) || ! is_string( $config['api_key'] ) ) {
			throw new \InvalidArgumentException( __( 'API key is required', 'gl-color-palette-generator' ) );
		}

		$this->config = array_merge(
			array(
				'timeout'     => 30,
				'max_retries' => 3,
			),
			$config
		);
	}

	/**
	 * Generate a color palette
	 *
	 * @param array $params Generation parameters
	 * @return array{colors: array<string>, metadata: array{theme: string, mood: string, description: string, provider: string, model?: string, timestamp: int}} Generated palette data
	 * @throws Palette_Generation_Exception If generation fails
	 * @throws \InvalidArgumentException If input parameters are invalid
	 */
	public function generate_palette( array $params ): array {
		// Validate input parameters
		if ( ! isset( $params['prompt'] ) || $params['prompt'] === '' ) {
			throw new \InvalidArgumentException( __( 'Prompt is required', 'gl-color-palette-generator' ) );
		}

		$num_colors = $params['num_colors'] ?? 5;
		if ( $num_colors < 2 || $num_colors > 10 ) {
			throw new \InvalidArgumentException(
				sprintf( __( 'Number of colors must be between 2 and 10, got %d', 'gl-color-palette-generator' ), $num_colors )
			);
		}

		// Validate provider is ready
		if ( ! isset( $this->config ) || $this->config === array() ) {
			throw new \InvalidArgumentException( __( 'Provider configuration is required', 'gl-color-palette-generator' ) );
		}

		try {
			$result = $this->generate_colors_internal( $params );
			return $this->validate_and_format_result( $result );
		} catch ( \Exception $e ) {
			throw new Palette_Generation_Exception(
				sprintf(
					__( 'Failed to generate palette: %s', 'gl-color-palette-generator' ),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Internal color generation method to be implemented by providers
	 *
	 * @param array $params Generation parameters
	 * @return array Raw provider response
	 * @throws \Exception If generation fails
	 */
	abstract protected function generate_colors_internal( array $params ): array;

	/**
	 * Validate and format the provider's response
	 *
	 * @param array $result Raw provider response
	 * @return array{colors: array<string>, metadata: array} Formatted palette data
	 * @throws \InvalidArgumentException If result format is invalid
	 */
	protected function validate_and_format_result( array $result ): array {
		if ( ! isset( $result['colors'] ) || ! is_array( $result['colors'] ) ) {
			throw new \InvalidArgumentException( __( 'Invalid response format: missing colors array', 'gl-color-palette-generator' ) );
		}

		foreach ( $result['colors'] as $color ) {
			if ( ! Color_Types::is_valid_hex_color( $color ) ) {
				throw new \InvalidArgumentException(
					sprintf( __( 'Invalid color code: %s', 'gl-color-palette-generator' ), $color )
				);
			}
		}

		if ( ! isset( $result['metadata'] ) || ! is_array( $result['metadata'] ) ) {
			throw new \InvalidArgumentException( __( 'Invalid response format: missing metadata', 'gl-color-palette-generator' ) );
		}

		$required_metadata = array( 'theme', 'mood', 'description' );
		foreach ( $required_metadata as $field ) {
			if ( ! isset( $result['metadata'][ $field ] ) || ! is_string( $result['metadata'][ $field ] ) ) {
				throw new \InvalidArgumentException(
					sprintf( __( 'Invalid metadata: missing or invalid %s', 'gl-color-palette-generator' ), $field )
				);
			}
		}

		// Add provider info and timestamp
		$result['metadata']['provider']  = $this->get_name();
		$result['metadata']['timestamp'] = time();

		return $result;
	}

	/**
	 * Check if provider is configured and ready
	 *
	 * @return bool True if ready, false otherwise
	 */
	public function is_ready(): bool {
		return isset( $this->config['api_key'] ) && $this->config['api_key'] !== '';
	}

	/**
	 * Validate provider options
	 *
	 * @param array $options Options to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validate_options( array $options ): bool {
		return Color_Types::is_valid_provider_options( $options );
	}

	/**
	 * Make HTTP request to provider API
	 *
	 * @param string $endpoint API endpoint
	 * @param array  $data     Request data
	 * @param string $method   HTTP method
	 * @return array Response data
	 * @throws \Exception If request fails
	 */
	protected function make_request( string $endpoint, array $data, string $method = 'POST' ): array {
		$url = rtrim( $this->config['base_url'] ?? '', '/' ) . '/' . ltrim( $endpoint, '/' );

		$args = array(
			'method'  => $method,
			'timeout' => $this->config['timeout'],
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->config['api_key'],
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( $data ),
		);

		if ( isset( $this->config['organization'] ) ) {
			$args['headers']['OpenAI-Organization'] = $this->config['organization'];
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( __( 'Invalid JSON response from API', 'gl-color-palette-generator' ) );
		}

		return $data;
	}
}
