<?php

namespace GL_Color_Palette_Generator\Interfaces;

interface Exporter {
	public function export_palette( array $palette, string $format ): string;
	public function get_supported_formats(): array;
	public function validate_export_data( array $data ): bool;
}
