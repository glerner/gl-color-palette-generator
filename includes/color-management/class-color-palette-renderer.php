<?php
/**
 * Color Palette Renderer
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Renderer
 *
 * Renders color palettes in various visual formats:
 * - HTML swatches
 * - Grid layout
 * - Preview cards
 * - Color information displays
 *
 * @since 1.0.0
 */
class Color_Palette_Renderer {
	/**
	 * Render a palette as HTML
	 *
	 * @param Color_Palette $palette Palette to render
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	public function render( Color_Palette $palette, array $options = array() ): string {
		$default_options = array(
			'layout'      => 'swatches', // swatches, grid, cards
			'show_info'   => true,
			'show_names'  => true,
			'show_values' => true,
			'size'        => 'medium', // small, medium, large
			'class'       => '',
		);

		$options = wp_parse_args( $options, $default_options );
		$method  = "render_{$options['layout']}";

		if ( ! method_exists( $this, $method ) ) {
			return '';
		}

		$wrapper_class  = 'gl-color-palette';
		$wrapper_class .= ' gl-color-palette--' . $options['layout'];
		$wrapper_class .= ' gl-color-palette--' . $options['size'];
		$wrapper_class .= ' ' . $options['class'];

		$html = sprintf(
			'<div class="%s" data-palette-id="%s">',
			esc_attr( $wrapper_class ),
			esc_attr( $palette->get_metadata( 'id' ) ?? '' )
		);

		if ( $options['show_info'] ) {
			$html .= $this->render_palette_info( $palette );
		}

		$html .= $this->$method( $palette, $options );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render palette as swatches
	 *
	 * @param Color_Palette $palette Palette to render
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	private function render_swatches( Color_Palette $palette, array $options ): string {
		$html = '<div class="gl-color-palette__swatches">';

		foreach ( $palette->get_colors() as $index => $color ) {
			$html .= $this->render_swatch( $color, $index, $palette, $options );
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render palette as grid
	 *
	 * @param Color_Palette $palette Palette to render
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	private function render_grid( Color_Palette $palette, array $options ): string {
		$html = '<div class="gl-color-palette__grid">';

		foreach ( $palette->get_colors() as $index => $color ) {
			$html .= sprintf(
				'<div class="gl-color-palette__grid-item" style="background-color: %s;">',
				esc_attr( $color )
			);

			if ( $options['show_info'] ) {
				$html .= $this->render_color_info( $color, $index, $palette, $options );
			}

			$html .= '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render palette as cards
	 *
	 * @param Color_Palette $palette Palette to render
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	private function render_cards( Color_Palette $palette, array $options ): string {
		$html = '<div class="gl-color-palette__cards">';

		foreach ( $palette->get_colors() as $index => $color ) {
			$html .= '<div class="gl-color-palette__card">';
			$html .= $this->render_swatch( $color, $index, $palette, $options );
			$html .= $this->render_color_details( $color, $index, $palette, $options );
			$html .= '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render individual swatch
	 *
	 * @param string        $color   Color hex value
	 * @param int           $index   Color index
	 * @param Color_Palette $palette Palette object
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	private function render_swatch( string $color, int $index, Color_Palette $palette, array $options ): string {
		$html = sprintf(
			'<div class="gl-color-palette__swatch" style="background-color: %s;" data-color="%s">',
			esc_attr( $color ),
			esc_attr( $color )
		);

		if ( $options['show_info'] ) {
			$html .= $this->render_color_info( $color, $index, $palette, $options );
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render color information
	 *
	 * @param string        $color   Color hex value
	 * @param int           $index   Color index
	 * @param Color_Palette $palette Palette object
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	private function render_color_info( string $color, int $index, Color_Palette $palette, array $options ): string {
		$html = '<div class="gl-color-palette__info">';

		if ( $options['show_names'] ) {
			$role  = array_keys( $palette->get_colors() )[ $index ] ?? '';
			$html .= sprintf(
				'<div class="gl-color-palette__role">%s</div>',
				esc_html( ucfirst( $role ) )
			);
		}

		if ( $options['show_values'] ) {
			$html .= sprintf(
				'<div class="gl-color-palette__value">%s</div>',
				esc_html( $color )
			);
		}

		// Add AI-generated descriptions if available
		$palette_description = get_option( 'gl_cpg_last_palette_description' );
		if ( $palette_description && isset( $palette_description['colors'][ $role ] ) ) {
			$color_data = $palette_description['colors'][ $role ];
			if ( ! empty( $color_data['name'] ) ) {
				$html .= sprintf(
					'<div class="gl-color-palette__artistic-name">%s</div>',
					esc_html( $color_data['name'] )
				);
			}
			if ( ! empty( $color_data['emotion'] ) ) {
				$html .= sprintf(
					'<div class="gl-color-palette__emotion">%s</div>',
					esc_html( $color_data['emotion'] )
				);
			}
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render palette information
	 *
	 * @param Color_Palette $palette Palette to render info for
	 * @return string HTML output
	 */
	public function render_palette_info( Color_Palette $palette ): string {
		$html = '<div class="gl-color-palette__palette-info">';

		// Add AI-generated palette story if available
		$palette_description = get_option( 'gl_cpg_last_palette_description' );
		if ( $palette_description && ! empty( $palette_description['palette_story'] ) ) {
			$html .= '<div class="gl-color-palette__story">';
			$html .= '<h3>' . esc_html__( 'Palette Story', 'gl-color-palette-generator' ) . '</h3>';
			$html .= sprintf(
				'<p>%s</p>',
				esc_html( $palette_description['palette_story'] )
			);
			$html .= '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Render detailed color information
	 *
	 * @param string        $color   Color hex value
	 * @param int           $index   Color index
	 * @param Color_Palette $palette Palette object
	 * @param array         $options Rendering options
	 * @return string HTML output
	 */
	private function render_color_details( string $color, int $index, Color_Palette $palette, array $options ): string {
		$rgb = $this->hex_to_rgb( $color );

		$html = '<div class="gl-color-palette__color-details">';

		$html .= sprintf(
			'<div class="gl-color-palette__color-values">
                <div>HEX: %s</div>
                <div>RGB: %d, %d, %d</div>
            </div>',
			esc_html( $color ),
			$rgb['r'],
			$rgb['g'],
			$rgb['b']
		);

		$html .= '</div>';
		return $html;
	}

	/**
	 * Generate color name
	 *
	 * @param string        $color   Color hex value
	 * @param int           $index   Color index
	 * @param Color_Palette $palette Palette object
	 * @return string Generated name
	 */
	private function generate_color_name( string $color, int $index, Color_Palette $palette ): string {
		$metadata = $palette->get_metadata();
		$theme    = sanitize_title( $metadata['theme'] ?? '' );

		if ( empty( $theme ) ) {
			return 'Color ' . ( $index + 1 );
		}

		return ucfirst( $theme ) . ' ' . ( $index + 1 );
	}

	/**
	 * Convert hex to RGB
	 *
	 * @param string $color Hex color
	 * @return array RGB values
	 */
	private function hex_to_rgb( string $color ): array {
		$color = ltrim( $color, '#' );
		return array(
			'r' => hexdec( substr( $color, 0, 2 ) ),
			'g' => hexdec( substr( $color, 2, 2 ) ),
			'b' => hexdec( substr( $color, 4, 2 ) ),
		);
	}
}
