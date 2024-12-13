<?php
/**
 * Template for the plugin settings page
 *
 * This template provides the interface for configuring plugin settings,
 * including storage methods, API configurations, and general preferences.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Admin\Views
 * @since 1.0.0
 */

defined('ABSPATH') || exit; ?>

<div class="wrap gl-color-palette-settings">
    <h1><?php esc_html_e('Color Palette Settings', 'gl-color-palette-generator'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('gl_color_palette_settings'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php esc_html_e('Storage Method', 'gl-color-palette-generator'); ?>
                </th>
                <td>
                    <select name="gl_color_palette_settings[storage_method]">
                        <option value="options" <?php selected($settings['storage_method'] ?? 'options', 'options'); ?>>
                            <?php esc_html_e('WordPress Options', 'gl-color-palette-generator'); ?>
                        </option>
                        <option value="database" <?php selected($settings['storage_method'] ?? 'options', 'database'); ?>>
                            <?php esc_html_e('Custom Database Table', 'gl-color-palette-generator'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Choose how to store color palettes. Changes require migration.', 'gl-color-palette-generator'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Default Export Format', 'gl-color-palette-generator'); ?>
                </th>
                <td>
                    <select name="gl_color_palette_settings[default_export]">
                        <option value="css" <?php selected($settings['default_export'] ?? 'css', 'css'); ?>>CSS</option>
                        <option value="scss" <?php selected($settings['default_export'] ?? 'css', 'scss'); ?>>SCSS</option>
                        <option value="json" <?php selected($settings['default_export'] ?? 'css', 'json'); ?>>JSON</option>
                        <option value="tailwind" <?php selected($settings['default_export'] ?? 'css', 'tailwind'); ?>>Tailwind</option>
                        <option value="bootstrap" <?php selected($settings['default_export'] ?? 'css', 'bootstrap'); ?>>Bootstrap</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <?php esc_html_e('Analysis Features', 'gl-color-palette-generator'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="gl_color_palette_settings[analysis_features][]"
                               value="contrast" <?php checked(in_array('contrast', $settings['analysis_features'] ?? [])); ?>>
                        <?php esc_html_e('Contrast Analysis', 'gl-color-palette-generator'); ?>
                    </label><br>
                    <label>
                        <input type="checkbox" name="gl_color_palette_settings[analysis_features][]"
                               value="harmony" <?php checked(in_array('harmony', $settings['analysis_features'] ?? [])); ?>>
                        <?php esc_html_e('Color Harmony', 'gl-color-palette-generator'); ?>
                    </label><br>
                    <label>
                        <input type="checkbox" name="gl_color_palette_settings[analysis_features][]"
                               value="accessibility" <?php checked(in_array('accessibility', $settings['analysis_features'] ?? [])); ?>>
                        <?php esc_html_e('Accessibility Checks', 'gl-color-palette-generator'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div> 
