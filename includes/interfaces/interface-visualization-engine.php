<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface VisualizationEngine {
    public function create_visualization(array $data): string;
    public function get_visualization_types(): array;
    public function customize_visualization(array $options): void;
} 
