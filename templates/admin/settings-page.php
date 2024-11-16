<?php
/**
 * Settings page template
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gl-cpg-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('gl_cpg_settings');
        do_settings_sections('gl_cpg_settings');
        submit_button();
        ?>
    </form>
</div>
