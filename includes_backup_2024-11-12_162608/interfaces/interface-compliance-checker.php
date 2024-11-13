<?php
namespace GLColorPalette\Interfaces;

interface ComplianceChecker {
    public function check_wcag_compliance(array $colors): array;
    public function validate_contrast_ratio(string $color1, string $color2): float;
    public function get_compliance_report(array $palette): array;
} 
