<?php

namespace GLColorPalette;

class DependencyManager {
    private static ?self $instance = null;
    private array $loaded_classes = [];
    private array $class_dependencies = [];
    private array $load_order = [];

    private const REQUIREMENTS = [
        'php' => [
            'version' => '8.0.0',
            'extensions' => [
                'gd',
                'json',
                'mbstring',
                'curl',
                'zip',
                'dom',
                'libxml'
            ]
        ],
        'wordpress' => [
            'version' => '6.2.0',
            'functions' => [
                'wp_get_global_settings',
                'wp_get_global_styles',
                'wp_enqueue_block_style'
            ]
        ],
        'mysql' => [
            'version' => '5.7.0'
        ]
    ];

    private function __construct() {
        $this->initialize_dependencies();
    }

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function check_system_requirements(): array {
        $results = [
            'status' => true,
            'messages' => [],
            'details' => []
        ];

        // Check PHP version
        if (version_compare(PHP_VERSION, self::REQUIREMENTS['php']['version'], '<')) {
            $results['status'] = false;
            $results['messages'][] = sprintf(
                'PHP version %s or higher is required. Current version: %s',
                self::REQUIREMENTS['php']['version'],
                PHP_VERSION
            );
            $results['details']['php_version'] = [
                'required' => self::REQUIREMENTS['php']['version'],
                'current' => PHP_VERSION,
                'status' => false
            ];
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, self::REQUIREMENTS['wordpress']['version'], '<')) {
            $results['status'] = false;
            $results['messages'][] = sprintf(
                'WordPress version %s or higher is required. Current version: %s',
                self::REQUIREMENTS['wordpress']['version'],
                $wp_version
            );
            $results['details']['wordpress_version'] = [
                'required' => self::REQUIREMENTS['wordpress']['version'],
                'current' => $wp_version,
                'status' => false
            ];
        }

        // Check PHP extensions
        $missing_extensions = [];
        foreach (self::REQUIREMENTS['php']['extensions'] as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }
        if (!empty($missing_extensions)) {
            $results['status'] = false;
            $results['messages'][] = sprintf(
                'Missing required PHP extensions: %s',
                implode(', ', $missing_extensions)
            );
            $results['details']['php_extensions'] = [
                'missing' => $missing_extensions,
                'status' => false
            ];
        }

        // Check WordPress functions
        $missing_functions = [];
        foreach (self::REQUIREMENTS['wordpress']['functions'] as $function) {
            if (!function_exists($function)) {
                $missing_functions[] = $function;
            }
        }
        if (!empty($missing_functions)) {
            $results['status'] = false;
            $results['messages'][] = sprintf(
                'Missing required WordPress functions: %s',
                implode(', ', $missing_functions)
            );
            $results['details']['wordpress_functions'] = [
                'missing' => $missing_functions,
                'status' => false
            ];
        }

        return $results;
    }

    public function load_class(string $class_name): object {
        if (isset($this->loaded_classes[$class_name])) {
            return $this->loaded_classes[$class_name];
        }

        // Check dependencies first
        if (isset($this->class_dependencies[$class_name])) {
            foreach ($this->class_dependencies[$class_name] as $dependency) {
                $this->load_class($dependency);
            }
        }

        $file_path = $this->get_class_file_path($class_name);
        if ($file_path && file_exists($file_path)) {
            require_once $file_path;
            $full_class_name = "GLColorPalette\\$class_name";
            $this->loaded_classes[$class_name] = new $full_class_name();
            return $this->loaded_classes[$class_name];
        }

        throw new \Exception("Could not load class: $class_name");
    }

    public function is_class_loaded(string $class_name): bool {
        return isset($this->loaded_classes[$class_name]);
    }

    public function get_class_instance(string $class_name): ?object {
        return $this->loaded_classes[$class_name] ?? null;
    }

    public function get_load_order(): array {
        return $this->load_order;
    }

    private function get_class_file_path(string $class_name): string|false {
        $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

        // Check main includes directory
        $main_path = plugin_dir_path(dirname(__FILE__)) . 'includes/' . $file_name;
        if (file_exists($main_path)) {
            return $main_path;
        }

        // Check providers directory
        $provider_path = plugin_dir_path(dirname(__FILE__)) . 'includes/providers/' . $file_name;
        if (file_exists($provider_path)) {
            return $provider_path;
        }

        return false;
    }

    // ... rest of the class implementation remains the same ...
}
