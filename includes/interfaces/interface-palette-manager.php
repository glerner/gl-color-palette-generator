<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface PaletteManager {
    public function create_palette(array $colors): array;
    public function validate_palette(array $palette): bool;
    public function save_palette(array $palette, string $name): bool;
    public function get_saved_palettes(): array;
} 
