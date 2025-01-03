<?php
/**
 * Cache Manager Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Utils
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Utils;

use GL_Color_Palette_Generator\Core\Abstract_Component;

/**
 * Class Cache_Manager
 * 
 * Handles caching for generated palettes and other data
 */
class Cache_Manager extends Abstract_Component {
    /**
     * Cache prefix
     *
     * @var string
     */
    private string $prefix = 'gl_cpg_';

    /**
     * Constructor
     */
    public function __construct() {
        $this->set_name('cache_manager');
        $this->set_version('1.0.0');
    }

    /**
     * Initialize the component
     *
     * @return bool True if initialization was successful
     */
    public function init(): bool {
        add_action('gl_daily_cleanup', [$this, 'cleanup']);
        return true;
    }

    /**
     * Set a cache item
     *
     * @param string $key   Cache key
     * @param mixed  $value Cache value
     * @param int    $ttl   Time to live in seconds
     */
    public function set(string $key, $value, int $ttl = 3600): void {
        set_transient($this->prefix . $key, $value, $ttl);
    }

    /**
     * Get a cache item
     *
     * @param string $key Cache key
     * @return mixed|false Cache value or false if not found
     */
    public function get(string $key) {
        return get_transient($this->prefix . $key);
    }

    /**
     * Delete a cache item
     *
     * @param string $key Cache key
     */
    public function delete(string $key): void {
        delete_transient($this->prefix . $key);
    }

    /**
     * Clean up expired cache items
     */
    public function cleanup(): void {
        global $wpdb;
        
        $time = time();
        $expired = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s
                AND option_value < %d",
                $wpdb->esc_like('_transient_timeout_' . $this->prefix) . '%',
                $time
            )
        );

        foreach ($expired as $transient) {
            $key = str_replace('_transient_timeout_', '', $transient);
            delete_transient($key);
        }
    }
}
