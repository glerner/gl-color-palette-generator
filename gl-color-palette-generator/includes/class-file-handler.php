<?php

class FileHandler {
    private $base_theme;
    private $upload_dir;
    private $theme_path;
    private $styles_path;

    public function __construct($base_theme = 'twentytwentyfour') {
        $this->base_theme = $base_theme;
        $this->upload_dir = wp_upload_dir();
        $this->theme_path = get_theme_root() . '/' . $this->base_theme;
        $this->styles_path = $this->theme_path . '/styles';

        // Create necessary directories
        $this->init_directories();
    }

    /**
     * Initialize necessary directories
     */
    private function init_directories() {
        // Create temporary working directory in uploads
        $temp_dir = $this->upload_dir['basedir'] . '/color-palette-temp';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }

        // Create styles directory if it doesn't exist
        if (!file_exists($this->styles_path)) {
            wp_mkdir_p($this->styles_path);
        }

        // Add .htaccess to protect temp directory
        $htaccess = $temp_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, 'Deny from all');
        }
    }

    /**
     * Save theme.json file
     */
    public function save_theme_json($data) {
        $theme_json_path = $this->theme_path . '/theme.json';

        try {
            // Backup existing theme.json if it exists
            if (file_exists($theme_json_path)) {
                $backup_path = $theme_json_path . '.backup-' . date('Y-m-d-His');
                copy($theme_json_path, $backup_path);
            }

            // Save new theme.json
            $result = file_put_contents(
                $theme_json_path,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            if ($result === false) {
                throw new Exception('Failed to save theme.json file');
            }
        } catch (Exception $e) {
            throw new Exception('Error saving theme.json file: ' . $e->getMessage());
        }
    }
} 
