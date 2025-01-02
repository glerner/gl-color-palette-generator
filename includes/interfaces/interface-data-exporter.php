<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface DataExporter {
    public function export_to_format(array $data, string $format): string;
    public function get_supported_formats(): array;
    public function validate_export_data(array $data): bool;
} 
