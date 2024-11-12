<?php

namespace GLColorPalette;

class ColorPaletteGenerator {
    private $dependency_manager;
    private $version;
    private $plugin_name;

    public function __construct() {
        $this->version = '1.0.0';
        $this->plugin_name = 'color-palette-generator';
        $this->dependency_manager = DependencyManager::get_instance();
        $this->load_dependencies();
    }

    private function load_dependencies() {
        $dependency_check = $this->dependency_manager->check_dependencies();

        if (!$dependency_check['status']) {
            add_action('admin_notices', function() use ($dependency_check) {
                $this->display_dependency_errors($dependency_check['messages']);
            });
            return;
        }

        // Load required files
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-accessibility-checker.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-color-wheel.php';
        // ... other requires
    }

    private function display_dependency_errors($messages) {
        ?>
        <div class="notice notice-error">
            <p><strong>Color Palette Generator:</strong> The following dependencies are missing:</p>
            <ul>
                <?php foreach ($messages as $message): ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
} 
