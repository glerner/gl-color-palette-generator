<?php
namespace GLColorPalette\Interfaces;

interface PerformanceMonitor {
    public function start_monitoring(): void;
    public function end_monitoring(): array;
    public function get_performance_metrics(): array;
    public function log_performance_data(array $data): void;
} 
