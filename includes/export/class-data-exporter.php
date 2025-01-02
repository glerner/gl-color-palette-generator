<?php
namespace GL_Color_Palette_Generator;

class GL_Color_Palette_GeneratorExporter {
    /**
     * Export all plugin data
     */
    public static function export_all_data() {
        global $wpdb;

        // Create export directory
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/gl-color-exports';
        $timestamp = date('Y-m-d-H-i-s');
        $export_path = $export_dir . '/backup-' . $timestamp;

        wp_mkdir_p($export_path);

        // Export data
        $export_data = [
            'metadata' => self::get_export_metadata(),
            'tables' => self::export_tables(),
            'options' => self::export_options(),
            'files' => self::export_files($export_path)
        ];

        // Create JSON file
        file_put_contents(
            $export_path . '/data.json',
            json_encode($export_data, JSON_PRETTY_PRINT)
        );

        // Create ZIP archive
        $zip_file = $export_dir . '/gl-color-palette-backup-' . $timestamp . '.zip';
        self::create_zip_archive($export_path, $zip_file);

        // Cleanup temporary directory
        self::delete_directory($export_path);

        return $zip_file;
    }

    /**
     * Get export metadata
     */
    private static function get_export_metadata() {
        return [
            'export_date' => current_time('mysql'),
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => GL_COLOR_PALETTE_VERSION,
            'site_url' => get_site_url(),
            'admin_email' => get_option('admin_email')
        ];
    }

    /**
     * Export database tables
     */
    private static function export_tables() {
        global $wpdb;

        $tables = [
            'palettes' => $wpdb->prefix . 'gl_color_palettes',
            'history' => $wpdb->prefix . 'gl_color_history',
            'preferences' => $wpdb->prefix . 'gl_color_preferences'
        ];

        $export_data = [];

        foreach ($tables as $key => $table) {
            $results = $wpdb->get_results("SELECT * FROM `{$table}`", ARRAY_A);
            $export_data[$key] = $results;
        }

        return $export_data;
    }

    /**
     * Export plugin options
     */
    private static function export_options() {
        global $wpdb;

        $options = [];

        // Get all options with our prefix
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM `{$wpdb->options}`
                WHERE option_name LIKE %s",
                'gl_color_palette_%'
            )
        );

        foreach ($results as $result) {
            $options[$result->option_name] = get_option($result->option_name);
        }

        return $options;
    }

    /**
     * Export files and return manifest
     */
    private static function export_files($export_path) {
        $upload_dir = wp_upload_dir();
        $source_dir = $upload_dir['basedir'] . '/gl-color-palettes';
        $manifest = [];

        if (is_dir($source_dir)) {
            $files_dir = $export_path . '/files';
            wp_mkdir_p($files_dir);

            $manifest = self::copy_directory($source_dir, $files_dir);
        }

        return $manifest;
    }

    /**
     * Copy directory and create manifest
     */
    private static function copy_directory($source, $dest) {
        $manifest = [];

        if (!is_dir($source)) {
            return $manifest;
        }

        $dir = opendir($source);

        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $src_path = $source . '/' . $file;
                $dst_path = $dest . '/' . $file;

                if (is_dir($src_path)) {
                    wp_mkdir_p($dst_path);
                    $sub_manifest = self::copy_directory($src_path, $dst_path);
                    if (!empty($sub_manifest)) {
                        $manifest[$file] = $sub_manifest;
                    }
                } else {
                    copy($src_path, $dst_path);
                    $manifest[] = [
                        'name' => $file,
                        'size' => filesize($src_path),
                        'modified' => filemtime($src_path)
                    ];
                }
            }
        }

        closedir($dir);
        return $manifest;
    }

    /**
     * Create ZIP archive
     */
    private static function create_zip_archive($source, $destination) {
        if (!extension_loaded('zip')) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source)) {
            $iterator = new RecursiveDirectoryIterator($source);
            $files = new RecursiveIteratorIterator(
                $iterator,
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else {
                    $zip->addFile($file, str_replace($source . '/', '', $file));
                }
            }
        }

        return $zip->close();
    }

    /**
     * Delete directory
     */
    private static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                self::delete_directory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    /**
     * Export palette data
     */
    public function export_palette_data($palette_id, $format = 'json') {
        $palette_data = $this->get_palette_data($palette_id);
        $formatted_data = $this->format_palette_data($palette_data, $format);

        return [
            'data' => $formatted_data,
            'format' => $format,
            'metadata' => $this->generate_export_metadata($palette_data),
            'download_url' => $this->generate_download_url($formatted_data, $format)
        ];
    }

    /**
     * Generate export formats
     */
    public function generate_export_formats($data) {
        return [
            'json' => $this->export_as_json($data),
            'css' => $this->export_as_css($data),
            'scss' => $this->export_as_scss($data),
            'pdf' => $this->export_as_pdf($data),
            'xml' => $this->export_as_xml($data),
            'theme_json' => $this->export_as_theme_json($data)
        ];
    }

    /**
     * Export analytics data
     */
    public function export_analytics_data($start_date = null, $end_date = null) {
        $analytics = new ColorAnalytics();
        $data = $analytics->get_data_range($start_date, $end_date);

        return [
            'analytics_data' => $data,
            'export_formats' => $this->get_available_formats(),
            'generated_files' => $this->generate_analytics_files($data),
            'download_links' => $this->generate_download_links($data)
        ];
    }
}
