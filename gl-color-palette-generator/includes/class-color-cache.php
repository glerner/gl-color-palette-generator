<?php

class ColorCache {
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
} 
