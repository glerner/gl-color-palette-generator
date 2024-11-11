<?php
/**
 * Plugin Name: Lerner Color Palette Generator
 * Plugin URI: https://website-tech.glerner.com/gl-color-palette-generator
 * Description: Advanced color palette generation and management system
 * Version: 1.0.0
 * Author: George Lerner
 * Author URI: https://website-tech.glerner.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: color-palette-generator
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 */

// Prevent direct access
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('COLOR_PALETTE_VERSION', '1.0.0');
define('COLOR_PALETTE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COLOR_PALETTE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COLOR_PALETTE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    // Plugin namespace prefix
    $prefix = 'ColorPalette\\';
    $base_dir = COLOR_PALETTE_PLUGIN_DIR . 'includes/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Load the file if it exists
    if (file_exists($file)) {
        require $file;
    }
});

// Include required files
require_once COLOR_PALETTE_PLUGIN_DIR . 'includes/class-setup.php';
require_once COLOR_PALETTE_PLUGIN_DIR . 'includes/class-core.php';

/**
 * Main plugin class
 */
final class ColorPaletteGenerator {
    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Core plugin class instance
     */
    public $core;

    /**
     * Get plugin instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize plugin
        add_action('plugins_loaded', [$this, 'init']);

        // Register activation hook
        register_activation_hook(__FILE__, [$this, 'install']);

        // Register update routine
        add_action('plugins_loaded', [$this, 'update']);

        // Register uninstall hook
        register_uninstall_hook(__FILE__, ['GLColorPaletteUninstall', 'uninstall']);
    }

    /**
     * Initialize plugin
     */
    public function init() {
        $this->core = new ColorPaletteCore();
    }

    /**
     * Install plugin
     */
    public function install() {
        ColorPaletteSetup::install();
    }

    /**
     * Update plugin
     */
    public function update() {
        ColorPaletteSetup::update();
    }
}

// Initialize plugin
ColorPaletteGenerator::instance();

// Initialize deletion handler
add_action('init', function() {
    $deletion_handler = new GLColorPaletteDeletion();
    $deletion_handler->init();
});
