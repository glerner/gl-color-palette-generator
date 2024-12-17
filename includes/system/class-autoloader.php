<?php
/**
 * Autoloader for GL Color Palette Generator
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\System;

/**
 * Class Autoloader
 */
class Autoloader {
    private $base_dir;

    /**
     * Constructor
     */
    public function __construct() {
        // Go up two directories from the autoloader location to get to the plugin root
        $this->base_dir = dirname(dirname(__DIR__)) . '/';
    }

    /**
     * Register autoloader
     */
    public static function register() {
        spl_autoload_register([new self(), 'autoload']);
    }

    /**
     * Autoload classes
     *
     * @param string $class_name Full class name.
     */
    public function autoload($class_name) {
        // Only handle our namespace
        if (strpos($class_name, 'GL_Color_Palette_Generator\\') !== 0) {
            return;
        }

        $file_path = $this->get_file_path($class_name);
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /**
     * Get file path from class name
     *
     * @param string $class_name Full class name.
     * @return string File path
     */
    private function get_file_path($class_name) {
        $class_name = str_replace('GL_Color_Palette_Generator\\', '', $class_name);
        $class_path = strtolower($class_name);
        $class_path = str_replace('_', '-', $class_path);
        $class_path = str_replace('\\', '/', $class_path);

        // Add appropriate prefix based on type
        $class_parts = explode('/', $class_path);
        $class_file = end($class_parts);

        if (strpos($class_path, 'traits/') !== false) {
            $class_path = str_replace($class_file, 'trait-' . $class_file, $class_path);
        } elseif (strpos($class_path, 'interfaces/') !== false) {
            $class_path = str_replace($class_file, 'interface-' . $class_file, $class_path);
        } else {
            $class_path = str_replace($class_file, 'class-' . $class_file, $class_path);
        }

        return $this->base_dir . 'includes/' . $class_path . '.php';
    }
}

// Register the autoloader
Autoloader::register();
