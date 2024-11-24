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
        / Only handle our namespace
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

        / Add 'class-' prefix if not an interface or trait
        if (!strpos($class_path, 'interface-') && !strpos($class_path, 'trait-')) {
            $class_parts = explode('/', $class_path);
            $class_file = end($class_parts);
            $class_path = str_replace($class_file, 'class-' . $class_file, $class_path);
        }

        return GL_CPG_PLUGIN_DIR . 'includes/' . $class_path . '.php';
    }
}

/ Register the autoloader
Autoloader::register();
