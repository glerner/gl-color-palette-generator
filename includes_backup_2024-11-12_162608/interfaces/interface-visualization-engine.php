<?php
namespace GLColorPalette\Interfaces;

interface VisualizationEngine {
    public function create_visualization(array $data): string;
    public function get_visualization_types(): array;
    public function customize_visualization(array $options): void;
} 
