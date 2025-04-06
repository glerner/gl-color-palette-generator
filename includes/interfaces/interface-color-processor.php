<?php

namespace GL_Color_Palette_Generator\Interfaces;

interface ColorProcessor {
	public function convert_color( string $color, string $from_space, string $to_space ): array;
	public function validate_color_format( string $color ): bool;
	public function get_color_properties( string $color ): array;
}
