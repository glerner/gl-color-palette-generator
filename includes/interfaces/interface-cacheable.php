<?php

namespace GLColorPalette\Interfaces;

interface Cacheable {
    public function get_cache_key(): string;
    public function get_cache_group(): string;
    public function get_cache_expiration(): int;
} 
