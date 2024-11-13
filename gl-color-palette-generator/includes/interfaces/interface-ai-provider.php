<?php

namespace GLColorPalette\Interfaces;

interface AIProvider {
    public function initialize(): bool;
    public function generate_prompt(array $parameters): string;
    public function process_response(string $response): array;
    public function get_color_suggestions(array $criteria): array;
    public function validate_api_key(): bool;
    public function get_rate_limits(): array;
} 
