<?php

namespace GLColorPalette\Interfaces;

interface BusinessAnalyzer {
    public function analyze_brand_compatibility(array $palette): array;
    public function get_industry_recommendations(string $industry): array;
    public function generate_usage_guidelines(array $palette): array;
} 
