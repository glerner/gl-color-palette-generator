namespace GL_Color_Palette_Generator\Cache;

/**
 * Class Color_Cache
 * 
 * Handles caching of color data and calculations.
 * 
 * @package GL_Color_Palette_Generator
 * @subpackage Cache
 * @since 1.0.0
 */
class Color_Cache {
    private $cache_group = 'color_names';
    private $expiration = DAY_IN_SECONDS * 7;

    public function get_color_name($hex, $context) {
        $cache_key = $this->generate_cache_key($hex, $context);
        return wp_cache_get($cache_key, $this->cache_group);
    }

    public function set_color_name($hex, $name, $context) {
        $cache_key = $this->generate_cache_key($hex, $context);
        wp_cache_set($cache_key, $name, $this->cache_group, $this->expiration);
    }

    private function generate_cache_key($hex, $context) {
        return md5($hex . '_' . $context);
    }

    public function clear_cache() {
        wp_cache_delete_group($this->cache_group);
    }

    /**
     * Cache color calculations
     *
     * @param array $color_data Color data to cache
     * @return array Cache status information
     */
    public function cache_calculations($color_data) {
        $cache_key = $this->generate_cache_key($color_data);
        $expiration = $this->calculate_cache_expiration($color_data);

        wp_cache_set(
            $cache_key,
            [
                'data' => $color_data,
                'timestamp' => time(),
                'metadata' => $this->generate_cache_metadata($color_data)
            ],
            'color_palette_generator',
            $expiration
        );

        return [
            'cache_key' => $cache_key,
            'expiration' => $expiration,
            'status' => 'cached'
        ];
    }

    /**
     * Manage cache invalidation
     */
    public function manage_cache_invalidation($trigger_type) {
        switch ($trigger_type) {
            case 'update':
                return $this->handle_update_invalidation();
            case 'delete':
                return $this->handle_delete_invalidation();
            case 'bulk':
                return $this->handle_bulk_invalidation();
            default:
                return $this->handle_selective_invalidation($trigger_type);
        }
    }

    /**
     * Optimize cache performance
     */
    public function optimize_cache() {
        return [
            'cleanup_results' => $this->cleanup_expired_cache(),
            'optimization_results' => $this->optimize_cache_storage(),
            'statistics' => $this->generate_cache_statistics(),
            'recommendations' => $this->generate_optimization_recommendations()
        ];
    }
}
