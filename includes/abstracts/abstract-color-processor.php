<?php

namespace GLColorPalette\Abstracts;

abstract class BaseColorProcessor {
    protected array $color_space = ['rgb', 'hsl', 'hsv', 'cmyk', 'lab'];
    protected array $current_palette = [];

    abstract public function process_color(string $color): array;
    abstract public function validate_color(string $color): bool;

    protected function normalize_color_value($value, string $type): float {
        // Implementation
    }
} 
