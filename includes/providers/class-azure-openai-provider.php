<?php
/**
 * Azure OpenAI Provider
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Abstracts\AI_Provider_Base;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * Azure OpenAI Provider implementation
 */
class Azure_OpenAI_Provider extends AI_Provider_Base {
	/** @var string */
	private $api_key;

	/** @var string */
	private $endpoint;

	/** @var string */
	private $deployment;

	/** @var string */
	private $model = 'gpt-4';

	/**
	 * Constructor
	 *
	 * @param Provider_Config|null $config Provider configuration
	 */
	public function __construct( ?Provider_Config $config = null ) {
		parent::__construct( $config );
		$config           = $config ?? new Provider_Config();
		$this->api_key    = $config->get_api_key();
		$this->endpoint   = $config->get_endpoint();
		$this->deployment = $config->get_deployment();
		$this->model      = $config->get_model() ?? 'gpt-4';
	}

	/**
	 * Generate color palette
	 *
	 * @param array $params Generation parameters
	 * @return array|WP_Error Generated colors or error
	 */
	public function generate_palette( array $params ): array|WP_Error {
		if ( empty( $this->api_key ) || empty( $this->endpoint ) || empty( $this->deployment ) ) {
			return new WP_Error( 'missing_config', 'Azure OpenAI endpoint, API key and deployment ID are required' );
		}

		$prompt   = $this->build_prompt( $params );
		$response = wp_remote_post(
			"{$this->endpoint}/openai/deployments/{$this->deployment}/chat/completions?api-version=2023-05-15",
			array(
				'headers' => array(
					'api-key'      => $this->api_key,
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'       => $this->model,
						'messages'    => array(
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						),
						'temperature' => 0.7,
						'max_tokens'  => 100,
					)
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['choices'][0]['message']['content'] ) ) {
			return new WP_Error( 'invalid_response', 'Invalid response from Azure OpenAI' );
		}

		try {
			$colors = json_decode( $data['choices'][0]['message']['content'], true );
			return $this->validate_colors( $colors );
		} catch ( \Exception $e ) {
			return new WP_Error( 'parse_error', 'Failed to parse Azure OpenAI response' );
		}
	}

	/**
	 * Get provider name
	 *
	 * @return string Provider name
	 */
	public function get_name(): string {
		return 'azure-openai';
	}

	/**
	 * Get provider display name
	 *
	 * @return string Provider display name
	 */
	public function get_display_name(): string {
		return 'Azure OpenAI';
	}

	/**
	 * Get provider capabilities
	 *
	 * @return array Provider capabilities
	 */
	public function get_capabilities(): array {
		return array(
			'max_colors'         => 10,
			'supports_streaming' => true,
			'supports_batch'     => true,
		);
	}

	/**
	 * Validate provider credentials
	 *
	 * @return bool|WP_Error True if valid, WP_Error otherwise
	 */
	public function validate_credentials(): bool|WP_Error {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', 'Azure OpenAI API key is required' );
		}

		if ( empty( $this->endpoint ) ) {
			return new WP_Error( 'missing_endpoint', 'Azure OpenAI endpoint is required' );
		}

		if ( empty( $this->deployment ) ) {
			return new WP_Error( 'missing_deployment', 'Azure OpenAI deployment ID is required' );
		}

		// Make a simple API call to validate the credentials
		$response = wp_remote_post(
			"{$this->endpoint}/openai/deployments/{$this->deployment}/chat/completions?api-version=2023-05-15",
			array(
				'headers' => array(
					'api-key'      => $this->api_key,
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $this->model,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => 'Hi',
							),
						),
						'max_tokens' => 1,
					)
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
				$error['error']['message'] ?? 'Invalid API key or configuration'
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
			'api_key'    => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'Azure OpenAI API key',
				'link'        => 'https://portal.azure.com/',
			),
			'endpoint'   => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'Azure OpenAI endpoint URL',
			),
			'deployment' => array(
				'required'    => true,
				'type'        => 'string',
				'description' => 'Azure OpenAI deployment ID',
			),
			'model'      => array(
				'required'    => false,
				'type'        => 'string',
				'description' => 'Azure OpenAI model to use',
				'default'     => 'gpt-4',
				'options'     => array( 'gpt-4', 'gpt-35-turbo' ),
			),
		);
	}

	/**
	 * Build prompt for Azure OpenAI API
	 *
	 * @param array $params Generation parameters
	 * @return string Prompt
	 */
	private function build_prompt( array $params ): string {
		return sprintf(
			'Generate a color palette with %d colors based on this description: %s. Return only a JSON array of hex color codes.',
			$params['num_colors'] ?? 5,
			$params['prompt'] ?? ''
		);
	}

	/**
	 * Validate generated colors
	 *
	 * @param array $colors Colors to validate
	 * @return array Validated colors
	 * @throws \Exception If colors are invalid
	 */
	private function validate_colors( array $colors ): array {
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
