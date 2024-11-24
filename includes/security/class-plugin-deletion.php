<?php
namespace GLColorPalette;

class GLColorPaletteDeletion {
    /**
     * Initialize
     */
    public function init() {
        add_action('admin_menu', [$this, 'add_deletion_page']);
        add_action('admin_post_gl_confirm_deletion', [$this, 'handle_confirmation']);
        add_filter('plugin_action_links_' . GL_COLOR_PALETTE_PLUGIN_BASENAME,
            [$this, 'modify_plugin_actions'], 20);
    }

    /**
     * Add hidden admin page for deletion confirmation
     */
    public function add_deletion_page() {
        add_submenu_page(
            null, / Hidden from menu
            __('Delete Plugin', 'gl-color-palette-generator'),
            __('Delete Plugin', 'gl-color-palette-generator'),
            'activate_plugins',
            'gl-color-palette-delete',
            [$this, 'render_deletion_page']
        );
    }

    /**
     * Modify plugin action links
     */
    public function modify_plugin_actions($actions) {
        if (isset($actions['delete'])) {
            $delete_url = admin_url('admin.php?page=gl-color-palette-delete');
            $actions['delete'] = sprintf(
                '<a href="%s" class="delete" aria-label="%s">%s</a>',
                esc_url($delete_url),
                esc_attr__('Delete GL Color Palette Generator', 'gl-color-palette-generator'),
                esc_html__('Delete', 'gl-color-palette-generator')
            );
        }
        return $actions;
    }

