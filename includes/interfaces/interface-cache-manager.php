<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface CacheManager {
    public function set(string $key, $value, int $expiration = 0): bool;
    public function get(string $key, $default = null);
    public function delete(string $key): bool;
    public function flush(): bool;
} 
