<?php declare(strict_types=1);
/**
 * AI Provider Factory
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

use GL_Color_Palette_Generator\Providers\AI_Provider;
use GL_Color_Palette_Generator\Providers\Anthropic_Provider;
use GL_Color_Palette_Generator\Providers\Azure_OpenAI_Provider;
use GL_Color_Palette_Generator\Providers\Cohere_Provider;
use GL_Color_Palette_Generator\Providers\Color_Pizza_Provider;
use GL_Color_Palette_Generator\Providers\HuggingFace_Provider;
use GL_Color_Palette_Generator\Providers\OpenAI_Provider;
use GL_Color_Palette_Generator\Providers\Palm_Provider;
use GL_Color_Palette_Generator\Types\Provider_Config;
use WP_Error;

/**
 * AI Provider Factory
 *
 * Creates and configures AI provider instances based on type and credentials.
 *
 * @since 1.0.0
 */
class AI_Provider_Factory {
	/**
	 * Get provider instance
	 *
	 * @param string          $provider_name Provider name
	 * @param Provider_Config $config Provider configuration
	 * @return AI_Provider|WP_Error Provider instance or error if not found
	 */
	public function get_provider( string $provider_name, Provider_Config $config ): AI_Provider|\WP_Error {
		switch ( $provider_name ) {
			case 'anthropic':
				return new Anthropic_Provider( $config );
			case 'azure-openai':
				return new Azure_OpenAI_Provider( $config );
			case 'cohere':
				return new Cohere_Provider( $config );
			case 'color-pizza':
				return new Color_Pizza_Provider( $config );
			case 'huggingface':
				return new HuggingFace_Provider( $config );
			case 'openai':
				return new OpenAI_Provider( $config );
			case 'palm':
				return new Palm_Provider( $config );
			default:
				return new \WP_Error(
					'invalid_provider',
					sprintf( 'Invalid provider type: %s', $provider_name )
				);
		}
	}

	/**
	 * Get all registered providers
	 *
	 * @return array List of provider names
	 */
	public function get_registered_providers(): array {
		return array(
			'anthropic'    => 'Anthropic',
			'azure-openai' => 'Azure OpenAI',
			'cohere'       => 'Cohere',
			'color-pizza'  => 'Color Pizza',
			'huggingface'  => 'HuggingFace',
			'openai'       => 'OpenAI',
			'palm'         => 'PaLM',
		);
	}
}
