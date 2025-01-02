<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface FileHandler {
    public function save_file(string $content, string $filename): bool;
    public function read_file(string $filename): string;
    public function delete_file(string $filename): bool;
    public function get_file_list(): array;
} 
