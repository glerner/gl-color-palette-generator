<?php
namespace GLColorPalette\Interfaces;

interface CacheManager {
    public function set(string $key, $value, int $expiration = 0): bool;
    public function get(string $key, $default = null);
    public function delete(string $key): bool;
    public function flush(): bool;
} 
