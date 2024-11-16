<?php

namespace GLColorPalette\Interfaces;

interface ErrorHandler {
    public function log_error(string $message, string $level = 'error'): void;
    public function get_error_log(): array;
    public function clear_error_log(): bool;
} 
