<?php
/**
 * Admin page template for Color Palette Generator
 *
 * @package GL_Color_Palette_Generator
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap gl-color-palette">
	<h1><?php esc_html_e( 'Color Palette Generator', 'gl-color-palette-generator' ); ?></h1>

	<div class="gl-color-palette__container">
		<div class="gl-color-palette__questionnaire">
			<form id="gl-color-palette-form">
				<?php wp_nonce_field( 'gl_color_palette_nonce', 'gl_nonce' ); ?>

				<!-- Business Context -->
				<div class="gl-section">
					<h2><?php esc_html_e( 'Business Context', 'gl-color-palette-generator' ); ?></h2>

					<div class="gl-field">
						<label for="business_type">
							<?php esc_html_e( 'Tell us about your business', 'gl-color-palette-generator' ); ?>
							<span class="description"><?php esc_html_e( 'What makes your business unique? What do you offer?', 'gl-color-palette-generator' ); ?></span>
						</label>
						<textarea name="context[business_type]" id="business_type" rows="3" required></textarea>
					</div>
				</div>

				<!-- Target Audience -->
				<div class="gl-section">
					<h2><?php esc_html_e( 'Target Audience', 'gl-color-palette-generator' ); ?></h2>

					<div class="gl-field">
						<label for="target_audience">
							<?php esc_html_e( 'Describe your ideal customer', 'gl-color-palette-generator' ); ?>
							<span class="description"><?php esc_html_e( 'Who are they? What do they value? What attracts them to your business?', 'gl-color-palette-generator' ); ?></span>
						</label>
						<textarea name="context[target_audience]" id="target_audience" rows="3" required></textarea>
					</div>
				</div>

				<!-- Desired Mood -->
				<div class="gl-section">
					<h2><?php esc_html_e( 'Website Mood', 'gl-color-palette-generator' ); ?></h2>

					<div class="gl-field">
						<label for="desired_mood">
							<?php esc_html_e( 'What mood should your website evoke?', 'gl-color-palette-generator' ); ?>
							<span class="description"><?php esc_html_e( 'How should visitors feel when they visit your site? What emotions do you want to inspire?', 'gl-color-palette-generator' ); ?></span>
						</label>
						<textarea name="context[desired_mood]" id="desired_mood" rows="3" required></textarea>
					</div>
				</div>

				<div class="gl-actions">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Generate Palette', 'gl-color-palette-generator' ); ?>
					</button>
				</div>
			</form>
		</div>

		<div class="gl-color-palette__results" style="display: none;">
			<div class="gl-palette-preview"></div>
			<div class="gl-palette-analysis"></div>
			<div class="gl-palette-code"></div>

			<div class="gl-actions">
				<button type="button" class="button gl-save-palette">
					<?php esc_html_e( 'Save Palette', 'gl-color-palette-generator' ); ?>
				</button>
				<button type="button" class="button gl-export-palette">
					<?php esc_html_e( 'Export', 'gl-color-palette-generator' ); ?>
				</button>
			</div>
		</div>
	</div>
</div> 
