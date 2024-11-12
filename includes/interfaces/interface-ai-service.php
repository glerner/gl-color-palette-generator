<?php

namespace GLColorPalette\Interfaces;

interface AIService {
    public function initialize_service(): bool;
    public function get_color_suggestions(array $criteria): array;
    public function analyze_color_combination(array $colors): array;
    public function get_service_status(): array;
} 
