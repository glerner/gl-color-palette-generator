<?php
/**
 * Plugin Name:       GL Color Palette Generator
 * Plugin URI:        https://github.com/GeorgeLerner/gl-color-palette-generator
 * Description:       Generate color palettes for your website
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            George Lerner
 * Author URI:        https://website-tech.glerner.com/
 * Text Domain:       gl-color-palette-generator
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package           GLColorPalette
 */

if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('GL_COLOR_PALETTE_VERSION', '1.0.0');
define('GL_COLOR_PALETTE_FILE', __FILE__);
define('GL_COLOR_PALETTE_PATH', plugin_dir_path(__FILE__));
define('GL_COLOR_PALETTE_URL', plugin_dir_url(__FILE__));
define('GL_COLOR_PALETTE_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once GL_COLOR_PALETTE_PATH . 'includes/class-autoloader.php';
GLColorPalette\Autoloader::register();

// Initialize dependency manager
$dependency_manager = GLColorPalette\DependencyManager::get_instance();
$system_check = $dependency_manager->check_system_requirements();

if ($system_check['status']) {
    // Initialize plugin
    function run_gl_color_palette_generator() {
        $plugin = new GLColorPalette\ColorPaletteGenerator();
        $plugin->run();
    }
    add_action('plugins_loaded', 'run_gl_color_palette_generator');
} else {
    // Display admin notice for missing dependencies
    add_action('admin_notices', function() use ($system_check) {
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e('GL Color Palette Generator:', 'gl-color-palette-generator'); ?></strong></p>
            <ul>
                <?php foreach ($system_check['messages'] as $message): ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    });
}

// Register hooks
register_activation_hook(GL_COLOR_PALETTE_FILE, [GLColorPalette\Setup::class, 'activate']);
register_deactivation_hook(GL_COLOR_PALETTE_FILE, [GLColorPalette\Setup::class, 'deactivate']);
register_uninstall_hook(GL_COLOR_PALETTE_FILE, [GLColorPalette\PluginDeletion::class, 'uninstall']);
