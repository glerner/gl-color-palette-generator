<?php

namespace GLColorPalette\Interfaces;

interface PreviewGenerator {
    public function generate_preview(array $palette): string;
    public function get_preview_formats(): array;
    public function customize_preview(array $options): void;
} 