    /**
     * Render deletion confirmation page
     */
    public function render_deletion_page() {
        / Get statistics
        $stats = $this->get_plugin_statistics();
        ?>
        <div class="wrap">
            <h1><?php _e('Delete GL Color Palette Generator', 'gl-color-palette-generator'); ?></h1>

            <div class="notice notice-warning">
                <p><strong><?php _e('Warning: This action cannot be undone!', 'gl-color-palette-generator'); ?></strong></p>
            </div>

            <div class="card">
                <h2><?php _e('Plugin Data Summary', 'gl-color-palette-generator'); ?></h2>
                <ul>
                    <li><?php printf(__('Color Palettes: %d', 'gl-color-palette-generator'), $stats['palettes']); ?></li>
                    <li><?php printf(__('History Records: %d', 'gl-color-palette-generator'), $stats['history']); ?></li>
                    <li><?php printf(__('User Preferences: %d', 'gl-color-palette-generator'), $stats['preferences']); ?></li>
                    <li><?php printf(__('Uploaded Files: %d', 'gl-color-palette-generator'), $stats['files']); ?></li>
                </ul>
            </div>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>"
                  id="gl-color-palette-delete-form">

                <input type="hidden" name="action" value="gl_confirm_deletion">
                <?php wp_nonce_field('gl_delete_plugin', 'gl_deletion_nonce'); ?>

                <div class="card">
                    <h3><?php _e('Deletion Options', 'gl-color-palette-generator'); ?></h3>

                    <label>
                        <input type="checkbox" name="export_data" value="1" checked>
                        <?php _e('Export data before deletion', 'gl-color-palette-generator'); ?>
                    </label>

                    <p class="description">
                        <?php _e('Creates a backup of all plugin data before deletion.', 'gl-color-palette-generator'); ?>
                    </p>

                    <br><br>

                    <label>
                        <input type="checkbox" name="confirm_deletion" required>
                        <?php _e('I understand this will permanently delete all color palette data', 'gl-color-palette-generator'); ?>
                    </label>
                </div>

                <div class="card">
                    <h3><?php _e('Confirmation', 'gl-color-palette-generator'); ?></h3>
                    <p>
                        <?php _e('Please type "DELETE" to confirm:', 'gl-color-palette-generator'); ?>
                        <input type="text" name="delete_confirmation" pattern="DELETE" required>
                    </p>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-delete">
                        <?php _e('Permanently Delete Plugin', 'gl-color-palette-generator'); ?>
                    </button>
                    <a href="<?php echo admin_url('plugins.php'); ?>" class="button button-secondary">
                        <?php _e('Cancel', 'gl-color-palette-generator'); ?>
                    </a>
                </p>
            </form>
        </div>

        <style>
            .button-delete {
                background: #dc3232;
                border-color: #dc3232;
                color: #fff;
            }
            .button-delete:hover {
                background: #cc2929;
                border-color: #cc2929;
                color: #fff;
            }
            .card {
                padding: 1em;
                margin: 1em 0;
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#gl-color-palette-delete-form').on('submit', function(e) {
                if (!confirm('<?php _e('Are you absolutely sure you want to delete this plugin and all its data?', 'gl-color-palette-generator'); ?>')) {
                    e.preventDefault();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Handle deletion confirmation
     */
    public function handle_confirmation() {
        / Verify nonce
        if (!wp_verify_nonce($_POST['gl_deletion_nonce'], 'gl_delete_plugin')) {
            wp_die(__('Security check failed', 'gl-color-palette-generator'));
        }

        / Verify user capabilities
        if (!current_user_can('activate_plugins')) {
            wp_die(__('You do not have permission to perform this action', 'gl-color-palette-generator'));
        }

        / Export data if requested
        if (isset($_POST['export_data']) && $_POST['export_data'] == '1') {
            require_once GL_COLOR_PALETTE_PLUGIN_DIR . 'includes/class-data-exporter.php';
            GLColorPaletteExporter::export_all_data();
        }

        / Deactivate plugin
        deactivate_plugins(GL_COLOR_PALETTE_PLUGIN_BASENAME);

        / Redirect to plugins page with message
        wp_safe_redirect(add_query_arg(
            'gl_plugin_deleted',
            '1',
            admin_url('plugins.php')
        ));
        exit;
    }

    /**
     * Get plugin statistics
     */
    private function get_plugin_statistics() {
        global $wpdb;

        return [
            'palettes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gl_color_palettes"),
            'history' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gl_color_history"),
            'preferences' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gl_color_preferences"),
            'files' => $this->count_uploaded_files()
        ];
    }

    /**
     * Count uploaded files
     */
    private function count_uploaded_files() {
        $upload_dir = wp_upload_dir();
        $path = $upload_dir['basedir'] . '/gl-color-palettes';

        if (!is_dir($path)) {
            return 0;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $count = 0;
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Handle plugin deletion
     */
    public function handle_deletion($delete_data = false) {
        if ($delete_data) {
            $this->delete_plugin_data();
        } else {
            $this->backup_plugin_data();
        }

        $this->remove_scheduled_hooks();
        $this->clean_up_files();

        return [
            'status' => 'success',
            'deleted_data' => $delete_data,
            'backup_location' => $this->get_backup_location(),
            'cleanup_log' => $this->get_cleanup_log()
        ];
    }

    /**
     * Delete plugin data
     */
    private function delete_plugin_data() {
        global $wpdb;

        / Delete database tables
        $tables = [
            $wpdb->prefix . 'color_palettes',
            $wpdb->prefix . 'color_analytics',
            $wpdb->prefix . 'color_cache'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        / Delete options
        $options = [
            'color_palette_generator_version',
            'color_palette_generator_settings',
            'color_palette_generator_cache'
        ];

        foreach ($options as $option) {
            delete_option($option);
        }

        / Delete transients
        $this->delete_transients();
    }

    /**
     * Clean up files and directories
     */
    private function clean_up_files() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/color-palette-generator';

        if (is_dir($plugin_upload_dir)) {
            $this->recursively_delete_directory($plugin_upload_dir);
        }

        / Clean up any temporary files
        $temp_files = glob(WP_TEMP_DIR . '/color-palette-*');
        if (is_array($temp_files)) {
            foreach ($temp_files as $file) {
                @unlink($file);
            }
        }
    }
}
