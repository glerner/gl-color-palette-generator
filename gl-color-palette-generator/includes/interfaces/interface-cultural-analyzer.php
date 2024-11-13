<?php

namespace GLColorPalette\Interfaces;

interface CulturalAnalyzer {
    public function analyze_cultural_significance(string $color): array;
    public function get_cultural_variations(string $color): array;
    public function get_regional_preferences(): array;
} 
