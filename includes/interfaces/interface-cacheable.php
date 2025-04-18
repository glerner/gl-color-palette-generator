<?php

namespace GL_Color_Palette_Generator\Interfaces;

interface Cacheable {
	public function get_cache_key(): string;
	public function get_cache_group(): string;
	public function get_cache_expiration(): int;
}
