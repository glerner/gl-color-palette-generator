<?php
/**
 * Autoloader Class
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GLColorPalette;

/**
 * Class Autoloader
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */
class Autoloader {
    /**
     * @var string Base directory for includes
     */
    private string $base_dir;

    /**
     * @var array Registered directories for autoloading
     */
    private array $directories = [];

    /**
     * Initialize the autoloader
     */
    private function __construct() {
        $this->base_dir = dirname(__FILE__);
        $this->register_directories();
    }

    /**
     * Register the autoloader
     *
     * @return void
     */
    public static function register(): void {
        $loader = new self();
        spl_autoload_register([$loader, 'autoload']);
    }

    /**
     * Register directories to autoload
     *
     * @return void
     */
    private function register_directories(): void {
        $this->directories = [
            'GLColorPalette\\' => [
                $this->base_dir,
                $this->base_dir . '/abstracts',
                $this->base_dir . '/interfaces',
                $this->base_dir . '/traits',
            ],
            'GLColorPalette\\Providers\\' => [
                $this->base_dir . '/Providers'
            ]
        ];
    }

    /**
     * Autoload classes
     *
     * @param string $class The fully-qualified class name.
     * @return void
     */
    private function autoload(string $class): void {
        foreach ($this->directories as $namespace => $directories) {
            if (strpos($class, $namespace) === 0) {
                $this->load_class($class, $namespace, $directories);
                break;
            }
        }
    }

    /**
     * Load a class file
     *
     * @param string $class The fully-qualified class name.
     * @param string $namespace The namespace prefix.
     * @param array  $directories Directories to search in.
     * @return void
     */
    private function load_class(string $class, string $namespace, array $directories): void {
        // Remove namespace from class name
        $class_name = str_replace($namespace, '', $class);

        // Convert namespace separator to directory separator
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);

        // Convert class name format to file name format
        $file_name = 'class-' . strtolower(str_replace('_', '-', $class_path)) . '.php';

        // Search in registered directories
        foreach ($directories as $directory) {
            $file = $directory . DIRECTORY_SEPARATOR . $file_name;
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
} 
