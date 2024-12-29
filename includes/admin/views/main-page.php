<?php
/**
 * Template for the main palette listing page
 *
 * This template displays the grid of saved color palettes and provides
 * options for managing them, including adding new palettes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Admin\Views
 * @since 1.0.0
 */

defined('ABSPATH') || exit; ?>

<div class="wrap gl-color-palette-main">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Color Palettes', 'gl-color-palette-generator'); ?>
    </h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=gl-color-palettes-generate')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'gl-color-palette-generator'); ?>
    </a>

    <?php if (!(isset($palettes) && count($palettes) > 0)): ?>
        <div class="gl-color-palette-empty">
            <p><?php esc_html_e('No palettes found. Create your first color palette!', 'gl-color-palette-generator'); ?></p>
        </div>
    <?php else: ?>
        <div class="gl-color-palette-grid">
            <?php foreach ($palettes as $palette): ?>
                <div class="gl-color-palette-card" data-id="<?php echo esc_attr($palette->get_metadata('id')); ?>">
                    <div class="gl-color-palette-preview">
                        <?php foreach ($palette->get_colors() as $color): ?>
                            <div class="gl-color-swatch" style="background-color: <?php echo esc_attr($color); ?>"></div>
                        <?php endforeach; ?>
                    </div>

                    <div class="gl-color-palette-info">
                        <h3><?php echo esc_html($palette->get_metadata('name') ?? __('Unnamed Palette', 'gl-color-palette-generator')); ?></h3>
                        <div class="gl-color-palette-meta">
                            <span class="colors-count">
                                <?php printf(
                                    esc_html(_n('%s color', '%s colors', count($palette->get_colors()), 'gl-color-palette-generator')),
                                    count($palette->get_colors())
                                ); ?>
                            </span>
                            <span class="created-date">
                                <?php echo esc_html(human_time_diff(strtotime($palette->get_metadata('created')), current_time('timestamp'))); ?>
                            </span>
                        </div>
                    </div>

                    <div class="gl-color-palette-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=gl-color-palettes-generate&palette_id=' . $palette->get_metadata('id'))); ?>"
                           class="button edit-palette">
                            <?php esc_html_e('Edit', 'gl-color-palette-generator'); ?>
                        </a>
                        <button type="button" class="button delete-palette">
                            <?php esc_html_e('Delete', 'gl-color-palette-generator'); ?>
                        </button>
                        <button type="button" class="button export-palette">
                            <?php esc_html_e('Export', 'gl-color-palette-generator'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="gl-color-palette-export-modal" class="gl-modal" style="display: none;">
    <div class="gl-modal-content">
        <span class="gl-modal-close">&times;</span>
        <h2><?php esc_html_e('Export Palette', 'gl-color-palette-generator'); ?></h2>

        <div class="gl-export-options">
            <select id="gl-export-format">
                <option value="css">CSS Variables</option>
                <option value="scss">SCSS Variables</option>
                <option value="json">JSON</option>
                <option value="tailwind">Tailwind Config</option>
                <option value="bootstrap">Bootstrap Variables</option>
            </select>

            <div class="gl-export-preview">
                <pre><code id="gl-export-content"></code></pre>
            </div>

            <button type="button" class="button button-primary" id="gl-copy-export">
                <?php esc_html_e('Copy to Clipboard', 'gl-color-palette-generator'); ?>
            </button>
        </div>
    </div>
</div> 
