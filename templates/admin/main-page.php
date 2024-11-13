<?php
/**
 * Main admin page template
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="gl-color-palette-generator">
        <div class="gl-color-palette-controls">
            <button type="button" class="button button-primary" id="generate-palette">
                <?php esc_html_e('Generate New Palette', 'gl-color-palette-generator'); ?>
            </button>

            <select id="palette-size">
                <?php for ($i = 2; $i <= 10; $i++) : ?>
                    <option value="<?php echo esc_attr($i); ?>" <?php selected(get_option('gl_color_palette_size', 5), $i); ?>>
                        <?php echo esc_html($i); ?> <?php esc_html_e('Colors', 'gl-color-palette-generator'); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="gl-color-palette-display" id="palette-display"></div>

        <div class="gl-color-palette-actions">
            <input type="text" id="palette-name" placeholder="<?php esc_attr_e('Palette Name', 'gl-color-palette-generator'); ?>" />
            <button type="button" class="button" id="save-palette">
                <?php esc_html_e('Save Palette', 'gl-color-palette-generator'); ?>
            </button>
        </div>
    </div>
</div> 
