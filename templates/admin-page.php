<?php
/**
 * Admin page template for Color Palette Generator
 *
 * @package GLColorPalette
 */

defined('ABSPATH') || exit;
?>

<div class="wrap gl-color-palette">
    <h1><?php esc_html_e('Color Palette Generator', 'gl-color-palette-generator'); ?></h1>

    <div class="gl-color-palette__container">
        <div class="gl-color-palette__questionnaire">
            <form id="gl-color-palette-form">
                <?php wp_nonce_field('gl_color_palette_nonce', 'gl_nonce'); ?>

                <!-- Business Context -->
                <div class="gl-section">
                    <h2><?php esc_html_e('Business Context', 'gl-color-palette-generator'); ?></h2>

                    <div class="gl-field">
                        <label for="industry_type">
                            <?php esc_html_e('Industry', 'gl-color-palette-generator'); ?>
                        </label>
                        <select name="context[industry_type]" id="industry_type" required>
                            <option value=""><?php esc_html_e('Select industry...', 'gl-color-palette-generator'); ?></option>
                            <option value="technology"><?php esc_html_e('Technology', 'gl-color-palette-generator'); ?></option>
                            <option value="healthcare"><?php esc_html_e('Healthcare', 'gl-color-palette-generator'); ?></option>
                            <option value="education"><?php esc_html_e('Education', 'gl-color-palette-generator'); ?></option>
                            <option value="finance"><?php esc_html_e('Finance', 'gl-color-palette-generator'); ?></option>
                            <option value="retail"><?php esc_html_e('Retail', 'gl-color-palette-generator'); ?></option>
                            <option value="creative"><?php esc_html_e('Creative', 'gl-color-palette-generator'); ?></option>
                        </select>
                    </div>

                    <!-- Additional fields here -->
                </div>

                <!-- Psychological Impact -->
                <div class="gl-section">
                    <h2><?php esc_html_e('Psychological Impact', 'gl-color-palette-generator'); ?></h2>

                    <div class="gl-field">
                        <label for="first_impression">
                            <?php esc_html_e('Desired First Impression', 'gl-color-palette-generator'); ?>
                        </label>
                        <input type="text"
                               name="context[first_impression]"
                               id="first_impression"
                               required
                               placeholder="<?php esc_attr_e('e.g., trustworthy, innovative', 'gl-color-palette-generator'); ?>">
                    </div>

                    <!-- Additional fields here -->
                </div>

                <div class="gl-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Generate Palette', 'gl-color-palette-generator'); ?>
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
                    <?php esc_html_e('Save Palette', 'gl-color-palette-generator'); ?>
                </button>
                <button type="button" class="button gl-export-palette">
                    <?php esc_html_e('Export', 'gl-color-palette-generator'); ?>
                </button>
            </div>
        </div>
    </div>
</div> 
