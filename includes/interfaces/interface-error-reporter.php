<?php
namespace GL_Color_Palette_Generator\Interfaces;

interface ErrorReporter {
	public function log_error( string $message, array $context = array() ): void;
	public function get_error_log(): array;
	public function has_errors(): bool;
	public function clear_errors(): void;
}
