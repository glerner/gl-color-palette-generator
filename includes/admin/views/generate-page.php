<?php
/**
 * Template for the palette generation page
 *
 * This template provides the interface for creating and editing color palettes,
 * including manual selection, image extraction, and harmony-based generation.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Admin\Views
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Ensure $palette is defined
$palette = isset( $palette ) ? $palette : null;
?>

<div class="wrap gl-color-palette-generate">
	<h1>
		<?php
		echo $palette
			? esc_html__( 'Edit Palette', 'gl-color-palette-generator' )
			: esc_html__( 'Generate New Palette', 'gl-color-palette-generator' );
		?>
	</h1>

	<div class="gl-color-palette-workspace">
		<div class="gl-color-palette-tools">
			<div class="gl-tool-section">
				<h3><?php esc_html_e( 'Generation Method', 'gl-color-palette-generator' ); ?></h3>
				<select id="gl-generation-method">
					<option value="manual"><?php esc_html_e( 'Manual Selection', 'gl-color-palette-generator' ); ?></option>
					<option value="image"><?php esc_html_e( 'Extract from Image', 'gl-color-palette-generator' ); ?></option>
					<option value="harmony"><?php esc_html_e( 'Color Harmony', 'gl-color-palette-generator' ); ?></option>
				</select>
			</div>

			<div class="gl-tool-section" id="gl-manual-tools">
				<h3><?php esc_html_e( 'Colors', 'gl-color-palette-generator' ); ?></h3>
				<div class="gl-color-list">
					<?php
					$colors = $palette ? $palette->get_colors() : array( '#000000' );
					foreach ( $colors as $index => $color ) :
						?>
						<div class="gl-color-input">
							<input type="color" value="<?php echo esc_attr( $color ); ?>"
									class="gl-color-picker" data-index="<?php echo esc_attr( $index ); ?>">
							<input type="text" value="<?php echo esc_attr( $color ); ?>"
									class="gl-color-hex" data-index="<?php echo esc_attr( $index ); ?>">
							<button type="button" class="gl-remove-color" <?php echo count( $colors ) <= 1 ? 'disabled' : ''; ?>>
								&times;
							</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button gl-add-color">
					<?php esc_html_e( 'Add Color', 'gl-color-palette-generator' ); ?>
				</button>
			</div>

			<div class="gl-tool-section" id="gl-image-tools" style="display: none;">
				<h3><?php esc_html_e( 'Image Upload', 'gl-color-palette-generator' ); ?></h3>
				<input type="file" id="gl-image-upload" accept="image/*">
				<div id="gl-image-preview"></div>
			</div>

			<div class="gl-tool-section" id="gl-harmony-tools" style="display: none;">
				<h3><?php esc_html_e( 'Harmony Settings', 'gl-color-palette-generator' ); ?></h3>
				<select id="gl-harmony-type">
					<option value="complementary"><?php esc_html_e( 'Complementary', 'gl-color-palette-generator' ); ?></option>
					<option value="analogous"><?php esc_html_e( 'Analogous', 'gl-color-palette-generator' ); ?></option>
					<option value="triadic"><?php esc_html_e( 'Triadic', 'gl-color-palette-generator' ); ?></option>
					<option value="split-complementary"><?php esc_html_e( 'Split Complementary', 'gl-color-palette-generator' ); ?></option>
				</select>
				<input type="color" id="gl-base-color" value="#000000">
			</div>
		</div>

		<div class="gl-color-palette-preview">
			<h3><?php esc_html_e( 'Preview', 'gl-color-palette-generator' ); ?></h3>
			<div class="gl-preview-swatches"></div>
			<button type="button" class="button button-primary gl-analyze-palette">
				<?php esc_html_e( 'Analyze Palette', 'gl-color-palette-generator' ); ?>
			</button>
		</div>

		<div class="gl-color-palette-analysis"></div>
	</div>

	<div class="gl-color-palette-save">
		<input type="text" id="gl-palette-name" placeholder="<?php esc_attr_e( 'Palette Name', 'gl-color-palette-generator' ); ?>"
				value="<?php echo esc_attr( $palette ? $palette->get_metadata( 'name' ) : '' ); ?>">
		<button type="button" class="button button-primary gl-save-palette">
			<?php esc_html_e( 'Save Palette', 'gl-color-palette-generator' ); ?>
		</button>
	</div>
</div> 
