<?php
namespace {
/*
None of this code requires the plugin's namespace (GL_Color_Palette_Generator\Tests\Bootstrap). All WordPress function mocks must be in the root namespace to properly intercept calls from WordPress code, and the rest of the code is just initialization that works fine in the root namespace.

See docs/development-guidelines.md for a grep command to find all the functions your plugin uses, that should be mocked.
*/

// Initialize error reporting
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);

// Define plugin directory
if (!defined('GL_CPG_PLUGIN_DIR')) {
    define('GL_CPG_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}

// Define WordPress constants
define('ABSPATH', dirname(__DIR__, 2) . '/');
define('WP_DEBUG', true);

// Load composer autoloader
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Initialize WP_Mock
\WP_Mock::bootstrap();

// Translation Functions
function __($text, $domain = 'default') { return $text; }
function _e($text, $domain = 'default') { echo $text; }
function _n($single, $plural, $number, $domain = 'default') { return $number === 1 ? $single : $plural; }

// Escaping Functions
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html__($text, $domain = 'default') { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function wp_kses_post($data) { return $data; }

// Hook Functions
function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) { return true; }
function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) { return true; }
function apply_filters($tag, $value, ...$args) { return $value; }
function do_action($tag, ...$args) { return true; }
function has_action($tag, $function_to_check = false) { return false; }

// Admin Functions
function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) { return true; }
function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null) { return true; }
function admin_url($path = '', $scheme = 'admin') { return 'http://example.com/wp-admin/' . ltrim($path, '/'); }
function add_settings_error($setting, $code, $message, $type = 'error') { return true; }
function set_current_screen($screen = '') { return true; }

// Settings Functions
function add_settings_section($id, $title, $callback, $page) { return true; }
function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = []) { return true; }
function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') { return true; }
function delete_option($option) { return true; }
function do_settings_sections($page) { return true; }
function register_setting($option_group, $option_name, $args = []) { return true; }
function settings_fields($option_group) { return true; }
function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {
    echo '<input type="submit" name="' . esc_attr($name) . '" value="' . esc_attr($text) . '" class="button button-' . esc_attr($type) . '">';
}
function update_option($option, $value, $autoload = null) { return true; }

// Cache Functions
function wp_cache_add_global_groups($groups) { return true; }
function wp_cache_delete_group($group) { return true; }
function wp_cache_flush() { return true; }
function wp_cache_init() { return true; }
function wp_cache_set($key, $data, $group = '', $expire = 0) { return true; }

// Asset Functions
function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) { return true; }
function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') { return true; }
function wp_localize_script($handle, $object_name, $l10n) { return true; }
function wp_script_is($handle, $list = 'enqueued') { return true; }
function wp_style_is($handle, $list = 'enqueued') { return true; }

// Plugin Functions
function add_shortcode($tag, $callback) { return true; }
function check_ajax_referer($action = -1, $query_arg = false, $die = true) { return true; }
function deactivate_plugins($plugins, $silent = false, $network_wide = null) { return true; }
function flush_rewrite_rules($hard = true) { return true; }
function plugin_basename($file) { return basename($file); }
function plugin_dir_url($file) { return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/'; }
function plugins_url($path = '', $plugin = '') { return 'http://example.com/wp-content/plugins/' . ltrim($path, '/'); }
function register_activation_hook($file, $callback) { return true; }
function register_deactivation_hook($file, $callback) { return true; }
function register_rest_route($namespace, $route, $args = []) { return true; }

// Transient Functions
function delete_transient($transient) { return true; }
function get_transient($transient) { return false; }
function set_transient($transient, $value, $expiration = 0) { return true; }

// User Functions
function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') { return true; }

// Filesystem Functions
function wp_mkdir_p($dir) { return true; }
function wp_upload_dir($time = null, $create_dir = true, $refresh_cache = false) {
    return ['path' => '/tmp', 'url' => 'http://example.com', 'subdir' => '',
            'basedir' => '/tmp', 'baseurl' => 'http://example.com', 'error' => false];
}

// AJAX/JSON Functions
function wp_send_json_error($data = null, $status_code = null) { return true; }
function wp_send_json_success($data = null, $status_code = null) { return true; }

// Form Functions
function selected($selected, $current = true, $echo = true) {
    $result = $selected == $current ? ' selected="selected"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}
function checked($checked, $current = true, $echo = true) {
    $result = $checked == $current ? ' checked="checked"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}
function disabled($disabled, $current = true, $echo = true) {
    $result = $disabled == $current ? ' disabled="disabled"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}

// Security Functions
function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
    $nonce = '<input type="hidden" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . wp_create_nonce($action) . '" />';
    if ($referer) {
        $nonce .= wp_referer_field(false);
    }
    if ($echo) {
        echo $nonce;
    }
    return $nonce;
}
function wp_create_nonce($action = -1) { return 'test-nonce'; }
function wp_referer_field($echo = true) {
    $referer = '<input type="hidden" name="_wp_http_referer" value="" />';
    if ($echo) {
        echo $referer;
    }
    return $referer;
}

// Error Handling
function wp_die($message = '', $title = '', $args = []) { throw new \Exception($message); }

// Scheduled Tasks
function wp_clear_scheduled_hook($hook) { return true; }

// WordPress Classes
class WP_REST_Controller {
    public function register_routes() {}
    public function get_item_schema() {}
    public function get_collection_params() { return []; }
    protected function prepare_item_for_response($item, $request) { return $item; }
    protected function prepare_items_for_response($items, $request) { return $items; }
    public function get_namespace() { return ''; }
    public function get_rest_base() { return ''; }
}

echo "\n=== Unit Testing Bootstrap Complete ===\n";
}
