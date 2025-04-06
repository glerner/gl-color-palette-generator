<?php
namespace GL_Color_Palette_Generator\Traits;

use GL_Color_Palette_Generator\Color_Management\Color_Shade_Generator;

/**
 * Trait Color_Shade_Generator_Trait
 *
 * Provides methods for generating accessible tints and shades of colors.
 * This is distinct from WordPress theme style variations - it focuses on
 * creating lighter and darker versions of a single color that meet WCAG standards.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
trait Color_Shade_Generator_Trait {
	/**
	 * Generate accessible tints and shades
	 *
	 * @param string $color Base color in hex format
	 * @param array  $options Optional. Generation options.
	 * @return array Array of accessible tints and shades (lighter, light, dark, darker)
	 */
	protected function generate_accessible_shades( $color, $options = array() ) {
		if ( ! isset( $this->shade_generator ) ) {
			throw new \RuntimeException( 'ColorShadeGenerator dependency not set. Make sure to initialize it in the constructor.' );
		}

		$result = $this->shade_generator->generate_tints_and_shades(
			$color,
			array_merge(
				array(
					'contrast_level' => 'AA',
					'small_text'     => true,
					'include_base'   => true,
				),
				$options
			)
		);

		return $result['variations'];
	}
}
