<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface ThemeGenerator {
    public function generate_theme_json(array $palette): string;
    public function validate_theme_structure(array $theme): bool;
    public function get_theme_templates(): array;
} 
