<?php
/**
 * Main admin page template
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap gl-cpg-main">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="gl-cpg-generator">
		<div class="gl-cpg-prompt">
			<h2><?php esc_html_e( 'Generate New Palette', 'gl-color-palette-generator' ); ?></h2>
			<form id="gl-cpg-generate-form">
				<textarea
					name="prompt"
					id="gl-cpg-prompt"
					placeholder="<?php esc_attr_e( 'Describe your desired color palette...', 'gl-color-palette-generator' ); ?>"
					rows="3"
				></textarea>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Generate', 'gl-color-palette-generator' ); ?>
				</button>
			</form>
		</div>

		<div id="gl-cpg-preview" class="gl-cpg-preview hidden">
			<h3><?php esc_html_e( 'Generated Palette', 'gl-color-palette-generator' ); ?></h3>
			
			<?php
			$palette_description = get_option( 'gl_cpg_last_palette_description' );
			if ( $palette_description ) :
				?>
			<div class="gl-cpg-palette-story">
				<h4><?php esc_html_e( 'Palette Story', 'gl-color-palette-generator' ); ?></h4>
				<p><?php echo esc_html( $palette_description['palette_story'] ?? '' ); ?></p>
			</div>
			<?php endif; ?>

			<div class="gl-cpg-colors">
				<?php foreach ( array( 'primary', 'secondary', 'tertiary', 'accent' ) as $role ) : ?>
				<div class="gl-cpg-color <?php echo esc_attr( $role ); ?>">
					<div class="gl-cpg-color-preview"></div>
					<div class="gl-cpg-color-info">
						<span class="gl-cpg-color-hex"></span>
						<?php if ( $palette_description && isset( $palette_description['colors'][ $role ] ) ) : ?>
						<span class="gl-cpg-color-name">
							<?php echo esc_html( $palette_description['colors'][ $role ]['name'] ?? '' ); ?>
						</span>
						<p class="gl-cpg-color-emotion">
							<?php echo esc_html( $palette_description['colors'][ $role ]['emotion'] ?? '' ); ?>
						</p>
						<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<form id="gl-cpg-save-form">
				<input
					type="text"
					name="palette_name"
					placeholder="<?php esc_attr_e( 'Palette Name', 'gl-color-palette-generator' ); ?>"
					required
				>
				<button type="submit" class="button button-secondary">
					<?php esc_html_e( 'Save Palette', 'gl-color-palette-generator' ); ?>
				</button>
			</form>
		</div>
	</div>

	<div class="gl-cpg-saved">
		<h2><?php esc_html_e( 'Saved Palettes', 'gl-color-palette-generator' ); ?></h2>
		<div class="gl-cpg-palettes">
			<?php if ( empty( $palettes ) ) : ?>
				<p><?php esc_html_e( 'No saved palettes yet.', 'gl-color-palette-generator' ); ?></p>
			<?php else : ?>
				<?php foreach ( $palettes as $palette ) : ?>
					<div class="gl-cpg-palette" data-id="<?php echo esc_attr( $palette['id'] ); ?>">
						<h4><?php echo esc_html( $palette['name'] ); ?></h4>
						<div class="gl-cpg-colors">
							<?php foreach ( $palette['colors'] as $color ) : ?>
								<div class="gl-cpg-color" style="background-color: <?php echo esc_attr( $color ); ?>">
									<span class="gl-cpg-color-hex"><?php echo esc_html( $color ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
						<button class="gl-cpg-delete button button-link-delete">
							<?php esc_html_e( 'Delete', 'gl-color-palette-generator' ); ?>
						</button>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="gl-cpg-tools-section">
		<h2><?php esc_html_e( 'Palette Tools', 'gl-color-palette-generator' ); ?></h2>

		<!-- Export/Import Section -->
		<div class="gl-cpg-export-import">
			<h3><?php esc_html_e( 'Export/Import', 'gl-color-palette-generator' ); ?></h3>
			<div class="gl-cpg-button-group">
				<button class="button gl-cpg-export" data-format="json">
					<?php esc_html_e( 'Export JSON', 'gl-color-palette-generator' ); ?>
				</button>
				<button class="button gl-cpg-export" data-format="csv">
					<?php esc_html_e( 'Export CSV', 'gl-color-palette-generator' ); ?>
				</button>
				<button class="button gl-cpg-import">
					<?php esc_html_e( 'Import Palettes', 'gl-color-palette-generator' ); ?>
				</button>
			</div>
			<input type="file" id="gl-cpg-import-file" accept=".json,.csv" style="display: none;">
		</div>

		<!-- Accessibility Checker Section -->
		<div class="gl-cpg-accessibility">
			<h3><?php esc_html_e( 'Accessibility Checker', 'gl-color-palette-generator' ); ?></h3>
			<div class="gl-cpg-color-picker-group">
				<div class="gl-cpg-color-input">
					<label><?php esc_html_e( 'Text Color', 'gl-color-palette-generator' ); ?></label>
					<input type="color" id="gl-cpg-text-color" value="#000000">
				</div>
				<div class="gl-cpg-color-input">
					<label><?php esc_html_e( 'Background Color', 'gl-color-palette-generator' ); ?></label>
					<input type="color" id="gl-cpg-bg-color" value="#ffffff">
				</div>
				<button class="button gl-cpg-check-contrast">
					<?php esc_html_e( 'Check Contrast', 'gl-color-palette-generator' ); ?>
				</button>
			</div>
			<div id="gl-cpg-accessibility-results" class="gl-cpg-results hidden"></div>
		</div>
	</div>
</div>
