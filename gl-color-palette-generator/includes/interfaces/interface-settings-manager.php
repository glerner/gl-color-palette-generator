<?php

namespace GLColorPalette\Interfaces;

interface SettingsManager {
    public function get_setting(string $key, $default = null);
    public function update_setting(string $key, $value): bool;
    public function delete_setting(string $key): bool;
    public function get_all_settings(): array;
} 
