<?php
/**
 * Main admin page template
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gl-cpg-main">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="gl-cpg-generator">
        <div class="gl-cpg-prompt">
            <h2><?php esc_html_e('Generate New Palette', 'gl-color-palette-generator'); ?></h2>
            <form id="gl-cpg-generate-form">
                <textarea
                    name="prompt"
                    id="gl-cpg-prompt"
                    placeholder="<?php esc_attr_e('Describe your desired color palette...', 'gl-color-palette-generator'); ?>"
                    rows="3"
                ></textarea>
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Generate', 'gl-color-palette-generator'); ?>
                </button>
            </form>
        </div>

        <div id="gl-cpg-preview" class="gl-cpg-preview hidden">
            <h3><?php esc_html_e('Generated Palette', 'gl-color-palette-generator'); ?></h3>
            <div class="gl-cpg-colors"></div>
            <form id="gl-cpg-save-form">
                <input
                    type="text"
                    name="palette_name"
                    placeholder="<?php esc_attr_e('Palette Name', 'gl-color-palette-generator'); ?>"
                    required
                >
                <button type="submit" class="button button-secondary">
                    <?php esc_html_e('Save Palette', 'gl-color-palette-generator'); ?>
                </button>
            </form>
        </div>
    </div>

    <div class="gl-cpg-saved">
        <h2><?php esc_html_e('Saved Palettes', 'gl-color-palette-generator'); ?></h2>
        <div class="gl-cpg-palettes">
            <?php if (empty($palettes)) : ?>
                <p><?php esc_html_e('No saved palettes yet.', 'gl-color-palette-generator'); ?></p>
            <?php else : ?>
                <?php foreach ($palettes as $palette) : ?>
                    <div class="gl-cpg-palette" data-id="<?php echo esc_attr($palette['id']); ?>">
                        <h4><?php echo esc_html($palette['name']); ?></h4>
                        <div class="gl-cpg-colors">
                            <?php foreach ($palette['colors'] as $color) : ?>
                                <div class="gl-cpg-color" style="background-color: <?php echo esc_attr($color); ?>">
                                    <span class="gl-cpg-color-hex"><?php echo esc_html($color); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="gl-cpg-delete button button-link-delete">
                            <?php esc_html_e('Delete', 'gl-color-palette-generator'); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
