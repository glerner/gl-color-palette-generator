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
        } else {
            error_log("Failed to load class: $class_name at path: $file_path");
        }
    }

    /**
     * Get file path from class name
     *
     * @param string $class_name Full class name.
     * @return string File path
     */
    private function get_file_path($class_name) {
        // Remove namespace prefix
        $class_name = str_replace('GL_Color_Palette_Generator\\', '', $class_name);

        // Split into parts
        $parts = explode('\\', $class_name);

        // Get the actual class name (last part)
        $class_name = array_pop($parts);

        // Convert directory names to lowercase and hyphenated
        $parts = array_map(function($part) {
            return strtolower(str_replace('_', '-', $part));
        }, $parts);

        // Build path
        $path = $this->base_dir . 'includes/';
        if (!empty($parts)) {
            $path .= implode('/', $parts) . '/';
        }

        // Determine file prefix based on type
        $prefix = 'class-';
        if (strpos($path, 'interfaces/') !== false) {
            $prefix = 'interface-';
        } elseif (strpos($path, 'traits/') !== false) {
            $prefix = 'trait-';
        }

        // Convert class name to file name format
        $file_name = $prefix . strtolower(str_replace('_', '-', $class_name)) . '.php';

        return $path . $file_name;
    }
}

// Register the autoloader
Autoloader::register();
